<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetAjusteInventario extends Model
{
    protected $table   = 'det_ajuste_inventario' ;
    protected $guarded = [];
    public $timestamps = false;

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
