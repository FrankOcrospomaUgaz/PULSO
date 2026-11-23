<?php

namespace App\Http\Middleware;

use App\Models\Encuestum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SurveyApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtener el UUID desde los encabezados de la solicitud
        $uuid = $request->header('Authorization');  // Asegúrate de que el encabezado esté pasando como 'UUID'

        // Verificar si el UUID es válido
        $encuesta = Encuestum::where('uuid', $uuid)->first();

        if (!$encuesta) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
