<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Socio extends Model
{
    protected $table   = 'socios';
    protected $guarded = [];
    public $timestamps = false;

    public function detCompras()
    {
        return $this->hasMany(DetCompra::class, 'socio_id');
    }
}
