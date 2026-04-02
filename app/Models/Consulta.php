<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Consulta extends Model
{
    use HasFactory;

    protected $table = 'consultas';

    protected $fillable = [
        'prestamista_id',
        'ciudadano_id',
        'coins_usados'
    ];

    // ==========================
    // RELACIONES
    // ==========================

    public function ciudadano()
    {
        return $this->belongsTo(Ciudadano::class, 'ciudadano_id');
    }

    public function prestamista()
    {
        return $this->belongsTo(
            User::class,
            'prestamista_id'
        );
    }

}
