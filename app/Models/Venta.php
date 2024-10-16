<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table   = 'ventas' ;
    protected $guarded = [];
    public $timestamps = false;



    public function productos()
    {
        return $this->hasMany(VentaProducto::class);
    }
}
