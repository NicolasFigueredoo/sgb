<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->enum('origen', ['web', 'bejerman'])->default('web')->after('user_id');
            $table->integer('bejerman_id')->nullable()->after('origen');
            $table->enum('estado_envio_bejerman', ['pendiente', 'enviando', 'enviado', 'error'])->default('pendiente')->after('bejerman_id');
            $table->text('error_bejerman')->nullable()->after('estado_envio_bejerman');
            $table->decimal('descuento_total', 10, 2)->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn([
                'origen',
                'bejerman_id',
                'estado_envio_bejerman',
                'error_bejerman',
                'descuento_total',
            ]);
        });
    }
};