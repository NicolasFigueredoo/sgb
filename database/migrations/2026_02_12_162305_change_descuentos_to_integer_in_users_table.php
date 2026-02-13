<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('descuento_uno')->default(0)->change();
            $table->integer('descuento_dos')->default(0)->change();
            $table->integer('descuento_tres')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('descuento_uno')->default(0)->change();
            $table->unsignedInteger('descuento_dos')->default(0)->change();
            $table->unsignedInteger('descuento_tres')->default(0)->change();
        });
    }
};