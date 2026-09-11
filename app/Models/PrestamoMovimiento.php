<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestamoMovimiento extends Model
{
    protected $table = 'prestamo_movimientos';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'fecha' => 'date',
        'fecha_corte_antes' => 'date',
        'fecha_corte_despues' => 'date',
        'created_at' => 'datetime',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }
}
