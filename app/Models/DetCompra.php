<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetCompra extends Model
{
    protected $table   = 'det_compras' ;
    protected $guarded = [];
    public $timestamps = false;
    
    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function socio()
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }

    // aportes de los socios cuando la prenda se compró en sociedad entre varios
    public function socios()
    {
        return $this->hasMany(DetCompraSocio::class, 'det_compra_id');
    }
}
