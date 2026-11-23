<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use App\Http\Requests\BancoRequest;
use App\Models\Operacione;
use Illuminate\Http\Request;

class BancoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Operaciones activas asociadas a la tabla 'bancos'
        $operaciones = Operacione::where('tabla_id', 40)
            ->where('estado', true)
            ->get();

        // Query base de bancos
        $query = Banco::query();

        // Si hay búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('orden', '::text ILIKE', "%{$search}%");
            });
        }

        // Paginación
        $bancos = $query->orderBy('orden', 'asc')->paginate(10);

        return view('banco.index', compact('bancos', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $bancos->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $banco = new Banco();
        return view('banco.create', compact('banco'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BancoRequest $request)
    {
        try {
            Banco::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Banco creado exitosamente.',
                'redirect_url' => route('banco.index')
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
        $banco = Banco::find($id);
        return view('banco.edit', compact('banco'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BancoRequest $request, Banco $banco)
    {
        try {
            $banco->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Banco actualizado exitosamente.',
                'redirect_url' => route('banco.index')
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
            Banco::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banco eliminado correctamente.',
                'redirect_url' => route('banco.index')
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
            $bank = Banco::find($id);
            $bank->update(['situacion' => true]);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Banco activada exitosamente.',
                'encuesta' => $bank,
                'redirect_url' => route('banco.index')
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
            $bank = Banco::find($id);
            $bank->update(['situacion' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Banco deactivada exitosamente.',
                'encuesta' => $bank,
                'redirect_url' => route('banco.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
