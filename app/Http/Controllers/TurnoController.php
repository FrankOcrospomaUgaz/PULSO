<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Http\Requests\TurnoRequest;
use App\Models\Operacione;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TurnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        $search = $request->input('search');
        $perPage = $request->input('perPage', 10); // valor por defecto

        $query = Turno::where('sucursal_id', $persona->sucursal_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('abreviatura', 'ILIKE', "%{$search}%")
                    ->orWhere('horainicio', 'ILIKE', "%{$search}%")
                    ->orWhere('horafin', 'ILIKE', "%{$search}%");
            });
        }

        $turnos = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 63) // tabla turno
            ->where('estado', true)
            ->get();

        return view('turno.index', compact('turnos', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $turnos->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $turno = new Turno();
        // Obtener el usuario autenticado y su empresa
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        return view('turno.create', compact('turno', 'sucursal_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TurnoRequest $request)
    {
        try {
            Turno::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Turno creado exitosamente.',
                'redirect_url' => route('turno.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $turno = Turno::find($id);
        // Obtener el usuario autenticado y su empresa
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        return view('turno.edit', compact('turno', 'sucursal_id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TurnoRequest $request, Turno $turno)
    {
        try {
            $turno->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Turno actualizado exitosamente.',
                'redirect_url' => route('turno.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            Turno::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Turno eliminado correctamente.',
                'redirect_url' => route('turno.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar un Turno especificado.
     */
    public function activar($id)
    {
        try {
            $turno = Turno::find($id);
            $turno->update(['situacion' => 'A']);

            return response()->json([
                'success' => true,
                'message' => 'Turno activado correctamente.',
                'redirect_url' => route('turno.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar un Turno especificado.
     */
    public function desactivar($id)
    {
        try {
            $turno = Turno::find($id);
            $turno->update(['situacion' => 'I']);

            return response()->json([
                'success' => true,
                'message' => 'Turno desactivado correctamente.',
                'redirect_url' => route('turno.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
