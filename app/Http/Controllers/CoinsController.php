<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoinCompra;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CoinsController extends Controller
{
    public function comprar(Request $request)
    {
        // 🔹 Validación
        $request->validate([
            'coins' => 'required|integer|min:1',
            'total' => 'required|numeric|min:1',
            'comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        // 🔹 Usuario autenticado
        $user = Auth::user();

        // (Opcional) también podrías buscarlo así:
        // $user = User::findOrFail(Auth::id());

        // 🔹 Subir comprobante
        $ruta = $request->file('comprobante')
                        ->store('comprobantes_coins', 'public');

        // 🔹 Registrar compra
        $compra = CoinCompra::create([
            'user_id' => $user->id,
            'coins' => $request->coins,
            'total' => $request->total,
            'comprobante' => $ruta,
            'estado' => 'aprobado' // ya que se acredita automático
        ]);

        // ==========================================
        // 🔥 SUMAR COINS AL USUARIO
        // ==========================================
        $user->coins += $request->coins;
        $user->save();

        // También podrías hacerlo así:
        // $user->increment('coins', $request->coins);

        return response()->json([
            'message' => 'Compra registrada y coins acreditados',
            'coins_actuales' => $user->coins
        ]);
    }
}
