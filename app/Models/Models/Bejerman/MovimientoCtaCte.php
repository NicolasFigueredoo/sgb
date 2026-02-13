<?php

namespace App\Models\Bejerman;

use Illuminate\Database\Eloquent\Model;

class MovimientoCtaCte extends Model
{
    protected $connection = 'bejerman';
    protected $table = 'MovCC';
    protected $primaryKey = 'mcc_ID';
    public $timestamps = false;

    // Relación con cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'mcccli_Cod', 'cli_Cod');
    }

    // Relación con comprobante (factura)
    public function comprobante()
    {
        return $this->belongsTo(PedidoVenta::class, 'mcccve_ID', 'cve_ID');
    }
}