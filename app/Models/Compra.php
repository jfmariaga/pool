<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table   = 'compras' ;
    protected $guarded = [];
    public $timestamps = false;

    public function detalles()
    {
        return $this->hasMany(DetCompra::class, 'compra_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedores::class, 'proveedor_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id', 'id');
    }

    public function adjuntos()
    {
        return $this->hasMany(Adjunto::class);
    }

}
