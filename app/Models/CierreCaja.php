<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreCaja extends Model
{
    use HasFactory;
    protected $table   = 'cierre_caja' ;
    protected $guarded = [];
    public $timestamps = false;

    public function detalles()
    {
        return $this->hasMany(DetCierreCaja::class, 'cierre_id');
    }
    
    public function usuario(){
        return $this->belongsTo(User::class, 'user_id');
     }

}
