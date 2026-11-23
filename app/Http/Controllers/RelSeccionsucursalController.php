<?php

namespace App\Http\Controllers;

use App\Models\RelSeccionsucursal;
use App\Http\Requests\RelSeccionsucursalRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;

/**
 * Class RelSeccionsucursalController
 * @package App\Http\Controllers
 */
class RelSeccionsucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $sucursal_id)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = RelSeccionsucursal::with('seccione')
            ->where('sucursal_id', $sucursal_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('seccione', function ($sub) use ($search) {
                    $sub->where('descripcion', 'ILIKE', "%{$search}%")
                        ->orWhere('abreviatura', 'ILIKE', "%{$search}%")
                        ->orWhere('orden', 'ILIKE', "%{$search}%")
                        ->orWhere('subtitulo', 'ILIKE', "%{$search}%")
                        ->orWhere('comentario', 'ILIKE', "%{$search}%")
                        ->orWhere('situacion', 'ILIKE', "%{$search}%");
                });
            });
        }

        $relSeccionsucursals = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 31)
            ->where('estado', true)
            ->get();

        return view('rel-seccionsucursal.index', compact('relSeccionsucursals', 'operaciones', 'sucursal_id', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $relSeccionsucursals->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $relSeccionsucursal = new RelSeccionsucursal();
        return view('rel-seccionsucursal.create', compact('relSeccionsucursal'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RelSeccionsucursalRequest $request)
    {
        RelSeccionsucursal::create($request->validated());

        return redirect()->route('rel-seccionsucursals.index')
            ->with('success', 'RelSeccionsucursal created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $relSeccionsucursal = RelSeccionsucursal::find($id);

        return view('rel-seccionsucursal.show', compact('relSeccionsucursal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $relSeccionsucursal = RelSeccionsucursal::find($id);

        return view('rel-seccionsucursal.edit', compact('relSeccionsucursal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RelSeccionsucursalRequest $request, RelSeccionsucursal $relSeccionsucursal)
    {
        $relSeccionsucursal->update($request->validated());

        return redirect()->route('rel-seccionsucursals.index')
            ->with('success', 'RelSeccionsucursal updated successfully');
    }

    public function destroy($id)
    {
        RelSeccionsucursal::find($id)->delete();

        return redirect()->route('rel-seccionsucursals.index')
            ->with('success', 'RelSeccionsucursal deleted successfully');
    }

    public function activar($id)
    {
        try {
            $relSeccionsucursal = RelSeccionsucursal::find($id);
            $relSeccionsucursal->update(['situacion' => 'A']);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Seccion de sucursal activada exitosamente.',
                'encuesta' => $relSeccionsucursal,
                'redirect_url' => route('sucursal.verSecciones', $relSeccionsucursal->sucursal_id)
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
            $relSeccionsucursal = RelSeccionsucursal::find($id);
            $relSeccionsucursal->update(['situacion' => 'D']);

            return response()->json([
                'success' => true,
                'message' => 'Seccion de sucursal desactivada exitosamente.',
                'encuesta' => $relSeccionsucursal,
                'redirect_url' => route('sucursal.verSecciones', $relSeccionsucursal->sucursal_id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
