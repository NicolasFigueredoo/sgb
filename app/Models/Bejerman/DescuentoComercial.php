<?php

namespace App\Models\Bejerman;

use Illuminate\Database\Eloquent\Model;

class DescuentoComercial extends Model
{
    protected $connection = 'bejerman';
    protected $table = 'DescCom';
    protected $primaryKey = 'dco_Cod';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'dco_Cod',
        'dco_Desc',
        'dco_Tasa1',
        'dco_Tasa2',
        'dco_Tasa3',
    ];

    protected $casts = [
        'dco_Tasa1' => 'decimal:2',
        'dco_Tasa2' => 'decimal:2',
        'dco_Tasa3' => 'decimal:2',
    ];
}