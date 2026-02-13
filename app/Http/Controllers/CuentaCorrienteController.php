<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuentaCorrienteController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->bejerman_cliente_cod) {
            return inertia('privada/cuenta-corriente', [
                'movimientos' => [],
                'saldo' => 0,
                'mensaje' => 'Usuario no vinculado con Bejerman',
            ]);
        }

        // Obtener movimientos desde CabVenta
        $movimientos = DB::connection('bejerman')
            ->table('CabVenta')
            ->where('cve_CodCli', $user->bejerman_cliente_cod)
            ->whereIn('cvetco_Cod', ['FC', 'ND', 'NC', 'RC'])
            ->orderBy('cve_FEmision', 'asc') // ✅ ORDENAR ASC para calcular saldo
            ->orderBy('cve_ID', 'asc')
            ->get();

        // ✅ CALCULAR SALDO ACUMULADO
        $saldoAcumulado = 0;
        $movimientosConSaldo = $movimientos->map(function($mov) use (&$saldoAcumulado) {
            $debe = $this->calcularDebe($mov);
            $haber = $this->calcularHaber($mov);
            
            // Calcular saldo acumulado
            $saldoAcumulado += $debe - $haber;
            
            return [
                'id' => $mov->cve_ID,
                'fecha' => $mov->cve_FEmision,
                'tipo_comprobante' => $mov->cvetco_Cod,
                'numero_comprobante' => $this->formatearNumeroComprobante($mov),
                'descripcion' => $this->obtenerDescripcion($mov->cvetco_Cod),
                'debe' => $debe,
                'haber' => $haber,
                'saldo' => $saldoAcumulado, // ✅ SALDO ACUMULADO
            ];
        });

        // ✅ INVERTIR ORDEN PARA MOSTRAR MÁS RECIENTES PRIMERO
        $movimientosOrdenados = $movimientosConSaldo->reverse()->values();

        // El último saldo acumulado es el saldo actual
        $saldoActual = $saldoAcumulado;

        return inertia('privada/cuenta-corriente', [
            'movimientos' => $movimientosOrdenados,
            'saldo' => $saldoActual,
            'cliente' => [
                'nombre' => $user->razon_social ?? $user->name,
                'codigo' => $user->bejerman_cliente_cod,
            ]
        ]);
    }

    private function formatearNumeroComprobante($mov)
    {
        $tipo = $mov->cvetco_Cod ?? '';
        $letra = $mov->cve_Letra ?? '';
        $numero = str_pad($mov->cve_Nro ?? '0', 8, '0', STR_PAD_LEFT);
        
        return $tipo . $letra . $numero;
    }

    private function obtenerDescripcion($tipo)
    {
        $descripciones = [
            'FC' => 'Factura',
            'NC' => 'Nota de Crédito',
            'ND' => 'Nota de Débito',
            'RC' => 'Recibo',
            'NP' => 'Nota de Pedido',
        ];

        return $descripciones[$tipo] ?? $tipo;
    }
    
    private function calcularDebe($mov)
    {
        // Facturas y Notas de Débito son DEBE
        if (in_array($mov->cvetco_Cod, ['FC', 'ND'])) {
            return $mov->cve_ImpMonLoc ?? 0;
        }
        return 0;
    }
    
    private function calcularHaber($mov)
    {
        // Notas de Crédito y Recibos son HABER
        if (in_array($mov->cvetco_Cod, ['NC', 'RC'])) {
            return $mov->cve_ImpMonLoc ?? 0;
        }
        return 0;
    }
}