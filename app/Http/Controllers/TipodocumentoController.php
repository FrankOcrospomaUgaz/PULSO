<?php

namespace App\Http\Controllers;

use App\Models\Tipodocumento;
use App\Http\Requests\TipodocumentoRequest;
use App\Models\Operacione;
use App\Models\Tipomovimiento;
use Illuminate\Http\Request;

/**
 * Class TipodocumentoController
 * @package App\Http\Controllers
 */
class TipodocumentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10); // valor por defecto 10

        $query = Tipodocumento::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('abreviatura', 'ILIKE', "%{$search}%")
                    ->orWhere('stock', 'ILIKE', "%{$search}%");
            });
        }

        $tipodocumentos = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 28)
            ->where('estado', true)
            ->get();

        return view('tipodocumento.index', compact('tipodocumentos', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $tipodocumentos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipodocumento = new Tipodocumento();
        $tipomovimientos = Tipomovimiento::all();

        return view('tipodocumento.create', compact('tipodocumento', 'tipomovimientos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipodocumentoRequest $request)
    {
        try {
            $Tipodocumento = Tipodocumento::create($request->validated());

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de documento creado exitosamente.',
                'Tipodocumento' => $Tipodocumento,
                'redirect_url' => route('tipodocumento.index', ['page' => $pagina_actual])
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
        $tipodocumento = Tipodocumento::findOrFail($id);

        return view('tipodocumento.show', compact('tipodocumento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tipodocumento = Tipodocumento::findOrFail($id);
        $tipomovimientos = Tipomovimiento::all();

        return view('tipodocumento.edit', compact('tipodocumento', 'tipomovimientos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TipodocumentoRequest $request, Tipodocumento $tipodocumento)
    {
        try {
            $tipodocumento->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tipo de documento actualizado exitosamente.',
                'tipodocumento' => $tipodocumento,
                'redirect_url' => route('tipodocumento.index')
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
            $tipodocumento = Tipodocumento::findOrFail($id);
            $tipodocumento->delete();

            return redirect()->route('tipodocumento.index')
                ->with('success', 'Tipo de documento eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Hubo un error al eliminar el documento.']);
        }
    }
}
