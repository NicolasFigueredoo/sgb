<?php

namespace App\Models\Bejerman;

use Illuminate\Database\Eloquent\Model;

class ItemVenta extends Model
{
    protected $connection = 'bejerman';
    protected $table = 'ItemVta';
    protected $primaryKey = 'ive_ID';
    public $timestamps = false;

    protected $fillable = [
        'ivecve_ID',
        'iveart_CodGen',
        'ive_Desc',
        'ive_Cant',
        'ive_PreUni',
        'ive_TotDtoArtLoc',
        'ive_ImpTotLoc',
    ];

    protected $casts = [
        'ive_Cant' => 'integer',
        'ive_PreUni' => 'decimal:2',
        'ive_TotDtoArtLoc' => 'decimal:2',
        'ive_ImpTotLoc' => 'decimal:2',
    ];

    // Relaciones
    public function pedido()
    {
        return $this->belongsTo(PedidoVenta::class, 'ivecve_ID', 'cve_ID');
    }
}