<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SincronizacionBejermanLog extends Model
{
    protected $table = 'sincronizacion_bejerman_log';

    protected $fillable = [
        'tipo',
        'user_id',
        'pedido_id',
        'accion',
        'detalles',
        'resultado',
        'mensaje_error',
    ];

    protected $casts = [
        'detalles' => 'array',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}