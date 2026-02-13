<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerificarPedidoBejerman extends Command
{
    protected $signature = 'verificar:bejerman {id}';
    
    public function handle()
    {
        $id = $this->argument('id');
        
        $this->info("Buscando pedido {$id} en Bejerman...");
        
        $pedido = DB::connection('bejerman')
            ->table('CabVenta')
            ->where('cve_ID', $id)
            ->first();
        
        if (!$pedido) {
            $this->error("❌ Pedido {$id} NO existe");
            return 1;
        }
        
        $this->info("✅ Pedido {$id} EXISTE");
        $this->newLine();
        
        $this->line("Empresa: " . ($pedido->cveemp_Codigo ?? 'NULL'));
        $this->line("Tipo: " . ($pedido->cvetco_Cod ?? 'NULL'));
        $this->line("Número: " . ($pedido->cve_Nro ?? 'NULL'));
        $this->line("Cliente: " . ($pedido->cve_CodCli ?? 'NULL'));
        $this->line("Razón Social: " . ($pedido->cvecli_RazSoc ?? 'NULL'));
        $this->line("Fecha: " . ($pedido->cve_FEmision ?? 'NULL'));
        $this->line("Total: $" . ($pedido->cve_ImpMonLoc ?? 0));
        
        $items = DB::connection('bejerman')
            ->table('ItemVta')
            ->where('ivecve_ID', $id)
            ->get();
        
        $this->newLine();
        $this->info("Items: {$items->count()}");
        
        foreach ($items as $item) {
            $this->line("  - {$item->iveart_CodGen} | {$item->ive_Desc} | Cant: {$item->ive_CantUM1}");
        }
        
        // Ver últimos pedidos del cliente
        if ($pedido->cve_CodCli) {
            $this->newLine();
            $this->info("Últimos pedidos del cliente {$pedido->cve_CodCli}:");
            
            $otros = DB::connection('bejerman')
                ->table('CabVenta')
                ->where('cve_CodCli', $pedido->cve_CodCli)
                ->where('cve_ID', '>', 0)
                ->orderBy('cve_ID', 'desc')
                ->take(5)
                ->get();
            
            foreach ($otros as $p) {
                $this->line("  ID: {$p->cve_ID} | {$p->cvetco_Cod} | Fecha: {$p->cve_FEmision} | \${$p->cve_ImpMonLoc}");
            }
        }
        
        return 0;
    }
}