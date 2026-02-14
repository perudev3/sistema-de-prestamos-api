<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Denuncia;
use App\Models\Ciudadano;
use Illuminate\Support\Facades\Storage;

class DenunciaController extends Controller
{
    public function store(Request $request)
    {
        // ==========================
        // VALIDACIÓN
        // ==========================
        $request->validate([
            'cedula'        => 'required|string|max:50',
            'nombres'       => 'required|string|max:150',
            'apellidos'     => 'required|string|max:150',
            'descripcion'   => 'required|string',
            'imagenes.*'    => 'image|max:5120', // 5MB
            'celular'       => 'nullable|string|max:20'
        ]);

        // ==========================
        // 1️⃣ BUSCAR O CREAR CIUDADANO
        // ==========================
        $ciudadano = Ciudadano::where('cedula', $request->cedula)->first();

        if (!$ciudadano) {

            $ciudadano = Ciudadano::create([
                'nombre'    => $request->nombres,
                'apellidos' => $request->apellidos,
                'cedula'    => $request->cedula,
                'telefono'  => $request->celular ?? 'No registrado',
                'direccion' => 'No especificada',
                'email'     => null,
            ]);
        }

        // ==========================
        // 2️⃣ SUBIR IMÁGENES
        // ==========================
        $imagenesGuardadas = [];

        if ($request->hasFile('imagenes')) {

            foreach ($request->file('imagenes') as $imagen) {

                $path = $imagen->store('denuncias', 'public');

                $imagenesGuardadas[] = $path;
            }
        }

        // ==========================
        // 3️⃣ CREAR DENUNCIA
        // ==========================
        $denuncia = Denuncia::create([
            'prestamista_id'    => $request->user()->id ?? null,
            'ciudadano_id'      => $ciudadano->id,
            'cedula'            => $request->cedula,
            'nombres'           => $request->nombres,
            'apellidos'         => $request->apellidos,
            'descripcion_deuda' => $request->descripcion,
            'imagenes'          => json_encode($imagenesGuardadas),
            'nombre_reportante' => $request->nombre_reportante,
            'celular'           => $request->celular,
            'estado'            => 'pendiente',
        ]);

        // ==========================
        // RESPONSE
        // ==========================
        return response()->json([
            'success' => true,
            'message' => 'Denuncia registrada correctamente',
            'data'    => $denuncia,
            'ciudadano' => $ciudadano
        ]);
    }

    public function misDenuncias()
    {
        // 🔹 Si usas auth de prestamista
        $prestamistaId = Auth::id();

        // 🔹 Si no usas auth y mandas id por token/custom
        // $prestamistaId = $request->prestamista_id;

        $denuncias = Denuncia::where('prestamista_id', $prestamistaId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'fecha' => $d->created_at->format('d/m/Y'),
                    'cedula' => $d->cedula,
                    'nombre' => $d->nombres . ' ' . $d->apellidos,
                    'descripcion' => $d->descripcion_deuda,
                    'estado' => $d->estado ?? 'Pendiente',
                    'imagenes' => $d->imagenes,
                    'reportante' => $d->nombre_reportante,
                    'celular' => $d->celular,
                ];
            });

        return response()->json([
            'ok' => true,
            'data' => $denuncias
        ]);
    }

}
