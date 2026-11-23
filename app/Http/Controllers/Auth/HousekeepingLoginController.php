<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Models\Housekeeping;
use App\Models\HousekeepingDetalle;
use App\Models\Persona;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\HabitacionAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HousekeepingLoginController extends Controller
{
    public function showLoginForm($ruc)
    {
        // Buscar la sucursal por su RUC
        $sucursal = Sucursal::where('ruc', $ruc)->first();
    
        // Si no se encuentra la sucursal, mostrar error 404
        if (!$sucursal) {
            abort(404, 'Sucursal no encontrada');
        }

        if ($sucursal->getRawOriginal('situacion') !== 'A') {
            abort(403, 'Sucursal desactivada');
        }
    
        // Obtener empleados con el rol "Limpiador" en la sucursal encontrada
        $empleados = Persona::whereHas('rolpersonas.rol', function ($query) {
            $query->where('descripcion', 'Limpiador');
        })->where('sucursal_id', $sucursal->id)->get();

       // dd($empleados);
    
        return view('auth.login_housekeeping', compact('empleados', 'sucursal'));
    }
    

    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:persona,id',
            'password' => 'required|string',
        ]);
    
        // Buscar al usuario por su ID
        $usuario = Persona::find($request->user_id);
        if (!$usuario) {
            return back()->withErrors(['user_id' => 'Usuario no encontrado']);
        }

        if ($usuario->sucursal && $usuario->sucursal->getRawOriginal('situacion') !== 'A') {
            return back()->withErrors(['user_id' => 'La sucursal asignada se encuentra desactivada.']);
        }

        $user = User::where('persona_id', $usuario->id)->first();
        if (!$user) {
            return back()->withErrors(['user_id' => 'Usuario no encontrado']);
        }
    
        // Intentar autenticación usando el `name` del usuario
        if (Auth::attempt(['name' => $user->name, 'password' => $request->password], $request->remember)) {
            return redirect()->route('housekeeping.dashboard');
        }
    
        return back()->withErrors(['password' => 'Credenciales incorrectas']);
    }
    

    public function logout($ruc)
    {
        Auth::logout();
        return redirect()->route('housekeeping.login.form', $ruc);
    }

    public function index()
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
    
        // Obtener todas las jornadas de housekeeping con sus detalles filtrados por empleado
        $housekeepingJornadas = Housekeeping::with(['housekeepingDetalles' => function ($query) use ($persona) {
            $query->where('empleado_id', $persona->id)->with('habitacion');
        }])   ->orderBy('fecha_programada', 'desc')->where('sucursal_id', $persona->sucursal_id)
        ->get();
    
        return view('housekeeping.dashboard', compact('housekeepingJornadas'));
    }
    

    public function startCleaning($id)
    {
        $detalle = HousekeepingDetalle::findOrFail($id);
    
        // Verificar si es el primer detalle en iniciar limpieza
        $jornadaId = $detalle->jornada_id;
    
        $yaIniciados = HousekeepingDetalle::where('jornada_id', $jornadaId)
            ->whereNotNull('inicio_limpieza')
            ->count();
    
        // Si nadie ha empezado aún, cambiar estado de la jornada
        if ($yaIniciados === 0) {
            $jornada = Housekeeping::find($jornadaId);
            if ($jornada) {
                $jornada->estado = 'En progreso';
                $jornada->save();
            }
        }
    
        // Actualizar el detalle
        $detalle->estado = 'En progreso';
        $detalle->inicio_limpieza = now();
        $detalle->save();
    
        return redirect()->route('housekeeping.dashboard')->with('success', 'Limpieza iniciada.');
    }
    public function finishCleaning(Request $request, $id)
    {
        DB::beginTransaction();
    
        try {
            $detalle = HousekeepingDetalle::findOrFail($id);
    
            $detalle->estado = 'Completado';
            $detalle->fin_limpieza = now();
            $detalle->tiempo = now()->diffInMinutes($detalle->inicio_limpieza);
    
            if ($request->observaciones) {
                $detalle->observaciones = $request->observaciones;
            }
    
            // La ocupación real depende del alojamiento activo, no solo del estado visual.
            $habitacion = Habitacion::find($detalle->habitacion_id);
            if ($habitacion) {
                HabitacionAvailability::markAvailableIfNoActiveStay((int) $habitacion->id);
            }
    
            $detalle->save();
    
            // Verificar si todos los detalles de esta jornada están Completados
            $jornadaId = $detalle->jornada_id;
            $pendientes = HousekeepingDetalle::where('jornada_id', $jornadaId)
                ->where('estado', '!=', 'Completado')
                ->count();
    
            if ($pendientes === 0) {
                $jornada = Housekeeping::find($jornadaId);
                if ($jornada) {
                    $jornada->estado = 'Completado';
    
                    // Calcular el tiempo total: desde el primer inicio hasta el último fin
                    $inicio = HousekeepingDetalle::where('jornada_id', $jornadaId)->min('inicio_limpieza');
                    $fin = HousekeepingDetalle::where('jornada_id', $jornadaId)->max('fin_limpieza');
    
                    if ($inicio && $fin) {
                        $jornada->tiempo_total = \Carbon\Carbon::parse($inicio)->diffInMinutes(\Carbon\Carbon::parse($fin));
                    }
    
                    $jornada->save();
                }
            }
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Jornada(s) finalizada(s) exitosamente.',
                'redirect_url' => route('housekeeping.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    
    


    
}
