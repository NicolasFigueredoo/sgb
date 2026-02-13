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

            DB::connection('bejerman')->transaction(function () use ($pedido) {

                DB::connection('bejerman')
                    ->table('CabVenta')
                    ->insert([
                        'cveemp_Codigo' => 'JOYR',
                        'cvesuc_Cod' => ' ',

                        'cve_CodCli' => $pedido->user->bejerman_cliente_cod,
                        'cvecli_CodIN' => $pedido->user->bejerman_cliente_cod,
                        'cvecli_RazSoc' => $pedido->user->razon_social ?? 'Cliente',
                        'cvecli_CUIT' => $pedido->user->cuit,
                        'cvecli_NroIB' => ' ',

                        'cve_NroCuota' => ' ',
                        'cveptr_Cod' => 'VEN',
                        'cvepre_Cod' => ' ',
                        'cve_CodPvt' => '00003',
                        'cvepvt_CodIN' => '00003',
                        'cve_Letra' => 'X',
                        'cvetco_Cod' => 'NP',
                        'cve_Nro' => '00000001',
                        'cvetic_Cod' => 'B',
                        'cve_NroHasta' => ' ',

                        'cve_FEmision' => now(),
                        'cve_HEmision' => now(),
                        'cve_FContab' => now(),
                        'cve_FecMod' => now(),

                        'cve_IncluDDJJ' => 0,
                        'cve_Orig' => 'O',
                        'cve_Circuito' => 'V',
                        'cve_MarcaCdoCtaCteBcoTarj' => '2',
                        'cve_PasajeCG' => 'V',
                        'cve_PasadoCG' => 'N',
                        'cve_OrigenComp' => 'E',
                        'cve_CodApe' => ' ',
                        'cveusu_Codigo' => 'WEB',
                        'cve_Convert' => ' ',
                        'cve_EsDifCambio' => 'N',

                        'cvemon_Codigo' => '1',
                        'cvemcot_Cotiza' => 1,
                        'cvemon_CodigoCC' => '1',
                        'cvemtca_CodigoCC' => 'UNI',
                        'cvemcot_CotizaCC' => 1,
                        'cve_ImpMonLoc' => $pedido->total,
                        'cve_ImpMonCC' => $pedido->total,
                        'cve_ImpMonEmis' => $pedido->total,
                        'cve_SaldoMonLoc' => $pedido->total,
                        'cve_SaldoMonCC' => $pedido->total,
                        'cvecvt_Cod' => '2',
                        'cve_FVto' => now(),
                        'cve_PorcCuotaComp' => 1,
                        'cve_CoefFinImpl' => 0,
                        'cve_FCCreditoAsoc' => 0,
                        'cve_PasadoACC' => 0,
                        'cve_Anulado' => 0,
                        'cvepai_Cod' => 'ARG',
                        'cve_Motivo' => '',
                        'cvesiv_Cod' => '1',
                        'cvetdc_Cod' => '1',
                        'cveprv_CodigoIB' => '002',
                        'cveprv_Codigo' => '002',
                        'cveven_Cod' => 'JOY',
                        'cve_ConClaus' => '0',
                        'cvedco_Cod' => '2',
                        'cvedc1_Cod' => 'REP',
                        'cvedc2_Cod' => 'CMOT',
                        'cvetal_Cod' => 'FAE',
                        'cveemp_CodigoSCV' => 'JOYR',
                        'cvesuc_CodSCV' => ' ',
                        'cvertd_MaxLinDet' => 0,
                    ]);


                $cabeceraId = DB::connection('bejerman')
    ->select("SELECT CAST(IDENT_CURRENT('CabVenta') AS INT) as id")[0]->id;

                // ✅ lo que encontraste en la DB
                $partidaDefault = '10-2021';

                $reng = 1;
                foreach ($pedido->productos as $item) {
                    // ✅ Buscar el artículo en Bejerman para obtener los códigos completos
                    $articuloBej = DB::connection('bejerman')
                        ->table('Articulos')
                        ->where('art_CodGen', trim($item->producto->code))
                        ->first();

                    // Si no existe el artículo, usar valores por defecto
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

                        // ✅ USAR CÓDIGOS COMPLETOS DEL ARTÍCULO
                        'iveart_CodGen' => $codGen,
                        'iveart_CodEle1' => $codEle1,
                        'iveart_CodEle2' => $codEle2,
                        'iveart_CodEle3' => $codEle3,

                        'ivecon_Cod' => 'NC',

                        // ✅ DESCRIPCIÓN COMPLETA
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
                        'total' => $pedido->total,
                        'items_count' => $pedido->productos->count(),
                    ]),
                    'resultado' => 'exito',
                ]);

                Log::info("✅ Pedido enviado a Bejerman", [
                    'pedido_id' => $pedido->id,
                    'bejerman_id' => $cabeceraId,
                ]);
            });

            return [
                'success' => true,
                'bejerman_id' => $pedido->fresh()->bejerman_id,
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
