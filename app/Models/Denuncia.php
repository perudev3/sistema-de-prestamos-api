<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denuncia extends Model
{
    use HasFactory;

    protected $table = 'denuncias';

    protected $fillable = [
        'prestamista_id',
        'cedula',
        'nombres',
        'apellidos',
        'descripcion_deuda',
        'imagenes',
        'nombre_reportante',
        'celular',
        'estado'
    ];

    protected $casts = [
        'imagenes' => 'array'
    ];

    // Relación
    public function prestamista()
    {
        return $this->belongsTo(Prestamista::class);
    }
}
