<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;

    protected $table   = 'creditos' ;
    protected $guarded = [];
    public $timestamps = false;
    // Relación con el modelo User (Deudor)
    public function deudor()
    {
        return $this->belongsTo(User::class, 'deudor_id');
    }

    // Relación con el modelo User (Responsable)
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // Relación con los abonos
    public function abonos()
    {
        return $this->hasMany(Abono::class);
    }

    public function ventas()
    {
        return $this->belongsTo(Venta::class);
    }

    public function adjuntos()
    {
        return $this->hasMany(Adjunto::class);
    }
}
