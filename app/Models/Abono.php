<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abono extends Model
{
    use HasFactory;

    protected $fillable = ['credito_id', 'monto', 'fecha'];

    // Relación con el modelo Credito
    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }
}
