<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sincronizacion_bejerman_log', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['cliente', 'pedido', 'producto', 'precio']);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('pedido_id')->nullable();
            $table->string('accion', 100);
            $table->text('detalles')->nullable();
            $table->enum('resultado', ['exito', 'error'])->default('exito');
            $table->text('mensaje_error')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sincronizacion_bejerman_log');
    }
};