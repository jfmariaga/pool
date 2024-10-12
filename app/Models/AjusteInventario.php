<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AjusteInventario extends Model
{
    protected $table   = 'ajuste_inventario' ;
    protected $guarded = [];
    public $timestamps = false;

    public function detalles()
    {
        return $this->hasMany(DetAjusteInventario::class, 'ajuste_id');
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
