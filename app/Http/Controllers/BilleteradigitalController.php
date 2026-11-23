<?php

namespace App\Http\Controllers;

use App\Models\Billeteradigital;
use App\Http\Requests\BilleteradigitalRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;

/**
 * Class BilleteradigitalController
 * @package App\Http\Controllers
 */
class BilleteradigitalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Operaciones activas de la tabla billeteradigital
        $operaciones = Operacione::where('tabla_id', 41)
            ->where('estado', true)
            ->get();

        // Query base
        $query = Billeteradigital::query();
        $perPage = $request->input('perPage', 10);
        // Si hay búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('orden', '::text ILIKE', "%{$search}%");
            });
        }

        // Paginación y orden por 'orden'
        $billeteras = $query->orderBy('orden', 'asc')->paginate($perPage);

        return view('billeteradigital.index', compact('billeteras', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $billeteras->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $billeteradigital = new Billeteradigital();
        return view('billeteradigital.create', compact('billeteradigital'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BilleteradigitalRequest $request)
    {
        try {
            $billeteradigital = Billeteradigital::create($request->validated());

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Billetera Digital creada exitosamente.',
                'billeteradigital' => $billeteradigital,
                'redirect_url' => route('billeteradigital.index', ['page' => $pagina_actual])
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
        $billeteradigital = Billeteradigital::find($id);
        return view('billeteradigital.edit', compact('billeteradigital'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BilleteradigitalRequest $request, Billeteradigital $billeteradigital)
    {
        try {
            $billeteradigital->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Billetera Digital actualizada exitosamente.',
                'billeteradigital' => $billeteradigital,
                'redirect_url' => route('billeteradigital.index')
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
            Billeteradigital::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Billetera Digital eliminada correctamente.',
                'redirect_url' => route('billeteradigital.index')
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
            $bille = Billeteradigital::find($id);
            $bille->update(['situacion' => true]);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Billetera digital activada exitosamente.',
                'encuesta' => $bille,
                'redirect_url' => route('billeteradigital.index')
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
            $bille = Billeteradigital::find($id);
            $bille->update(['situacion' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Billetera digital deactivada exitosamente.',
                'encuesta' => $bille,
                'redirect_url' => route('billeteradigital.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
