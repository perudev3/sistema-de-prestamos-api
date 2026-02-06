<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ciudadano;

class CiudadanoController extends Controller
{
    public function store(Request $request)
    {
        //dd($request);
        if (!$request->user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'nombre'    => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'cedula'    => 'required|string|max:50|unique:ciudadanos,cedula',
            'telefono'  => 'required|string|max:20',
            'direccion' => 'required|string|max:255',
            'email'     => 'nullable|email|max:150',
        ]);

        $ciudadano = Ciudadano::create($request->all());

        return response()->json([
            'message' => 'Ciudadano registrado correctamente',
            'data'    => $ciudadano
        ], 201);
    }

    public function listaCiudadanos(){
        $ciudadanos = Ciudadano::all();
        return response()->json($ciudadanos);
    }

    public function consultar(Request $request)
    {
        $request->validate([
            'dni' => 'required'
        ]);

        // Usuario autenticado (prestamista)
        $prestamista = $request->user();

        // 1️⃣ Verificar coins
        if ($prestamista->coins <= 0) {
            return response()->json([
                'message' => 'No tienes coins disponibles'
            ], 402);
        }

        // 2️⃣ Buscar ciudadano
        $ciudadano = Ciudadano::where('cedula', $request->dni)->first();

        if (!$ciudadano) {
            return response()->json([
                'message' => 'Ciudadano no encontrado'
            ], 404);
        }

        \DB::beginTransaction();

        try {

            // 3️⃣ Descontar coin
            $prestamista->decrement('coins', 1);

            // 4️⃣ (Opcional) Guardar log
            \DB::table('consultas')->insert([
                'prestamista_id' => $prestamista->id,
                'ciudadano_id'   => $ciudadano->id,
                'coins_usados'   => 1,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            \DB::commit();

            // 5️⃣ Respuesta
            return response()->json([
                'coins_restantes' => $prestamista->fresh()->coins,
                'data' => $ciudadano
            ]);

        } catch (\Exception $e) {

            \DB::rollBack();

            return response()->json([
                'message' => 'Error en la consulta',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
