<?php

namespace App\Http\Controllers;

use App\Models\Piso;
use App\Http\Requests\PisoRequest;
use App\Models\Operacione;
use App\Models\Persona;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/**
 * Class PisoController
 * @package App\Http\Controllers
 */
class PisoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $perPage = $request->input('perPage', 10);

        $query = Piso::where('sucursal_id', $persona->sucursal_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%");
            });
        }

        $pisos = $query->orderBy('nombre', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        $operaciones = Operacione::where('tabla_id', 19)
            ->where('estado', true)
            ->get();

        return view('piso.index', compact('pisos', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $pisos->perPage());
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $piso = new Piso();
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        return view('piso.create', compact('piso', 'sucursal_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PisoRequest $request)
    {
        try {
            // Obtener el estado del switch
            $switchState = $request->input('switch_state', 'unitario');
    
            if ($switchState === 'unitario') {
                // Modo Unitario: Crear un solo piso de forma manual
                $piso = Piso::create([
                    'nombre' => $request->input('nombre'), // Obteniendo el nombre directamente
                    'estado' => 1, // Estado activo por defecto
                    'sucursal_id' => $request->input('sucursal_id') // Obteniendo la sucursal directamente
                ]);
                    
            } else {
                // Modo Por Bloques: Crear múltiples pisos
                $inicio = $request->input('inicio_piso');
                $fin = $request->input('fin_piso');
    
                if ($inicio > $fin) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El piso inicial no puede ser mayor que el piso final.'
                    ], 400);
                }
    
                for ($i = $inicio; $i <= $fin; $i++) {
                    Piso::create([
                        'nombre' => 'Piso ' . $i,
                        'estado' => true, // Asume que "1" significa activo
                        'sucursal_id' => $request->input('sucursal_id')
                    ]);
                }
            }
    
            $pagina_actual = $request->input('pagina_actual', 1);
    
            return response()->json([
                'success' => true,
                'message' => 'Piso(s) creado(s) exitosamente.',
                'redirect_url' => route('pisos.index', ['page' => $pagina_actual])
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
        $piso = Piso::find($id);
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        return view('piso.edit', compact('piso', 'sucursal_id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PisoRequest $request, Piso $piso)
    {
        try {
            $piso->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Piso actualizado exitosamente.',
                'piso' => $piso,
                'redirect_url' => route('pisos.index')
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
        
            $piso = Piso::findOrFail($id);

            // Eliminar lógicamente el piso y sus habitaciones relacionadas
            $piso->habitaciones()->delete();
            $piso->delete();
            return response()->json([
                'success' => true,
                'message' => 'Piso y habitaciones relacionadas eliminado correctamente.',
                'redirect_url' => route('pisos.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    
}
