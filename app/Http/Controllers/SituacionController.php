<?php

namespace App\Http\Controllers;

use App\Models\Situacion;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SituacionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);
        $query = Situacion::where('estado', true);

        if ($search) {
            $query->where('descripcion', 'ILIKE', "%$search%");
        }
        $persona = Persona::find(Auth::user()->persona_id);
        $situaciones = $query->orderBy('id', 'asc')->where('sucursal_id', $persona->sucursal_id)->paginate($perPage);
        $operaciones = Operacione::where('tabla_id', 79)->where('estado', true)->get();

        return view('situacion.index', compact('situaciones', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $situaciones->perPage());
    }

    public function create()
    {
        $situacion = new Situacion();
        return view('situacion.create', compact('situacion'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'descripcion' => 'required|string|max:255|unique:situacion',
                'color' => 'required|string|max:7',
            ]);

            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::find($persona->sucursal_id);

            Situacion::create([
                'descripcion' => $request->descripcion,
                'color' => $request->color,
                'sucursal_id' => $sucursal->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Situación creada exitosamente.',
                'redirect_url' => route('situacion.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $situacion = Situacion::findOrFail($id);
        return view('situacion.show', compact('situacion'));
    }

    public function edit($id)
    {
        $situacion = Situacion::findOrFail($id);
        return view('situacion.edit', compact('situacion'));
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'color' => 'required|string|max:7',
                'estado' => 'boolean',
            ]);

            $situacion = Situacion::findOrFail($id);

            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::find($persona->sucursal_id);

            $situacion->update([
                'descripcion' => $request->descripcion,
                'color' => $request->color,
                'estado' => $request->estado ?? true,
                'sucursal_id' => $sucursal->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Situación actualizada correctamente.',
                'redirect_url' => route('situacion.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
        
            $situacion = Situacion::findOrFail($id);
            $situacion->delete();
            return response()->json([
                'success' => true,
                'message' => 'Situacion eliminada correctamente.',
                'redirect_url' => route('situacion.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        $situacion = Situacion::onlyTrashed()->findOrFail($id);
        $situacion->restore();

        return redirect()->route('situacion.index')
            ->with('success', 'Situación restaurada correctamente.');
    }
}
