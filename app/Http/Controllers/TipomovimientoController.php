<?php

namespace App\Http\Controllers;

use App\Models\Tipomovimiento;
use App\Http\Requests\TipomovimientoRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;

/**
 * Class TipomovimientoController
 * @package App\Http\Controllers
 */
class TipomovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10); // valor por defecto 10

        $query = Tipomovimiento::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('id', 'ILIKE', "%{$search}%");
            });
        }

        $tipomovimientos = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 27)
            ->where('estado', true)
            ->get();

        return view('tipomovimiento.index', compact('tipomovimientos', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $tipomovimientos->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipomovimiento = new Tipomovimiento();
        return view('tipomovimiento.create', compact('tipomovimiento'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipomovimientoRequest $request)
    {
        try {
            $tipomovimiento = Tipomovimiento::create($request->validated());

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de movimiento creado exitosamente.',
                'tipomovimiento' => $tipomovimiento,
                'redirect_url' => route('tipomovimiento.index', ['page' => $pagina_actual])
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
        $tipomovimiento = Tipomovimiento::find($id);

        return view('tipomovimiento.show', compact('tipomovimiento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tipomovimiento = Tipomovimiento::find($id);

        return view('tipomovimiento.edit', compact('tipomovimiento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TipomovimientoRequest $request, Tipomovimiento $tipomovimiento)
    {
        try {
            $tipomovimiento->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tipo de movimiento actualizado exitosamente.',
                'tipomovimiento' => $tipomovimiento,
                'redirect_url' => route('tipomovimiento.index')
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
            Tipomovimiento::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de movimiento eliminado correctamente.',
                'redirect_url' => route('tipomovimiento.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
