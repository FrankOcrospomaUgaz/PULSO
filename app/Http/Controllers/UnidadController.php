<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use App\Http\Requests\UnidadRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;

/**
 * Class UnidadController
 * @package App\Http\Controllers
 */
class UnidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10); // valor por defecto 10

        $query = Unidad::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('abreviatura', 'ILIKE', "%{$search}%")
                    ->orWhere('tipo', 'ILIKE', "%{$search}%")
                    ->orWhere('sunat', 'ILIKE', "%{$search}%");
            });
        }

        $unidades = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 52)
            ->where('estado', true)
            ->get();

        return view('unidad.index', compact('unidades', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $unidades->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $unidad = new Unidad();
        return view('unidad.create', compact('unidad'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnidadRequest $request)
    {
        try {
            $unidad = Unidad::create($request->validated());

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Unidad creada exitosamente.',
                'unidad' => $unidad,
                'redirect_url' => route('unidad.index', ['page' => $pagina_actual])
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
        $unidad = Unidad::find($id);
        return view('unidad.edit', compact('unidad'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnidadRequest $request, Unidad $unidad)
    {
        try {
            $unidad->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Unidad actualizada exitosamente.',
                'unidad' => $unidad,
                'redirect_url' => route('unidad.index')
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
            Unidad::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unidad eliminada correctamente.',
                'redirect_url' => route('unidad.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
