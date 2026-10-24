<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetCompraSocio extends Model
{
    protected $table   = 'det_compra_socios';
    protected $guarded = [];
    public $timestamps = false;

    public function detCompra()
    {
        return $this->belongsTo(DetCompra::class, 'det_compra_id');
    }

    public function socio()
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }
}
