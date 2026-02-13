<?php

namespace App\Models\Bejerman;

use Illuminate\Database\Eloquent\Model;

class PedidoVenta extends Model
{
    protected $connection = 'bejerman';
    protected $table = 'CabVenta';
    protected $primaryKey = 'cve_ID';
    public $timestamps = false;

    protected $fillable = [
        'cve_CodCli',
        'cve_FEmision',
        'cve_ImpMonLoc',
        'cve_SaldoMonLoc',
        'cvetco_Cod',
        'cve_Nro',
        'cve_estado',
    ];

    protected $casts = [
        'cve_FEmision' => 'datetime',
        'cve_ImpMonLoc' => 'decimal:2',
        'cve_SaldoMonLoc' => 'decimal:2',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cve_CodCli', 'cli_Cod');
    }

    public function items()
    {
        return $this->hasMany(ItemVenta::class, 'ivecve_ID', 'cve_ID');
    }

    // Accessor para estado legible
    public function getEstadoTextoAttribute()
    {
        $estados = [
            '0' => 'Pendiente',
            '1' => 'En Proceso',
            '2' => 'Confirmado',
            '3' => 'Enviado',
            '4' => 'Entregado',
            '5' => 'Cancelado',
        ];

        return $estados[$this->cve_estado] ?? 'Desconocido';
    }
}