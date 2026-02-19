<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\SincronizacionBejermanLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnvioPedidoBejermanService
{
    /**
     * Enviar pedido a Bejerman
     */
  public function enviarPedido(Pedido $pedido)
{
    try {
        $pedido->loadMissing('user', 'productos.producto');

        if (!$pedido->user->bejerman_cliente_cod) {
            throw new \Exception("Usuario no tiene código de cliente en Bejerman");
        }

        if ($pedido->bejerman_id) {
            throw new \Exception("Este pedido ya fue enviado a Bejerman (ID: {$pedido->bejerman_id})");
        }

        $pedido->update(['estado_envio_bejerman' => 'enviando']);

        $cabeceraId = null;

        DB::connection('bejerman')->transaction(function () use ($pedido, &$cabeceraId) {

            // ✅ OBTENER PRÓXIMO ID MANUALMENTE
            $proximoId = DB::connection('bejerman')->select("
                SELECT ISNULL(MAX(cve_ID), 0) + 1 as proximo_id 
                FROM CabVenta
            ")[0]->proximo_id;

            // ✅ OBTENER PRÓXIMO NÚMERO DE NP (correlativo real de Bejerman)
            $proximoNro = DB::connection('bejerman')->select("
                SELECT ISNULL(MAX(CAST(cve_Nro AS INT)), 21311) + 1 as proximo_nro
                FROM CabVenta
                WHERE cvetco_Cod = 'NP' AND cveptr_Cod = 'VEN'
            ")[0]->proximo_nro;
            $proximoNroFormateado = str_pad($proximoNro, 8, '0', STR_PAD_LEFT);

            // ✅ INSERT CON ID Y NÚMERO DINÁMICO
            DB::connection('bejerman')->insert("
                INSERT INTO CabVenta (
                    cve_ID,
                    cveemp_Codigo, cvesuc_Cod, cve_CodCli, cvecli_CodIN, cvecli_RazSoc,
                    cvecli_CUIT, cvecli_NroIB, cve_NroCuota, cveptr_Cod, cvepre_Cod,
                    cve_CodPvt, cvepvt_CodIN, cve_Letra, cvetco_Cod, cve_Nro, cvetic_Cod,
                    cve_NroHasta, cve_FEmision, cve_HEmision, cve_FContab, cve_FecMod,
                    cve_IncluDDJJ, cve_Orig, cve_Circuito, cve_MarcaCdoCtaCteBcoTarj,
                    cve_PasajeCG, cve_PasadoCG, cve_OrigenComp, cve_CodApe, cveusu_Codigo,
                    cve_Convert, cve_EsDifCambio, cvemon_Codigo, cvemcot_Cotiza,
                    cvemon_CodigoCC, cvemtca_CodigoCC, cvemcot_CotizaCC, cve_ImpMonLoc,
                    cve_ImpMonCC, cve_ImpMonEmis, cve_SaldoMonLoc, cve_SaldoMonCC,
                    cvecvt_Cod, cve_FVto, cve_PorcCuotaComp, cve_CoefFinImpl,
                    cve_FCCreditoAsoc, cve_PasadoACC, cve_Anulado, cvepai_Cod,
                    cve_Motivo, cvesiv_Cod, cvetdc_Cod, cveprv_CodigoIB, cveprv_Codigo,
                    cveven_Cod, cve_ConClaus, cvedco_Cod, cvedc1_Cod, cvedc2_Cod,
                    cvetal_Cod, cveemp_CodigoSCV, cvesuc_CodSCV, cvertd_MaxLinDet
                )
                VALUES (
                    ?,
                    'JOYR', ' ', ?, ?, ?, ?, ' ', ' ', 'VEN', ' ',
                    '00003', '00003', 'X', 'NP', ?, 'B', ' ',
                    GETDATE(), GETDATE(), GETDATE(), GETDATE(),
                    0, 'O', 'V', '2', 'V', 'N', 'E', ' ', 'WEB',
                    ' ', 'N', '1', 1, '1', 'UNI', 1, ?, ?, ?, ?, ?,
                    '2', GETDATE(), 1, 0, 0, 0, 0, 'ARG', '',
                    '1', '1', '002', '002', 'JOY', '0', '2', 'REP', 'CMOT',
                    'FAE', 'JOYR', ' ', 0
                )
            ", [
                $proximoId,
                $pedido->user->bejerman_cliente_cod,
                $pedido->user->bejerman_cliente_cod,
                $pedido->user->razon_social ?? 'Cliente',
                $pedido->user->cuit,
                $proximoNroFormateado,  // ✅ número dinámico
                $pedido->total,
                $pedido->total,
                $pedido->total,
                $pedido->total,
                $pedido->total,
            ]);

            $cabeceraId = $proximoId;

            // Partida por defecto
            $partidaDefault = '10-2021';

            $reng = 1;
            foreach ($pedido->productos as $item) {
                $articuloBej = DB::connection('bejerman')
                    ->table('Articulos')
                    ->where('art_CodGen', trim($item->producto->code))
                    ->first();

                $codGen = $articuloBej ? trim($articuloBej->art_CodGen) : trim(mb_substr($item->producto->code ?? ('PROD-' . $item->producto_id), 0, 20));
                $codEle1 = $articuloBej ? trim($articuloBej->art_CodEle1) : ' ';
                $codEle2 = $articuloBej ? trim($articuloBej->art_CodEle2) : ' ';
                $codEle3 = $articuloBej ? trim($articuloBej->art_CodEle3) : ' ';
                $desc = $articuloBej ? mb_substr($articuloBej->art_DescGen . ' ' . $articuloBej->artele_Desc1, 0, 50) : mb_substr($item->producto->name ?? 'Producto', 0, 50);

                DB::connection('bejerman')->table('ItemVta')->insert([
                    'iveemp_Codigo' => 'JOYR',
                    'ivesuc_Cod' => ' ',
                    'ivecve_ID' => $cabeceraId,
                    'ive_NReng' => $reng,
                    'ive_FContab' => now(),
                    'ive_FDDJJ' => now(),
                    'ive_IncluDDJJ' => 0,
                    'ive_tipoIt' => 'A',
                    'iveart_CodGen' => $codGen,
                    'iveart_CodEle1' => $codEle1,
                    'iveart_CodEle2' => $codEle2,
                    'iveart_CodEle3' => $codEle3,
                    'ivecon_Cod' => 'NC',
                    'ive_Desc' => $desc,
                    'ive_TipoArt' => '1',
                    'ive_TipoConc' => ' ',
                    'ive_CantUM1' => $item->cantidad,
                    'ive_CantUM2' => 0,
                    'ive_NetoLoc' => $item->precio_unitario ?? 0,
                    'ive_NetoCC' => $item->precio_unitario ?? 0,
                    'ive_TipoTasa' => '1',
                    'ive_TInsc' => 0,
                    'ive_TNoInsc' => 0,
                    'ive_IInscLoc' => 0,
                    'ive_IInscCC' => 0,
                    'ive_INoInscLoc' => 0,
                    'ive_INoInscCC' => 0,
                    'ive_INoGraLoc' => 0,
                    'ive_INoGraCC' => 0,
                    'ive_TotDtoArtLoc' => 0,
                    'ive_TotDtoArtCC' => 0,
                    'ive_TotDtoFinLoc' => 0,
                    'ive_TotDtoFinCC' => 0,
                    'ive_TotDtoComLoc' => 0,
                    'ive_TotDtoComCC' => 0,
                    'ive_TotDtoPieLoc' => 0,
                    'ive_TotDtoPieCC' => 0,
                    'ive_PrCosto' => 0,
                    'ive_PasadoACC' => 0,
                    'ive_Kit' => ' ',
                    'ive_RengKit' => 0,
                    'ive_BUso' => 'N',
                    'ivestp_Partida' => $partidaDefault,
                    'ivecve_FEmision' => now(),
                    'ivecve_HEmision' => now(),
                    'iveptr_Cod' => 'VEN',
                    'ivetco_Cod' => 'NP',
                    'ivecve_CodCli' => $pedido->user->bejerman_cliente_cod,
                    'ivecli_CodIN' => $pedido->user->bejerman_cliente_cod,
                    'ive_OrigenComp' => 'E',
                    'ive_NoComputable' => 0,
                ]);

                $reng++;
            }

            $pedido->update([
                'bejerman_id' => $cabeceraId,
                'estado_envio_bejerman' => 'enviado',
                'error_bejerman' => null,
            ]);

            SincronizacionBejermanLog::create([
                'tipo' => 'pedido',
                'user_id' => $pedido->user_id,
                'pedido_id' => $pedido->id,
                'accion' => 'envio_pedido',
                'detalles' => json_encode([
                    'bejerman_id' => $cabeceraId,
                    'nro_comprobante' => $proximoNroFormateado,
                    'total' => $pedido->total,
                    'items_count' => $pedido->productos->count(),
                ]),
                'resultado' => 'exito',
            ]);

            Log::info("✅ Pedido enviado a Bejerman", [
                'pedido_id' => $pedido->id,
                'bejerman_id' => $cabeceraId,
                'nro_comprobante' => $proximoNroFormateado,
            ]);
        });

        return [
            'success' => true,
            'bejerman_id' => $cabeceraId,
        ];

    } catch (\Exception $e) {

        $pedido->update([
            'estado_envio_bejerman' => 'error',
            'error_bejerman' => $e->getMessage(),
        ]);

        SincronizacionBejermanLog::create([
            'tipo' => 'pedido',
            'user_id' => $pedido->user_id,
            'pedido_id' => $pedido->id,
            'accion' => 'envio_pedido',
            'resultado' => 'error',
            'mensaje_error' => $e->getMessage(),
        ]);

        Log::error("❌ Error enviando pedido a Bejerman", [
            'pedido_id' => $pedido->id,
            'error' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}
}