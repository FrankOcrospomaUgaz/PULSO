<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Http\Requests\ModuloRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;
use Exception;

class ModuloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search   = $request->input('search');
        $perPage  = $request->input('perPage', 10);

        $query = Modulo::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('abreviatura', 'ILIKE', "%{$search}%")
                    ->orWhere('orden', 'ILIKE', "%{$search}%");
            });
        }

        $modulos = $query->orderBy('orden', 'asc')->paginate($perPage);

        $operaciones = Operacione::where('tabla_id', 8)->where('estado', true)->get();

        return view('modulo.index', compact('modulos', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $modulos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $modulo = new Modulo();
        return view('modulo.create', compact('modulo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ModuloRequest $request)
    {
        try {
            $modulo = Modulo::create($request->validated());

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Módulo creado exitosamente.',
                'modulo' => $modulo,
                'redirect_url' => route('modulos.index', ['page' => $pagina_actual])
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
        $modulo = Modulo::find($id);
        return view('modulo.edit', compact('modulo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ModuloRequest $request, Modulo $modulo)
    {
        try {
            $modulo->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Módulo actualizado exitosamente.',
                'modulo' => $modulo,
                'redirect_url' => route('modulos.index')
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
            Modulo::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Módulo eliminado correctamente.',
                'redirect_url' => route('modulos.index')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
