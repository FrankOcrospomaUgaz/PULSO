<?php

namespace App\Http\Controllers;

use App\Models\Motivoreserva;
use App\Models\Operacione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MotivoReservaController extends Controller
{
    /**
     * Muestra la lista de motivos de reserva.
     */
    public function index(Request $request)
    {
        $search = $request->input('search'); // Buscar por código o descripción
        $perPage  = $request->input('perPage', 10);
        $query = Motivoreserva::where('situacion', true); // Filtrar solo activos

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'ILIKE', "%$search%")
                    ->orWhere('descripcion', 'ILIKE', "%$search%");
            });
        }

        $motivoreservas = $query->orderBy('orden', 'asc')->paginate($perPage);
        $operaciones = Operacione::where('tabla_id', 76)->where('estado', true)->get();

        return view('motivoreserva.index', compact('motivoreservas', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $motivoreservas->perPage());
    }

    /**
     * Muestra el formulario para crear un nuevo motivo de reserva.
     */
    public function create()
    {
        $motivo = new Motivoreserva();
        return view('motivoreserva.create', compact('motivo'));
    }

    /**
     * Guarda un nuevo motivo de reserva en la base de datos.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'codigo' => 'required|string|max:255|unique:motivoreserva,codigo',
                'descripcion' => 'required|string|max:255',
                'orden' => 'required|integer|min:1',
            ]);

            Motivoreserva::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Mortivo de reserva creada(s) exitosamente.',
                'redirect_url' => route('motivoreserva.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra un motivo de reserva específico.
     */
    public function show($id)
    {
        $motivo = Motivoreserva::findOrFail($id);
        return view('motivoreserva.show', compact('motivo'));
    }

    /**
     * Muestra el formulario para editar un motivo de reserva.
     */
    public function edit($id)
    {
        $motivo = Motivoreserva::findOrFail($id);
        return view('motivoreserva.edit', compact('motivo'));
    }

    /**
     * Actualiza un motivo de reserva en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'codigo' => "required|string|max:255|unique:motivoreserva,codigo,$id",
            'descripcion' => 'required|string|max:255',
            'orden' => 'required|integer|min:1',
            'situacion' => 'boolean',
        ]);

        $motivo = Motivoreserva::findOrFail($id);
        $motivo->update($request->all());

        return redirect()->route('motivoreserva.index')
            ->with('success', 'Motivo de reserva actualizado correctamente.');
    }

    /**
     * Elimina un motivo de reserva (soft delete).
     */
    public function destroy($id)
    {
        $motivo = Motivoreserva::findOrFail($id);
        $motivo->delete(); // Soft delete

        return redirect()->route('motivoreserva.index')
            ->with('success', 'Motivo de reserva eliminado correctamente.');
    }

    /**
     * Recupera un motivo de reserva eliminado (soft delete).
     */
    public function restore($id)
    {
        $motivo = Motivoreserva::onlyTrashed()->findOrFail($id);
        $motivo->restore();

        return redirect()->route('motivoreserva.index')
            ->with('success', 'Motivo de reserva restaurado correctamente.');
    }
}
