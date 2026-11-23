<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSucursalActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $persona = $user ? $user->persona : null;
        $sucursal = $persona ? $persona->sucursal : null;

        if ($sucursal && $sucursal->getRawOriginal('situacion') !== 'A') {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'La sucursal asignada se encuentra desactivada.',
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'name' => 'La sucursal asignada se encuentra desactivada. Comuniquese con el administrador.',
            ]);
        }

        return $next($request);
    }
}
