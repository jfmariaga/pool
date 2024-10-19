<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetCierreCaja extends Model
{
    use HasFactory;
    protected $table   = 'det_cierre_caja' ;
    protected $guarded = [];
    public $timestamps = false;

    public function cierre()
    {
        return $this->belongsTo(CierreCaja::class, 'cierre_id');
    }
    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }
}
