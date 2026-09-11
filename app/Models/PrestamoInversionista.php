<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestamoInversionista extends Model
{
    protected $table = 'prestamo_inversionistas';
    protected $guarded = [];

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'inversionista_id');
    }
}
