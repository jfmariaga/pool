<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoVentaTemporal extends Model
{
    protected $table   = 'productos_ventas_temporales' ;
    protected $guarded = [];
    public $timestamps = false;

     // Relación con el modelo Producto
     public function producto()
     {
         return $this->belongsTo(Producto::class);
     }

     // Relación con el modelo VentasTemporales
     public function ventaTemporal()
     {
         return $this->belongsTo(VentasTemporales::class);
     }
}
