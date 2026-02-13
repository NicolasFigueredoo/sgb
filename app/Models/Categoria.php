<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $guarded = [];

    public function productos()
    {
        return $this->hasMany(Producto::class)->orderBy('order');
    }

 public function getImageAttribute($value)
{
    if ($value) {
        return url("storage/" . $value);
    }
    
    // Fallback al logo
    return asset('storage/images/logo.png');
}

    public function subCategorias()
    {
        return $this->hasMany(SubCategoria::class)->orderBy('order');
    }
}
