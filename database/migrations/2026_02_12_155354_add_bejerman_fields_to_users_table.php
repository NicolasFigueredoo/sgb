<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Campos de integración con Bejerman
            $table->string('bejerman_cliente_cod')->nullable()->after('email');
            $table->timestamp('fecha_autorizacion')->nullable()->after('autorizado');
            $table->unsignedBigInteger('autorizado_por')->nullable()->after('fecha_autorizacion');
            
            // Códigos de descuento y lista de precios de Bejerman
            $table->string('bejerman_lista_precio_cod')->nullable()->after('lista_de_precios_id');
            $table->string('bejerman_descuento_comercial_cod')->nullable()->after('bejerman_lista_precio_cod');
            
            // Foreign key para quien autorizó
            $table->foreign('autorizado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['autorizado_por']);
            $table->dropColumn([
                'bejerman_cliente_cod',
                'fecha_autorizacion',
                'autorizado_por',
                'bejerman_lista_precio_cod',
                'bejerman_descuento_comercial_cod',
            ]);
        });
    }
};