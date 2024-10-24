<?php

namespace App\Models;

use App\Livewire\Ventas\Ventas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    protected $table   = 'cuentas' ;
    protected $guarded = [];
    public $timestamps = false;

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }

    public function compras(){
        return $this->hasMany(Compra::class);
    }

    public function ventas(){
        return $this->hasMany(Venta::class);
    }


}
