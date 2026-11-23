<?php

namespace App\Http\Controllers;

use App\Models\Tipoopcione;
use App\Http\Requests\TipoopcioneRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;

/**
 * Class TipoopcioneController
 * @package App\Http\Controllers
 */
class TipoopcioneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10); // por defecto 10

        $query = Tipoopcione::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('abreviatura', 'ILIKE', "%{$search}%")
                    ->orWhere('orden', 'ILIKE', "%{$search}%");
            });
        }

        $tipoopciones = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 13)
            ->where('estado', true)
            ->get();

        return view('tipoopcione.index', compact('tipoopciones', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $tipoopciones->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipoopcione = new Tipoopcione();
        return view('tipoopcione.create', compact('tipoopcione'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipoopcioneRequest $request)
    {
        try {
            $tipoopcione = Tipoopcione::create($request->validated());
            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de Opción creada exitosamente.',
                'tipoopcione' => $tipoopcione,
                'redirect_url' => route('tipoopciones.index', ['page' => $pagina_actual])
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
        $tipoopcione = Tipoopcione::find($id);
        return view('tipoopcione.edit', compact('tipoopcione'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TipoopcioneRequest $request, Tipoopcione $tipoopcione)
    {
        try {
            $tipoopcione->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tipo de Opción actualizada exitosamente.',
                'tipoopcione' => $tipoopcione,
                'redirect_url' => route('tipoopciones.index')
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
            $tipoopcione = Tipoopcione::find($id);

            if ($tipoopcione) {
                $tipoopcione->estado = false;
                $tipoopcione->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Tipo de Opción marcada como eliminada correctamente.',
                    'redirect_url' => route('tipoopciones.index')
                ]);
            }

            return response()->json([
                'success' => false,
                'errors' => 'Tipo de Opción no encontrada.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
