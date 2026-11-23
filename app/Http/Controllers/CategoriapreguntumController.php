<?php

namespace App\Http\Controllers;

use App\Models\Categoriapreguntum;
use App\Http\Requests\CategoriapreguntumRequest;
use App\Models\Operacione;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CategoriapreguntumController
 * @package App\Http\Controllers
 */
class CategoriapreguntumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        // Query base
        $query = Categoriapreguntum::where('sucursal_id', $persona->sucursal_id);

        // Si hay búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('situacion', 'ILIKE', "%{$search}%");
            });
        }
        $perPage = $request->input('perPage', 10);
        // Paginación ordenada por ID
        $categoriapreguntas = $query->orderBy('id', 'asc')->paginate($perPage);

        // Operaciones relacionadas
        $operaciones = Operacione::where('tabla_id', 74)
            ->where('estado', true)
            ->get();

        return view('categoriapreguntum.index', compact('categoriapreguntas', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $categoriapreguntas->perPage());
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        $categoriapreguntum = new Categoriapreguntum();
        return view('categoriapreguntum.create', compact('categoriapreguntum', 'sucursal_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoriapreguntumRequest $request)
    {

        try {
            // Insertar en la tabla 'categoria'
            Categoriapreguntum::create($request->validated());

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Categoría creada exitosamente.',
                'redirect_url' => route('categoriapregunta.index', ['page' => $pagina_actual])
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $categoriapreguntum = Categoriapreguntum::find($id);

        return view('categoriapreguntum.show', compact('categoriapreguntum'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        $categoriapreguntum = Categoriapreguntum::find($id);

        return view('categoriapreguntum.edit', compact('categoriapreguntum', 'sucursal_id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoriapreguntumRequest $request, Categoriapreguntum $categoriapreguntum)
    {
        try {
            $categoriapreguntum->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente.',
                'categoria' => $categoriapreguntum,
                'redirect_url' => route('categoriapregunta.index')
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
            // Buscar la respuesta antes de eliminarla
            $cat = Categoriapreguntum::findOrFail($id);

            // Eliminar la respuesta
            $cat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoria de pregunta eliminada correctamente.',
                'redirect_url' => route('categoriapregunta.index') // Usar la variable almacenada
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    public function activar($id)
    {
        try {
            $cat = Categoriapreguntum::find($id);
            $cat->update(['situacion' => 'E']);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Categoria de pregunta activada exitosamente.',
                'categoria' => $cat,
                'redirect_url' => route('categoriapregunta.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }



    public function desactivar($id)
    {
        try {
            $cat = Categoriapreguntum::find($id);
            $cat->update(['situacion' => 'D']);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Categoria de pregunta desactivada exitosamente.',
                'categoria' => $cat,
                'redirect_url' => route('categoriapregunta.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
