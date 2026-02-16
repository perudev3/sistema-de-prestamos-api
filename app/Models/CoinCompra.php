<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinCompra extends Model
{
    use HasFactory;

    protected $table = 'coin_compras';

    protected $fillable = [
        'user_id',
        'coins',
        'total',
        'comprobante',
        'estado'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
