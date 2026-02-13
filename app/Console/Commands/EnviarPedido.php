<?php

namespace App\Console\Commands;

use App\Models\Pedido;
use App\Services\EnvioPedidoBejermanService;
use Illuminate\Console\Command;

class EnviarPedido extends Command
{
    protected $signature = 'pedido:enviar {id}';
    
    public function handle()
    {
        $pedido = Pedido::find($this->argument('id'));
        $service = app(EnvioPedidoBejermanService::class);
        $resultado = $service->enviarPedido($pedido);
        
        $this->info($resultado['success'] ? "✅ Enviado: {$resultado['bejerman_id']}" : "❌ Error: {$resultado['error']}");
    }
}