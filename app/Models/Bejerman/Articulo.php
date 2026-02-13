<?php

namespace App\Models\Bejerman;

use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    protected $connection = 'bejerman';
    protected $table = 'Articulos';
    protected $primaryKey = 'art_CodGen';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}