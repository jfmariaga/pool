<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table   = 'ventas' ;
    protected $guarded = [];
    public $timestamps = false;



    // public function productos()
    // {
    //     return $this->hasMany(VentaProducto::class);
    // }
     public function detVentas(){
        return $this->hasMany(DetVenta::class);
     }

     public function usuario(){
        return $this->belongsTo(User::class, 'user_id');
     }

     public function cuenta(){
        return $this->belongsTo(Cuenta::class);
     }
}
