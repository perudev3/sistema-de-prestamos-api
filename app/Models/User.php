<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'role',
        'cedula',
        'phone',
        'address',
        'coins'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isPrestamista()
    {
        return $this->role === 'prestamista';
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'user_id');
    }

    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'user_id');
    }
}
