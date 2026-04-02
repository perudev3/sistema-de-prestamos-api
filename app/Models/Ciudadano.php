<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudadano extends Model
{
    use HasFactory;

    protected $table = 'ciudadanos';

    protected $fillable = [
        'nombre',
        'apellidos',
        'cedula',
        'telefono',
        'direccion',
        'email'
    ];

    public function consultas()
{
    return $this->hasMany(Consulta::class, 'ciudadano_id');
}

public function denuncias()
{
    return $this->hasMany(Denuncia::class, 'ciudadano_id');
}

}
