<?php

namespace App\Http\Controllers;

use App\Models\Igv;
use App\Http\Requests\IgvRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;

class IgvController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search   = $request->input('search');
        $situacion = $request->input('situacion');
        $perPage  = $request->input('perPage', 10);

        $query = Igv::query();

        // 🔹 Búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(codigo) LIKE ?", ['%' . strtolower($search) . '%'])
                    ->orWhereRaw("LOWER(descripcion) LIKE ?", ['%' . strtolower($search) . '%']);
            });
        }

        // 🔹 Filtro por situación
        if ($situacion !== null && $situacion !== '') {
            $query->where('situacion', $situacion);
        }

        // 🔹 Paginación y orden
        $igvs = $query->orderBy('orden', 'asc')->paginate($perPage);

        // 🔹 Operaciones relacionadas
        $operaciones = Operacione::where('tabla_id', 44)
            ->where('estado', true)
            ->get();

        return view('igv.index', compact('igvs', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $igvs->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $igv = new Igv();
        return view('igv.create', compact('igv'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IgvRequest $request)
    {
        try {
            Igv::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'IGV creado exitosamente.',
                'redirect_url' => route('igv.index')
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
        $igv = Igv::find($id);
        return view('igv.edit', compact('igv'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IgvRequest $request, Igv $igv)
    {
        try {
            $igv->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'IGV actualizado exitosamente.',
                'redirect_url' => route('igv.index')
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
            Igv::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'IGV eliminado correctamente.',
                'redirect_url' => route('igv.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar un IGV especificado.
     */
    public function activar($id)
    {
        try {
            $igv = Igv::find($id);
            $igv->update(['situacion' => true]);

            return response()->json([
                'success' => true,
                'message' => 'IGV activada exitosamente.',
                'encuesta' => $igv,
                'redirect_url' => route('igv.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar un IGV especificado.
     */
    public function desactivar($id)
    {
        try {
            $igv = Igv::find($id);
            $igv->update(['situacion' => false]);

            return response()->json([
                'success' => true,
                'message' => 'IGV desactivada exitosamente.',
                'encuesta' => $igv,
                'redirect_url' => route('igv.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
