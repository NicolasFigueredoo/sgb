<?php

namespace App\Models\Bejerman;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $connection = 'bejerman';
    protected $table = 'Clientes';
    protected $primaryKey = 'cli_Cod';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'cli_Cod',
        'cli_RazSoc',
        'cli_NomFantasia',
        'cli_EMail',
        'cli_CUIT',
        'cli_Direc',
        'cli_Loc',
        'cli_Tel',
        'cli_Habilitado',
        'clidlp_Cod',  // Lista de precios
        'clidco_Cod',  // Descuento comercial
        'clicvt_Cod',  // Condición de venta
    ];

    protected $casts = [
        'cli_Habilitado' => 'boolean',
    ];

    // Relaciones
    public function listaPrecio()
    {
        return $this->belongsTo(ListaPrecio::class, 'clidlp_Cod', 'dlp_Cod');
    }

    public function descuentoComercial()
    {
        return $this->belongsTo(DescuentoComercial::class, 'clidco_Cod', 'dco_Cod');
    }

    public function pedidos()
    {
        return $this->hasMany(PedidoVenta::class, 'cve_CodCli', 'cli_Cod');
    }
}