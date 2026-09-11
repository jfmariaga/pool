<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestamoCliente extends Model
{
    protected $table = 'prestamo_clientes';
    protected $guarded = [];

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'cliente_id');
    }
}
