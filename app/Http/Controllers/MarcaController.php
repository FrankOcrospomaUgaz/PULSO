<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Http\Requests\MarcaRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;

/**
 * Class MarcaController
 * @package App\Http\Controllers
 */
class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search  = $request->input('search');

        // Query base
        $query = Marca::query();

        // Filtro de búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(descripcion) LIKE ?", ['%' . strtolower($search) . '%'])
                    ->orWhereRaw("LOWER(abreviatura) LIKE ?", ['%' . strtolower($search) . '%']);
            });
        }

        // Ejecutar query con paginación
        $marcas = $query->orderBy('id', 'desc')->paginate($perPage);

        // Operaciones relacionadas a esta tabla
        $operaciones = Operacione::where('tabla_id', 54)
            ->where('estado', true)
            ->get();

        return view('marca.index', compact('marcas', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $marcas->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $marca = new Marca();
        return view('marca.create', compact('marca'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MarcaRequest $request)
    {
        try {
            $marca = Marca::create($request->validated());

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Marca creada exitosamente.',
                'marca' => $marca,
                'redirect_url' => route('marca.index', ['page' => $pagina_actual])
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
        $marca = Marca::find($id);
        return view('marca.edit', compact('marca'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MarcaRequest $request, Marca $marca)
    {
        try {
            $marca->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Marca actualizada exitosamente.',
                'marca' => $marca,
                'redirect_url' => route('marca.index')
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
            Marca::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Marca eliminada correctamente.',
                'redirect_url' => route('marca.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
