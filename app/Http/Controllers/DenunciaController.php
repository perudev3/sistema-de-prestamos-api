<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Denuncia;

class DenunciaController extends Controller
{
    public function store(Request $request)
    {
        $denuncia = Denuncia::create([
            'prestamista_id'   => $request->prestamista_id,
            'cedula'           => $request->cedula,
            'nombres'          => $request->nombres,
            'apellidos'        => $request->apellidos,
            'descripcion_deuda'=> $request->descripcion_deuda,
            'imagenes'         => $request->imagenes,
            'nombre_reportante'=> $request->nombre_reportante,
            'celular'          => $request->celular,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Denuncia registrada correctamente',
            'data' => $denuncia
        ]);
    }
}
