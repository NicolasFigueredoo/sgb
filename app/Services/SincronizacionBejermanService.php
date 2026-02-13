<?php

namespace App\Services;

use App\Models\Bejerman\Cliente;
use App\Models\Bejerman\DescuentoComercial;
use App\Models\User;
use App\Models\SincronizacionBejermanLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SincronizacionBejermanService
{
    /**
     * Sincronizar cliente desde Bejerman por email
     */
    public function sincronizarCliente(User $user)
    {
        try {
            DB::beginTransaction();

            // 1. Buscar cliente en Bejerman por email
            $clienteBej = Cliente::where(function($query) use ($user) {
                $query->where('cli_EMail', 'LIKE', "%{$user->email}%");
                
                // Buscar también en emails adicionales si existen
                if ($user->email_dos) {
                    $query->orWhere('cli_EMail', 'LIKE', "%{$user->email_dos}%");
                }
                if ($user->email_tres) {
                    $query->orWhere('cli_EMail', 'LIKE', "%{$user->email_tres}%");
                }
                if ($user->email_cuatro) {
                    $query->orWhere('cli_EMail', 'LIKE', "%{$user->email_cuatro}%");
                }
            })
            ->where('cli_Habilitado', true)
            ->with(['descuentoComercial'])
            ->first();

            if (!$clienteBej) {
                throw new \Exception("Cliente no encontrado en Bejerman con email: {$user->email}");
            }

            // 2. Sincronizar descuentos desde Bejerman
            $descuentos = $this->obtenerDescuentos($clienteBej);

            // 3. Actualizar datos del usuario
            $user->update([
                'bejerman_cliente_cod' => $clienteBej->cli_Cod,
                'razon_social' => $clienteBej->cli_RazSoc,
                'cuit' => $clienteBej->cli_CUIT,
                'direccion' => $clienteBej->cli_Direc,
                'localidad' => $clienteBej->cli_Loc,
                'telefono' => $clienteBej->cli_Tel,
                'bejerman_lista_precio_cod' => $clienteBej->clidlp_Cod,
                'bejerman_descuento_comercial_cod' => $clienteBej->clidco_Cod,
                'descuento_uno' => $descuentos['tasa1'],
                'descuento_dos' => $descuentos['tasa2'],
                'descuento_tres' => $descuentos['tasa3'],
            ]);

            // 4. Log de sincronización exitosa
            SincronizacionBejermanLog::create([
                'tipo' => 'cliente',
                'user_id' => $user->id,
                'accion' => 'sincronizacion_inicial',
                'detalles' => [
                    'bejerman_cod' => $clienteBej->cli_Cod,
                    'razon_social' => $clienteBej->cli_RazSoc,
                    'lista_precio' => $clienteBej->clidlp_Cod,
                    'descuento_comercial' => $clienteBej->clidco_Cod,
                    'descuentos' => $descuentos,
                ],
                'resultado' => 'exito',
            ]);

            DB::commit();

            Log::info("✅ Cliente sincronizado", [
                'user_id' => $user->id,
                'bejerman_cod' => $clienteBej->cli_Cod,
            ]);

            return [
                'success' => true,
                'cliente' => $clienteBej,
                'descuentos' => $descuentos,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            // Log de error
            SincronizacionBejermanLog::create([
                'tipo' => 'cliente',
                'user_id' => $user->id,
                'accion' => 'sincronizacion_inicial',
                'resultado' => 'error',
                'mensaje_error' => $e->getMessage(),
            ]);

            Log::error("❌ Error sincronizando cliente", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtener descuentos del cliente desde Bejerman
     */
   private function obtenerDescuentos($clienteBej)
{
    $descuento = DescuentoComercial::where('dco_Cod', $clienteBej->clidco_Cod)->first();

    if (!$descuento) {
        return [
            'tasa1' => 0,
            'tasa2' => 0,
            'tasa3' => 0,
        ];
    }

    // Convertir a valores absolutos (positivos)
    return [
        'tasa1' => abs((int) $descuento->dco_Tasa1),  // ← Agregar abs()
        'tasa2' => abs((int) $descuento->dco_Tasa2),  // ← Agregar abs()
        'tasa3' => abs((int) round($descuento->dco_Tasa3)),  // ← Agregar abs() y round
    ];
}

    /**
     * Verificar si un email existe en Bejerman
     */
    public function existeClienteEnBejerman(string $email): bool
    {
        return Cliente::where('cli_EMail', 'LIKE', "%{$email}%")
            ->where('cli_Habilitado', true)
            ->exists();
    }

    /**
     * Re-sincronizar descuentos de un cliente ya autorizado
     */
    public function resincronizarDescuentos(User $user)
    {
        if (!$user->bejerman_cliente_cod) {
            throw new \Exception("Usuario no tiene código de Bejerman asociado");
        }

        $clienteBej = Cliente::where('cli_Cod', $user->bejerman_cliente_cod)
            ->with('descuentoComercial')
            ->first();

        if (!$clienteBej) {
            throw new \Exception("Cliente no encontrado en Bejerman");
        }

        $descuentos = $this->obtenerDescuentos($clienteBej);

        $user->update([
            'bejerman_lista_precio_cod' => $clienteBej->clidlp_Cod,
            'bejerman_descuento_comercial_cod' => $clienteBej->clidco_Cod,
            'descuento_uno' => $descuentos['tasa1'],
            'descuento_dos' => $descuentos['tasa2'],
            'descuento_tres' => $descuentos['tasa3'],
        ]);

        SincronizacionBejermanLog::create([
            'tipo' => 'cliente',
            'user_id' => $user->id,
            'accion' => 'resincronizacion_descuentos',
            'detalles' => $descuentos,
            'resultado' => 'exito',
        ]);

        return $descuentos;
    }
}