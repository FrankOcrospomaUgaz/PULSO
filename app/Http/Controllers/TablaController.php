<?php

namespace App\Http\Controllers;

use App\Models\Tabla;
use App\Http\Requests\TablaRequest;
use App\Models\Operacione;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Class TablaController
 * @package App\Http\Controllers
 */
class TablaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Tabla::query();
        $perPage = $request->input('perPage', 10); 
        // Obtener todas las columnas de la tabla 'tablas'
        $columns = Schema::getColumnListing('tablas');
    
        if ($search) {
            $query->where(function($q) use ($columns, $search) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }
    
        $tablas = $query->paginate($perPage);
        $operaciones = Operacione::where('tabla_id', 3)->where('estado', true)->get();
    
        return view('tabla.index', compact('tablas', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $tablas->perPage());
    }
    
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tabla = new Tabla();
        return view('tabla.create', compact('tabla'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TablaRequest $request)
    {
        try {
            $tabla = Tabla::create($request->validated());

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Tabla creada correctamente.',
                'tabla' => $tabla,
                'redirect_url' => route('tablas.index', ['page' => $pagina_actual])
            ]);
        } catch (Exception $e) {
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
        $tabla = Tabla::find($id);

        return view('tabla.edit', compact('tabla'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TablaRequest $request, Tabla $tabla)
    {
        try {
            $tabla->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tabla actualizada correctamente.',
                'redirect_url' => route('tablas.index')
            ]);
        } catch (Exception $e) {
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
            $tabla = Tabla::find($id);
            if ($tabla) {
                $tabla->delete(); // O usar softDeletes si es eliminación lógica

                return response()->json([
                    'success' => true,
                    'message' => 'Tabla eliminada correctamente.',
                    'redirect_url' => route('tablas.index')
                ]);
            } else {
                throw new Exception("Tabla no encontrada.");
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
