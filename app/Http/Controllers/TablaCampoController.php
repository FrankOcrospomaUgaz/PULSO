<?php

namespace App\Http\Controllers;

use App\Models\Campo;
use App\Http\Requests\CampoRequest;
use App\Models\Operacione;
use App\Models\Tabla;
use Illuminate\Http\Request;

/**
 * Class CampoController
 * @package App\Http\Controllers
 */
class TablaCampoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $id)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10); // valor por defecto 10

        $query = Campo::where('tabla_id', $id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('comentario', 'ILIKE', "%{$search}%")
                    ->orWhere('diccionario', 'ILIKE', "%{$search}%")
                    ->orWhere('validacion', 'ILIKE', "%{$search}%")
                    ->orWhere('msgvalidacion', 'ILIKE', "%{$search}%");
            });
        }

        $campos = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 6)
            ->where('estado', true)
            ->get();

        return view('campo.index', compact('campos', 'id', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $campos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        $campo = new Campo();
        $tablas = Tabla::find($id);

        // Pasar el ID de la tabla a la vista
        return view('campo.create', compact('campo', 'tablas', 'id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CampoRequest $request, $id)
    {
        $campo = new Campo($request->validated());
        $campo->tabla_id = $id;
        $campo->save();

        return redirect()->route('tablas.campos.index', $id)
            ->with('success', 'Campo created successfully.');
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($tablaId, $campoId)
    {
        $campo = Campo::where('id', $campoId)->where('tabla_id', $tablaId)->firstOrFail();

        return view('campo.edit', compact('campo', 'tablaId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CampoRequest $request, $tablaId, $campoId)
    {
        $campo = Campo::where('id', $campoId)->where('tabla_id', $tablaId)->firstOrFail();
        $campo->update($request->validated());

        return redirect()->route('tablas.campos.index', $tablaId)
            ->with('success', 'Campo updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($tablaId, $campoId)
    {
        $campo = Campo::where('id', $campoId)->where('tabla_id', $tablaId)->firstOrFail();
        $campo->delete();

        return redirect()->route('tablas.campos.index', $tablaId)
            ->with('success', 'Campo deleted successfully');
    }
}
