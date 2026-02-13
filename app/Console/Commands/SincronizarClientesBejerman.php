<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SincronizarClientesBejerman extends Command
{
    protected $signature = 'bejerman:sync-clientes {--all} {--con-movimientos}';
    protected $description = 'Sincroniza clientes de Bejerman a la base local';

    public function handle()
    {
        $this->info('🚀 Sincronizando clientes desde Bejerman...');

        $query = DB::connection('bejerman')->table('Clientes');

        // Solo clientes habilitados
        $query->where('cli_Habilitado', '1');

        // Si se especifica --con-movimientos, filtrar solo clientes con movimientos
        if ($this->option('con-movimientos')) {
            $this->info('📊 Filtrando solo clientes con movimientos en cuenta corriente...');
            $query->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('MovCC')
                    ->whereColumn('MovCC.mcccli_Cod', 'Clientes.cli_Cod');
            });
        }

        $clientes = $query->get();

        $this->info("📦 Total clientes a sincronizar: {$clientes->count()}");

        $creados = 0;
        $actualizados = 0;
        $errores = 0;

        $bar = $this->output->createProgressBar($clientes->count());
        $bar->start();

        foreach ($clientes as $cliente) {
            try {
                // Validar email
                $email = $this->validarEmail($cliente->cli_EMail, $cliente->cli_Cod);

                // Buscar o crear usuario
                $user = User::updateOrCreate(
                    ['bejerman_cliente_cod' => $cliente->cli_Cod],
                    [
                        'name' => $this->limpiarTexto($cliente->cli_RazSoc),
                        'email' => $email,
                        'password' => bcrypt($cliente->cli_CUIT),
                        'cuit' => $cliente->cli_CUIT,
                        'razon_social' => $this->limpiarTexto($cliente->cli_RazSoc),
                        'direccion' => $this->limpiarTexto($cliente->cli_Direc),
                        'localidad' => $this->limpiarTexto($cliente->cli_Loc),
                        'provincia' => $this->provinciaDesdeCliprv($cliente->cliprv_Codigo ?? null, $cliente->cli_CodPos ?? null, $cliente->cli_Loc ?? null),
                        'telefono' => $this->limpiarTexto($cliente->cli_Tel),
                        'rol' => 'cliente',
                        'autorizado' => 1,

                        // Descuentos
                        'descuento_uno' => $this->extraerDescuento($cliente->clidco_Cod, 1),
                        'descuento_dos' => $this->extraerDescuento($cliente->clidco_Cod, 2),
                        'descuento_tres' => $this->extraerDescuento($cliente->clidco_Cod, 3),

                        'bejerman_lista_precio_cod' => $cliente->clidlp_Cod,
                        'bejerman_descuento_comercial_cod' => $cliente->clidco_Cod,
                    ]
                );

                if ($user->wasRecentlyCreated) {
                    $creados++;
                } else {
                    $actualizados++;
                }
            } catch (\Exception $e) {
                $errores++;
                $this->error("\n❌ Error con cliente {$cliente->cli_Cod}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Creados: {$creados}");
        $this->info("🔄 Actualizados: {$actualizados}");
        if ($errores > 0) {
            $this->warn("⚠️  Errores: {$errores}");
        }

        $this->info("\n🎉 Sincronización completada!");

        // Mostrar resumen de clientes con movimientos
        if ($this->option('con-movimientos')) {
            $this->mostrarResumenMovimientos();
        }

        return 0;
    }

    private function validarEmail($email, $codigoCliente)
    {
        $email = trim(strtolower($email));

        // Si no tiene email o es inválido, generar uno
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "cliente.{$codigoCliente}@bejerman.local";
        }

        return $email;
    }

    private function limpiarTexto($texto)
    {
        return trim($texto ?? '');
    }

    private function provinciaDesdeCliprv(?string $cliprvCodigo, ?string $cp = null, ?string $loc = null): ?string
    {
        $code = trim((string)$cliprvCodigo);

        // ✅ mapping inicial (arrancamos con lo que ya comprobaste)
        $map = [
            '002' => 'Buenos Aires',
            // ir agregando…
        ];

        if ($code !== '' && isset($map[$code])) {
            return $map[$code];
        }

        // Fallback por CP si querés (mínimo para Argentina)
        $cp = preg_replace('/\D+/', '', (string)$cp);
        if ($cp !== '') {
            $cpInt = (int)$cp;

            // Buenos Aires (B) suele ser 1000–1999 y 6000–6999 y varios rangos,
            // Mar del Plata 7600 es Buenos Aires también.
            if ($cpInt >= 7000 && $cpInt <= 7999) return 'Buenos Aires';
        }

        // Fallback por localidad (opcional)
        $loc = strtoupper(trim((string)$loc));
        if ($loc === 'MAR DEL PLATA') return 'Buenos Aires';

        return null;
    }


    private function extraerDescuento($codigoDescuento, $nivel)
    {
        if (!$codigoDescuento) return 0;

        try {
            // ✅ TABLA CORRECTA EN TU DB: DescCom
            $descuento = DB::connection('bejerman')
                ->table('DescCom')
                ->where('dco_Cod', $codigoDescuento)
                ->first();

            if (!$descuento) return 0;

            $campo = "dco_Tasa{$nivel}";
            $raw = $descuento->$campo ?? 0;

            // Normalizar string con coma / % / espacios
            if (is_string($raw)) {
                $raw = str_replace(['%', ' '], '', $raw);
                $raw = str_replace(',', '.', $raw);
            }

            $v = (float) $raw;

            // Si viniera como 0.10 => 10%
            if ($v > 0 && $v <= 1) $v = $v * 100;

            // Bejerman lo guarda negativo
            $v = abs($v);

            // Tus columnas son integer
            return (int) round($v);
        } catch (\Exception $e) {
            return 0;
        }
    }


    private function mostrarResumenMovimientos()
    {
        $this->newLine();
        $this->info("📊 Resumen de clientes con movimientos:");

        $resumen = DB::table('users')
            ->whereNotNull('bejerman_cliente_cod')
            ->selectRaw('COUNT(*) as total')
            ->first();

        $this->info("Total clientes sincronizados: {$resumen->total}");

        // Verificar algunos movimientos
        $usuariosConMovimientos = User::whereNotNull('bejerman_cliente_cod')
            ->take(5)
            ->get();

        $this->newLine();
        $this->info("Verificando movimientos de primeros 5 clientes:");

        foreach ($usuariosConMovimientos as $user) {
            $movimientos = DB::connection('bejerman')
                ->table('MovCC')
                ->where('mcccli_Cod', $user->bejerman_cliente_cod)
                ->count();

            $this->line("  • {$user->name} ({$user->bejerman_cliente_cod}): {$movimientos} movimientos");
        }
    }
}
