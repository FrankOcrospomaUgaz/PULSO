<?php

namespace App\Http\Controllers;

use App\Models\Conceptopago;
use App\Http\Requests\ConceptopagoRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class ConceptopagoController
 * @package App\Http\Controllers
 */
class ConceptopagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);

        // Query base
        $query = Conceptopago::query();

        // Si existe búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('tipo', 'ILIKE', "%{$search}%")
                    ->orWhere('comentarios', 'ILIKE', "%{$search}%");
            });
        }

        // Paginación ordenada por descripción
        $conceptopagos = $query->orderBy('descripcion', 'asc')->paginate($perPage);

        // Operaciones relacionadas
        $operaciones = Operacione::where('tabla_id', 37)
            ->where('estado', true)
            ->get();

        return view('conceptopago.index', compact('conceptopagos', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $conceptopagos->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $conceptopago = new Conceptopago();
        return view('conceptopago.create', compact('conceptopago'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ConceptopagoRequest $request)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            $conceptopago = Conceptopago::create($request->validated());

            DB::commit(); // Confirmar la transacción

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Concepto de pago creado exitosamente.',
                'conceptopago' => $conceptopago,
                'redirect_url' => route('conceptopago.index', ['page' => $pagina_actual])
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción si ocurre un error

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
        $conceptopago = Conceptopago::find($id);
        return view('conceptopago.edit', compact('conceptopago'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ConceptopagoRequest $request, Conceptopago $conceptopago)
    {
        try {
            $conceptopago->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Concepto de pago actualizado exitosamente.',
                'conceptopago' => $conceptopago,
                'redirect_url' => route('conceptopago.index')
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
            Conceptopago::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Concepto de pago eliminado correctamente.',
                'redirect_url' => route('conceptopago.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    public function getPorTipo($tipo)
    {
        $conceptos_pago = Conceptopago::whereNotIn('id', [2, 3, 4])
            ->when($tipo === 'Ingreso', function ($query) {
                return $query->where('tipo', 'I');
            })
            ->when($tipo === 'Egreso', function ($query) {
                return $query->where('tipo', 'E');
            })
            ->when(!in_array($tipo, ['Ingreso', 'Egreso']), function ($query) {
                // Por defecto mostrar ingresos
                return $query->where('tipo', 'I');
            })
            ->get();

        return response()->json($conceptos_pago);
    }
}
