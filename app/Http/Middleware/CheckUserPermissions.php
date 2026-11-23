<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Permisousuario;
use App\Models\Persona;

class CheckUserPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $menuopcion
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $menuopcion)
    {
        $user = Auth::user();

        if (!$user) {
            // Si el usuario no está autenticado
            abort(403, 'Acceso denegado');
        }

        // Obtener la persona y la sucursal del usuario
        $persona = Persona::find($user->persona_id);
        $sucursalId = $persona->sucursal_id;

        // Consultar los permisos del usuario para la opción del menú solicitada
        $hasPermission = Permisousuario::where('sucursal_id', $sucursalId)
            ->where('menuopcion_id', $menuopcion)
            ->where('situacion', true)
            ->exists();

        if (!$hasPermission) {
            // Si no tiene permisos para acceder a la ruta
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
