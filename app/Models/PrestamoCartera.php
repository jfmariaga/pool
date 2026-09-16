<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestamoCartera extends Model
{
    protected $table = 'prestamo_carteras';
    protected $guarded = [];

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'cartera_id');
    }
}
