<?php

namespace App\Http\Controllers;

use App\Models\Operacione;
use App\Models\RelOperacionsucursal;
use App\Models\RelTablasucursal;
use App\Models\Sucursal;
use App\Models\Tabla;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class TablaOperacioneController
 * @package App\Http\Controllers
 */
class TablaOperacioneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $id)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10); // valor por defecto

            $query = Operacione::where('tabla_id', $id);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('descripcion', 'ILIKE', "%{$search}%")
                        ->orWhere('comentario', 'ILIKE', "%{$search}%")
                        ->orWhere('accion', 'ILIKE', "%{$search}%")
                        ->orWhere('tipo', 'ILIKE', "%{$search}%");
                });
            }

            $operaciones = $query->paginate($perPage)->appends([
                'search' => $search,
                'perPage' => $perPage,
            ]);

            $ope_ope = Operacione::where('tabla_id', 7)->get();

            return view('operacione.index', compact('operaciones', 'id', 'ope_ope', 'search', 'perPage'))
                ->with('i', (request()->input('page', 1) - 1) * $operaciones->perPage());
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        $operacione = new Operacione();
        $tablas = Tabla::where('id', $id)->get();

        return view('operacione.create', compact('operacione', 'tablas', 'id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Crear la operación
            $operacione = new Operacione($request->all());
            $operacione->tabla_id = $id;
            $operacione->save();

            // Obtener todas las sucursales activas
            $sucursales = Sucursal::where('situacion', 'A')->get();

            // Recorrer todas las sucursales
            foreach ($sucursales as $sucursal) {


                // Si la tabla está relacionada con la sucursal
                // Obtener el último valor de 'orden' para esa sucursal y tabla
                $ultimoOrden = RelOperacionsucursal::where('tabla_id', $id)
                    ->where('sucursal_id', $sucursal->id)
                    ->max('orden');

                // Si no existe un valor previo, comenzar desde 1
                $nuevoOrden = $ultimoOrden ? $ultimoOrden + 1 : 1;

                // Insertar el registro en RelOperacionsucursal con el nuevo valor de 'orden'
                RelOperacionsucursal::create([
                    'orden' => $nuevoOrden,  // Asignar el valor secuencial de 'orden'
                    'situacion' => false,  // Asumimos un valor por defecto para 'situacion'
                    'operacion_id' => $operacione->id,
                    'tabla_id' => $id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // Confirmar la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Operación creada exitosamente junto con las relaciones en RelOperacionsucursal.',
                'redirect_url' => route('tablas.operaciones.index', $id)
            ]);
        } catch (Exception $e) {
            // Si ocurre un error, revertir la transacción
            DB::rollBack();

            // Retornar el error
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($tablaId, $operacioneId)
    {
        try {
            $operacione = Operacione::where('id', $operacioneId)->where('tabla_id', $tablaId)->firstOrFail();
            $tablas = Tabla::where('id', $tablaId)->get();

            return view('operacione.edit', compact('operacione', 'tablaId', 'tablas'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $tablaId, $operacioneId)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Obtener la operación a actualizar
            $operacione = Operacione::where('id', $operacioneId)
                ->where('tabla_id', $tablaId)
                ->firstOrFail();

            // Actualizar la operación con los datos del request
            $operacione->update($request->all());

            // Obtener todas las sucursales activas
            $sucursales = Sucursal::where('situacion', 'A')->get();

            // Recorrer todas las sucursales
            foreach ($sucursales as $sucursal) {

                // Verificar si ya existe una relación en RelOperacionsucursal
                $relacionExistente = RelOperacionsucursal::where('tabla_id', $tablaId)
                    ->where('sucursal_id', $sucursal->id)
                    ->where('operacion_id', $operacione->id)
                    ->first();

                // Si no existe la relación, creamos una nueva
                if (!$relacionExistente) {
                    // Obtener el último valor de 'orden' para esa sucursal y tabla
                    $ultimoOrden = RelOperacionsucursal::where('tabla_id', $tablaId)
                        ->where('sucursal_id', $sucursal->id)
                        ->max('orden');

                    // Si no existe un valor previo, comenzar desde 1
                    $nuevoOrden = $ultimoOrden ? $ultimoOrden + 1 : 1;

                    // Insertar el registro en RelOperacionsucursal con el nuevo valor de 'orden'
                    RelOperacionsucursal::create([
                        'orden' => $nuevoOrden,  // Asignar el valor secuencial de 'orden'
                        'situacion' => true,  // Asumimos un valor por defecto para 'situacion'
                        'operacion_id' => $operacione->id,
                        'tabla_id' => $tablaId,
                        'sucursal_id' => $sucursal->id
                    ]);
                }
            }

            // Confirmar la transacción
            DB::commit();

            // Responder con éxito
            return response()->json([
                'success' => true,
                'message' => 'Operación actualizada exitosamente junto con las relaciones en RelOperacionsucursal.',
                'redirect_url' => route('tablas.operaciones.index', $tablaId)
            ]);
        } catch (Exception $e) {
            // Si ocurre un error, revertir la transacción
            DB::rollBack();

            // Retornar el error
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($tablaId, $operacioneId)
    {
        try {
            $operacione = Operacione::where('id', $operacioneId)->where('tabla_id', $tablaId)->firstOrFail();
            $operacione->delete();

            return response()->json([
                'success' => true,
                'message' => 'Operación eliminada exitosamente.',
                'redirect_url' => route('tablas.operaciones.index', $tablaId)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
