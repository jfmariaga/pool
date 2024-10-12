<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adjunto extends Model
{

    protected $table   = 'adjuntos' ;
    protected $guarded = [];
    public $timestamps = false;

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class);
    }
}
