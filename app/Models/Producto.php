<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table   = 'productos';
    protected $guarded = [];
    public $timestamps = false;


    // Relación: Un producto pertenece a una categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id'); // Clave foránea en la tabla productos
    }

    // Relación: Un producto pertenece a una categoría
    public function stocks()
    {
        return $this->hasMany(DetCompra::class, 'producto_id');
        // return $this->hasMany(DetCompra::class, 'producto_id', 'id')->sum('stock');
        // return $this->hasMany(DetCompra::class, 'producto_id')->where('stock', '>', '0')->sum('stock');
    }


}
