<?php

namespace App\Http\Controllers;

use App\Models\Mediopago;
use App\Http\Requests\MediopagoRequest;
use App\Models\Operacione;
use App\Models\RelMediopagopasarela;
use Illuminate\Http\Request;

/**
 * Class MediopagoController
 * @package App\Http\Controllers
 */
class MediopagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search  = $request->input('search');

        // Query base
        $query = Mediopago::query();

        // Filtro de búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(descripcion) LIKE ?", ['%' . strtolower($search) . '%'])
                    ->orWhereRaw("CAST(orden AS TEXT) LIKE ?", ['%' . $search . '%']);
            });
        }

        // Ejecutar query con paginación
        $mediopagos = $query->orderBy('orden', 'asc')->paginate($perPage);

        // Operaciones relacionadas
        $operaciones = Operacione::where('tabla_id', 38)
            ->where('estado', true)
            ->get();

        return view('mediopago.index', compact('mediopagos', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $mediopagos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mediopago = new Mediopago();
        return view('mediopago.create', compact('mediopago'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MediopagoRequest $request)
    {
        try {
            $mediopago = Mediopago::create($request->validated());

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Mediopago creado exitosamente.',
                'mediopago' => $mediopago,
                'redirect_url' => route('mediopago.index', ['page' => $pagina_actual])
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
        $mediopago = Mediopago::find($id);
        return view('mediopago.edit', compact('mediopago'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(MediopagoRequest $request, Mediopago $mediopago)
    {
        try {
            $mediopago->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Mediopago actualizado exitosamente.',
                'mediopago' => $mediopago,
                'redirect_url' => route('mediopago.index')
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
            Mediopago::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mediopago eliminado correctamente.',
                'redirect_url' => route('mediopago.index')
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
            $medio = Mediopago::find($id);
            $medio->update(['situacion' => true]);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Medio pago activado exitosamente.',
                'encuesta' => $medio,
                'redirect_url' => route('mediopago.index')
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
            $medio = Mediopago::find($id);
            $medio->update(['situacion' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Medio pago desactivado exitosamente.',
                'encuesta' => $medio,
                'redirect_url' => route('mediopago.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    public function listarMedioPago($idpasarela)
    {
        try {
            if ($idpasarela == 0) {
                // Obtener todos los medios de pago con situación 'true'
                $mediospago = Mediopago::where('situacion', true)->get();
            } else {
                // Obtener los IDs de los medios de pago relacionados con la pasarela
                $medioPagoRelacionados = RelMediopagopasarela::where('pasarela_id', $idpasarela)
                    ->pluck('medio_id')
                    ->toArray();

                // Obtener los medios de pago con situación 'true' que no estén relacionados
                $mediospago = Mediopago::where('situacion', true)
                    ->whereNotIn('id', $medioPagoRelacionados)
                    ->get();
            }

            // Preparar la respuesta con los medios de pago
            $response = [
                'status' => 200,
                'mediospago' => $mediospago->keyBy('descripcion')->toArray()
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
