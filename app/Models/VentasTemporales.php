<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentasTemporales extends Model
{
    protected $table   = 'ventas_temporales';
    protected $guarded = [];
    public $timestamps = false;

     public function productos()
     {
         return $this->hasMany(ProductoVentaTemporal::class,'venta_temporal_id');
     }
}
