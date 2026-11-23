<?php

namespace App\Http\Controllers;

use App\Models\MotivoMovimiento;
use App\Models\TipoMovimiento;
use App\Http\Requests\MotivoMovimientoRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;

class MotivomovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search   = $request->input('search');
        $perPage  = $request->input('perPage', 10);

        $query = MotivoMovimiento::with('tipomovimiento');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'ILIKE', "%{$search}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            });
        }

        $motivos = $query->orderBy('orden', 'asc')->paginate($perPage);

        $operaciones = Operacione::where('tabla_id', 45)
            ->where('estado', true)
            ->get();

        return view('motivomovimiento.index', compact('motivos', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $motivos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipomovimientos = TipoMovimiento::all(); // Obtener todos los tipos de movimientos
        $motivo = new MotivoMovimiento();
        return view('motivomovimiento.create', compact('motivo', 'tipomovimientos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MotivoMovimientoRequest $request)
    {
        try {
            MotivoMovimiento::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Motivo de Movimiento creado exitosamente.',
                'redirect_url' => route('motivomovimiento.index')
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
        $motivo = MotivoMovimiento::find($id);
        $tipomovimientos = TipoMovimiento::all(); // Obtener todos los tipos de movimientos
        return view('motivomovimiento.edit', compact('motivo', 'tipomovimientos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MotivoMovimientoRequest $request, MotivoMovimiento $motivo)
    {
        try {
            $motivo->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Motivo de Movimiento actualizado exitosamente.',
                'redirect_url' => route('motivomovimiento.index')
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
            MotivoMovimiento::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Motivo de Movimiento eliminado correctamente.',
                'redirect_url' => route('motivomovimiento.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar un MotivoMovimiento especificado.
     */
    public function activar($id)
    {
        try {
            $motivo = MotivoMovimiento::find($id);
            $motivo->update(['situacion' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Motivo de Movimiento activado exitosamente.',
                'encuesta' => $motivo,
                'redirect_url' => route('motivomovimiento.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar un MotivoMovimiento especificado.
     */
    public function desactivar($id)
    {
        try {
            $motivo = MotivoMovimiento::find($id);
            $motivo->update(['situacion' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Motivo de Movimiento desactivada exitosamente.',
                'encuesta' => $motivo,
                'redirect_url' => route('motivomovimiento.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
