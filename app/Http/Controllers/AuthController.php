<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ciudadano;
use App\Models\Denuncia;
use App\Models\CoinCompra;
use App\Models\Consulta;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends Controller
{

    public function settingsPublicos()
    {
        $settings = Setting::first();

        return response()->json([
            'whatsapp' => $settings->whatsapp_admin ?? null
        ]);
    }

    // Login para todos los usuarios
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->role === 'admin') {
            $settings = Setting::first(); // uno solo

            // Se lo inyectamos al user
            $user->settings = $settings;
        }

        

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        $token = $user->createToken('API Token')->accessToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }

    // Crear prestamista (solo admin)
    public function createPrestamista(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'cedula' => 'required|string|unique:users',
            'telefono' => 'required|string|max:20',
            'direccion' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'coins' => 'required|integer|min:0',
        ]);

        $prestamista = User::create([
            'name' => $request->nombre,
            'last_name' => $request->apellidos,
            'cedula' => $request->cedula,
            'phone' => $request->telefono,
            'address' => $request->direccion,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'prestamista',
            'coins' => $request->coins,
        ]);

        return response()->json(['prestamista' => $prestamista], 201);
    }

    public function listaPrestamista()
    {
        $prestamistas = User::where('role', 'prestamista')
            ->with([
                'pagos:id,user_id,monto,created_at',
                'consultas:id,user_id,descripcion,created_at'
            ])
            ->get();

        $prestamistas = $prestamistas->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'last_name' => $p->last_name,
                'email' => $p->email,
                'cedula' => $p->cedula,
                'telefono' => $p->phone,
                'address' => $p->address,
                'coins' => $p->coins,
                'consultas' => $p->consultas->count(),

                // 🔥 NUEVOS CAMPOS
                'created_at' => $p->created_at ? $p->created_at->format('Y-m-d H:i') : null,

                'pagos' => $p->pagos->map(function ($pago) {
                    return [
                        'id' => $pago->id,
                        'monto' => $pago->monto,
                        'fecha' => $pago->created_at ? $pago->created_at->format('Y-m-d') : null,
                    ];
                }),

                'consultas_list' => $p->consultas->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'descripcion' => $c->descripcion,
                        'fecha' => $c->created_at ? $c->created_at->format('Y-m-d') : null,
                    ];
                }),
            ];
        });

        return response()->json($prestamistas);
    }


    // Obtener usuario autenticado
    public function user(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        /* ===== MÉTRICAS ===== */

        $prestamistas = User::where('role', 'prestamista')->count();
        $ciudadanos = Ciudadano::count();
        $totalReportes = Denuncia::count();
        $pendientes = Denuncia::where('estado', 'pendiente')->count();

        $ingresosMes = CoinCompra::where('estado', 'aprobado')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total'); 

        $consultasMes = Consulta::whereMonth('created_at', Carbon::now()->month)
            ->count();

        $reportesPendientes = Denuncia::with('ciudadano')
            ->where('estado', 'pendiente')
            ->latest()
            ->take(5)
            ->get();

        $pagosRecientes = CoinCompra::with('user')
            ->where('estado', 'aprobado')
            ->latest()
            ->take(5)
            ->get();

        // 👇 Combinar datos del usuario con datos del dashboard
        return response()->json(array_merge($user->toArray(), [
            'prestamistas' => $prestamistas,
            'ciudadanos' => $ciudadanos,
            'total_reportes' => $totalReportes,
            'pendientes' => $pendientes,
            'ingresos_mes' => $ingresosMes,
            'consultas_mes' => $consultasMes,
            'reportes_pendientes' => $reportesPendientes,
            'pagos_recientes' => $pagosRecientes,
        ]));
    }

    public function showPrestamista($id)
    {
        $prestamista = User::where('role', 'prestamista')
            ->findOrFail($id);

        return response()->json($prestamista);
    }

    public function updatePrestamista(Request $request, $id)
{
    if (!$request->user()->isAdmin()) {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    $prestamista = User::where('role', 'prestamista')
        ->findOrFail($id);

    $request->validate([
        'nombre' => 'required|string|max:255',
        'apellidos' => 'required|string|max:255',
        'cedula' => "required|string|unique:users,cedula,$id",
        'telefono' => 'required|string|max:20',
        'direccion' => 'required|string|max:255',
        'email' => "required|email|unique:users,email,$id",
        'coins' => 'required|integer|min:0',
    ]);

    $prestamista->update([
        'name' => $request->nombre,
        'last_name' => $request->apellidos,
        'cedula' => $request->cedula,
        'phone' => $request->telefono,
        'address' => $request->direccion,
        'email' => $request->email,
        'coins' => $request->coins,
    ]);

    return response()->json([
        'message' => 'Prestamista actualizado',
        'prestamista' => $prestamista
    ]);
}


public function deletePrestamista(Request $request, $id)
{
    if (!$request->user()->isAdmin()) {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    $prestamista = User::where('role', 'prestamista')
        ->findOrFail($id);

    $prestamista->delete();

    return response()->json([
        'message' => 'Prestamista eliminado'
    ]);
}



public function changePasswordPrestamista(Request $request, $id)
{
    if (!$request->user()->isAdmin()) {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    $request->validate([
        'password' => 'required|string|min:6'
    ]);

    $prestamista = User::where('role', 'prestamista')
        ->findOrFail($id);

    $prestamista->update([
        'password' => Hash::make($request->password)
    ]);

    return response()->json([
        'message' => 'Contraseña actualizada'
    ]);
}


        
}
