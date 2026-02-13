<?php

namespace App\Models\Bejerman;

use Illuminate\Database\Eloquent\Model;

class ListaPrecio extends Model
{
    protected $connection = 'bejerman';
    protected $table = 'ListaPrec';
    public $timestamps = false;

    protected $fillable = [
        'lprdlp_Cod',
        'lprart_CodGen',
        'lpr_Precio',
    ];

    protected $casts = [
        'lpr_Precio' => 'decimal:2',
    ];
}