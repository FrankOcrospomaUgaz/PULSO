<?php

namespace App\Http\Controllers;

use App\Models\Pasarelapago;
use App\Http\Requests\PasarelapagoRequest;
use App\Models\Mediopago;
use App\Models\Operacione;
use App\Models\RelMediopagopasarela;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class PasarelapagoController
 * @package App\Http\Controllers
 */
class PasarelapagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = Pasarelapago::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('orden', 'ILIKE', "%{$search}%")
                    ->orWhere('situacion', 'ILIKE', "%{$search}%");
            });
        }

        $pasarelas = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 42)
            ->where('estado', true)
            ->get();

        // Obtener medios de pago permitidos
        $mediospermitidos = [];
        foreach ($pasarelas as $pasarela) {
            $medios = RelMediopagopasarela::where('pasarela_id', $pasarela->id)
                ->join('mediopago', 'rel_mediopagopasarela.medio_id', '=', 'mediopago.id')
                ->pluck('mediopago.descripcion')
                ->toArray();

            $mediospermitidos[$pasarela->id] = implode(', ', $medios);
        }

        return view('pasarelapago.index', compact(
            'pasarelas',
            'operaciones',
            'mediospermitidos',
            'search',
            'perPage'
        ))->with('i', (request()->input('page', 1) - 1) * $pasarelas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pasarelapago = new Pasarelapago();
        $mediopagos = Mediopago::where('situacion', true)->get();

        return view('pasarelapago.create', compact('pasarelapago', 'mediopagos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PasarelapagoRequest $request)
    {
        DB::beginTransaction();
        try {
            // Crear la nueva pasarela de pago
            $pasarelapago = Pasarelapago::create($request->validated());

            // Obtener los IDs de medios de pago seleccionados del request
            $medioPagoIds = $request->input('medio_pago_ids', '');

            // Convertir la cadena de IDs en un array, eliminando posibles espacios en blanco
            $medioPagoIdsArray = array_filter(array_map('trim', explode(',', $medioPagoIds)));

            // Insertar las relaciones en la tabla rel_posmediopago
            foreach ($medioPagoIdsArray as $medioId) {
                RelMediopagopasarela::create([
                    'pasarela_id' => $pasarelapago->id,
                    'medio_id' => $medioId,
                ]);
            }

            // Confirmar la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pasarela de Pago creada exitosamente.',
                'pasarelapago' => $pasarelapago,
                'redirect_url' => route('pasarelapago.index')
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

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
        $pasarelapago = Pasarelapago::find($id);

        // Obtener los medios de pago relacionados con la pasarela
        $medioPagoRelacionados = RelMediopagopasarela::where('pasarela_id', $id)->pluck('medio_id')->toArray();

        // Obtener todos los medios de pago activos
        $mediopagos = Mediopago::where('situacion', true)->get();

        return view('pasarelapago.edit', compact('pasarelapago', 'mediopagos', 'medioPagoRelacionados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PasarelapagoRequest $request, Pasarelapago $pasarelapago)
    {
        DB::beginTransaction();
        try {
            // Actualizar la pasarela de pago
            $pasarelapago->update($request->validated());

            // Obtener los nuevos IDs de medios de pago seleccionados del request
            $medioPagoIds = $request->input('medio_pago_ids', []);

            // Convertir la cadena de IDs en un array, eliminando posibles espacios en blanco
            $medioPagoIdsArray = array_filter(array_map('trim', explode(',', $medioPagoIds)));


            // Eliminar relaciones existentes
            RelMediopagopasarela::where('pasarela_id', $pasarelapago->id)->delete();

            // Insertar las nuevas relaciones
            foreach ($medioPagoIdsArray as $medioId) {
                RelMediopagopasarela::create([
                    'pasarela_id' => $pasarelapago->id,
                    'medio_id' => $medioId,
                ]);
            }

            // Confirmar la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pasarela de Pago actualizada exitosamente.',
                'pasarelapago' => $pasarelapago,
                'redirect_url' => route('pasarelapago.index')
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

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
        DB::beginTransaction();
        try {
            RelMediopagopasarela::where('pasarela_id', $id)->delete();

            Pasarelapago::find($id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pasarela de Pago eliminada correctamente.',
                'redirect_url' => route('pasarelapago.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    public function activar($id)
    {
        try {
            $pasarela = Pasarelapago::find($id);
            $pasarela->update(['situacion' => true]);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Pasarela de Pago activada exitosamente.',
                'encuesta' => $pasarela,
                'redirect_url' => route('pasarelapago.index')
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
            $pasarela = Pasarelapago::find($id);
            $pasarela->update(['situacion' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Pasarela de Pago desactivada exitosamente.',
                'encuesta' => $pasarela,
                'redirect_url' => route('pasarelapago.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
