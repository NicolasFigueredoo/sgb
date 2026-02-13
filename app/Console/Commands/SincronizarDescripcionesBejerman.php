<?php

namespace App\Console\Commands;

use App\Models\Producto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SincronizarDescripcionesBejerman extends Command
{
    protected $signature = 'bejerman:sync-descripciones';
    protected $description = 'Sincroniza descripciones desde Bejerman';

    public function handle()
    {
        $this->info('🚀 Sincronizando descripciones desde Bejerman...');
        
        $productos = Producto::whereNotNull('code')->get();
        $actualizados = 0;
        $noEncontrados = 0;
        
        $bar = $this->output->createProgressBar($productos->count());
        $bar->start();
        
        foreach ($productos as $producto) {
            $articulo = DB::connection('bejerman')
                ->table('Articulos')
                ->where('art_CodGen', trim($producto->code))
                ->first();
            
            if ($articulo) {
                // ✅ CONSTRUIR DESCRIPCIÓN COMPLETA
                $descripcion = trim($articulo->art_DescGen . ' ' . $articulo->artele_Desc1);
                $descripcion = preg_replace('/\s+/', ' ', $descripcion);
                
                // ✅ ACTUALIZAR NAME CON LA DESCRIPCIÓN
                $producto->update([
                    'name' => $descripcion,  // ← NAME = DESCRIPCIÓN TÉCNICA
                    'codigo_barras' => $articulo->art_CodBarras,
                ]);
                
                $actualizados++;
            } else {
                $noEncontrados++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("✅ Actualizados: {$actualizados}");
        $this->warn("⚠️  No encontrados: {$noEncontrados}");
        $this->info("🎉 Sincronización completada!");
        
        return 0;
    }
}