<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable,HasRoles;
    protected $guarded=[];
    public $timestamps = false;

    protected $hidden = [
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function ventas(){
        return $this->hasMany(Venta::class);
    }
}
