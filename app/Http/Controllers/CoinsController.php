<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoinCompra;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CoinsController extends Controller
{
    /* =========================================
       COMPRAR COINS (usuario)
    ==========================================*/
    public function comprar(Request $request)
    {
        $request->validate([
            'coins' => 'required|integer|min:1',
            'total' => 'required|numeric|min:1',
            'comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $user = Auth::user();

        $ruta = $request->file('comprobante')
                        ->store('comprobantes_coins', 'public');

        CoinCompra::create([
            'user_id' => $user->id,
            'coins' => $request->coins,
            'total' => $request->total,
            'comprobante' => $ruta,
            'estado' => 'pendiente'
        ]);

        return response()->json([
            'message' => 'Compra registrada correctamente'
        ]);
    }

    /* =========================================
       LISTAR SOLICITUDES (ADMIN)
    ==========================================*/
    public function solicitudesCoins()
    {
        $compras = CoinCompra::with('user')
            ->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'fecha' => $c->created_at->format('d/m/Y H:i'),
                    'prestamista' => $c->user->name . ' ' . $c->user->last_name,
                    'monto' => $c->total,
                    'coins' => $c->coins,
                    'cuenta' => $c->user->phone ?? '-',
                    'metodo' => 'Yape', // puedes guardarlo luego en BD
                    'estado' => $c->estado,
                    'comprobante' => $c->comprobante,
                ];
            });

        return response()->json($compras);
    }

    /* =========================================
       APROBAR COINS (ADMIN)
    ==========================================*/
    public function aprobarCoins(Request $request)
    {
        $request->validate([
            'compra_id' => 'required|exists:coin_compras,id'
        ]);

        $compra = CoinCompra::findOrFail($request->compra_id);

        // Evitar doble aprobación
        if ($compra->estado === 'aprobado') {
            return response()->json([
                'message' => 'Esta compra ya fue aprobada'
            ], 400);
        }

        $user = User::findOrFail($compra->user_id);

        // 🔹 Sumar coins
        $user->coins += $compra->coins;
        $user->save();

        // 🔹 Cambiar estado
        $compra->estado = 'aprobado';
        $compra->save();

        return response()->json([
            'message' => 'Coins aprobados y acreditados al usuario'
        ]);
    }

    public function rechazarCoins(Request $request)
    {
        $request->validate([
            'compra_id' => 'required|exists:coin_compras,id'
        ]);

        $compra = CoinCompra::findOrFail($request->compra_id);
        $compra->estado = 'rechazado';
        $compra->save();

        return response()->json([
            'message' => 'Solicitud rechazada'
        ]);
    }

}
