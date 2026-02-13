<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Descripción técnica completa del producto
            $table->text('descripcion')->nullable()->after('name');
            
            // Aplicación (vehículos, motores, etc.)
            $table->text('aplicacion')->nullable()->after('descripcion');
            
            // Características técnicas
            $table->text('caracteristicas')->nullable()->after('aplicacion');
            
            // Código de barras
            $table->string('codigo_barras')->nullable()->after('code_oem');
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'aplicacion', 'caracteristicas', 'codigo_barras']);
        });
    }
};