<?php

namespace App\Console\Commands;

use App\Models\Pedido;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerificarPedido extends Command
{
    protected $signature = 'pedido:verificar {id}';
    protected $description = 'Verifica si un pedido llegó a Bejerman';

    public function handle()
    {
        $pedidoId = $this->argument('id');
        $pedido = Pedido::with('user', 'productos.producto')->find($pedidoId);
        
        if (!$pedido) {
            $this->error("Pedido #{$pedidoId} no encontrado");
            return 1;
        }

        $this->info("=== PEDIDO #{$pedido->id} ===");
        $this->line("Cliente: {$pedido->user->name}");
        $this->line("Código Bejerman: {$pedido->user->bejerman_cliente_cod}");
        $this->line("Total: \${$pedido->total}");
        $this->line("Origen: {$pedido->origen}");
        $this->line("Bejerman ID: " . ($pedido->bejerman_id ?? 'NULL'));
        $this->line("Estado envío: {$pedido->estado_envio_bejerman}");
        
        if ($pedido->error_bejerman) {
            $this->error("Error: " . substr($pedido->error_bejerman, 0, 200));
        }

        if ($pedido->bejerman_id) {
            $this->newLine();
            $this->info("=== VERIFICANDO EN BEJERMAN ===");
            
            $pedidoBejerman = DB::connection('bejerman')
                ->table('CabVenta')
                ->where('cve_ID', $pedido->bejerman_id)
                ->first();
            
            if ($pedidoBejerman) {
                $this->info("✅ ENCONTRADO EN BEJERMAN");
                $this->line("ID: {$pedidoBejerman->cve_ID}");
                $this->line("Tipo: {$pedidoBejerman->cvetco_Cod}");
                $this->line("Total: \${$pedidoBejerman->cve_ImpMonLoc}");
                
                $items = DB::connection('bejerman')
                    ->table('ItemVta')
                    ->where('ivecve_ID', $pedido->bejerman_id)
                    ->get();
                
                $this->line("Items: {$items->count()}");
                foreach ($items as $item) {
                    $this->line("  - {$item->iveart_CodGen} x {$item->ive_CantUM1}");
                }
            } else {
                $this->error("❌ NO ENCONTRADO EN BEJERMAN");
            }
        } else {
            $this->warn("❌ No tiene bejerman_id (no fue enviado)");
        }
        
        return 0;
    }
}