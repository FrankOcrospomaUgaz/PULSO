<?php

namespace App\Http\Controllers;

use App\Models\Operacione;
use App\Http\Requests\OperacioneRequest;
use App\Models\Perfile;
use App\Models\RelOperacionsucursal;
use App\Models\RelOperacionsucursalperfil;
use App\Models\RelTablasucursal;
use App\Models\Sucursal;
use App\Models\Tabla;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class OperacioneController
 * @package App\Http\Controllers
 */
class OperacioneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search   = $request->input('search');
        $perPage  = $request->input('perPage', 10);

        // Operaciones asociadas a la tabla 'operaciones'
        $ope_ope = Operacione::where('tabla_id', 7)
            ->where('estado', true)
            ->get();

        $query = Operacione::with('tabla');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('comentario', 'ILIKE', "%{$search}%")
                    ->orWhere('accion', 'ILIKE', "%{$search}%")
                    ->orWhere('tipo', 'ILIKE', "%{$search}%")
                    ->orWhereHas('tabla', function ($rel) use ($search) {
                        $rel->where('descripcion', 'ILIKE', "%{$search}%")
                            ->orWhere('comentario', 'ILIKE', "%{$search}%");
                    });
            });
        }

        $operaciones = $query->paginate($perPage)->appends([
            'search'   => $search,
            'perPage' => $perPage,
        ]);

        return view('operacione.index', compact('operaciones', 'ope_ope', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $operaciones->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $operacione = new Operacione();
        $tablas = Tabla::all(); // Obtener todas las tablas
        return view('operacione.create', compact('operacione', 'tablas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OperacioneRequest $request)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Crear la operación
            $operacione = Operacione::create($request->validated());
            $tablaId = $operacione->tabla_id;  // Asumimos que el campo 'tabla_id' está en el modelo Operacione

            // Obtener todas las sucursales y perfiles
            $sucursales = Sucursal::where('situacion', 'A')->get();

            // Recorrer todas las sucursales
            foreach ($sucursales as $sucursal) {
                // Verificar si la tabla_id está relacionada con la sucursal en RelTablasucursal
                $tablaRelacionada = RelTablasucursal::where('tabla_id', $tablaId)
                    ->where('sucursal_id', $sucursal->id)
                    ->exists();

                // Si la tabla está relacionada con la sucursal
                if ($tablaRelacionada) {
                    // Obtener el último valor de 'orden' para esa sucursal y tabla
                    $ultimoOrden = RelOperacionsucursal::where('tabla_id', $tablaId)
                        ->where('sucursal_id', $sucursal->id)
                        ->max('orden');

                    // Si no existe un valor previo, comenzar desde 1
                    $nuevoOrden = $ultimoOrden ? $ultimoOrden + 1 : 1;

                    // Insertar el registro en RelOperacionsucursal con el nuevo valor de 'orden'
                    RelOperacionsucursal::create([
                        'orden' => $nuevoOrden,  // Asignar el valor secuencial de 'orden'
                        'situacion' => false,  // Asumimos un valor por defecto para 'situacion'
                        'operacion_id' => $operacione->id,
                        'tabla_id' => $tablaId,
                        'sucursal_id' => $sucursal->id
                    ]);
                }
            }

            // Confirmar la transacción
            DB::commit();

            $pagina_actual = $request->input('pagina_actual', 1);

            // Responder con éxito
            return response()->json([
                'success' => true,
                'message' => 'Operación creada exitosamente junto con las relaciones en RelOperacionsucursal y RelOperacionsucursalperfil.',
                'operacione' => $operacione,
                'redirect_url' => route('operaciones.index', ['page' => $pagina_actual])
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
    public function edit($id)
    {
        $operacione = Operacione::find($id);
        $tablas = Tabla::all(); // Obtener todas las tablas

        return view('operacione.edit', compact('operacione', 'tablas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OperacioneRequest $request, $id)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Obtener la operación a actualizar
            $operacione = Operacione::findOrFail($id);

            // Actualizar la operación con los datos del request
            $operacione->update($request->validated());
            $tablaId = $operacione->tabla_id;  // Asumimos que el campo 'tabla_id' está en el modelo Operacione

            // Obtener todas las sucursales activas
            $sucursales = Sucursal::where('situacion', 'A')->get();

            // Recorrer todas las sucursales
            foreach ($sucursales as $sucursal) {
                // Verificar si la tabla_id está relacionada con la sucursal en RelTablasucursal
                $tablaRelacionada = RelTablasucursal::where('tabla_id', $tablaId)
                    ->where('sucursal_id', $sucursal->id)
                    ->exists();

                // Si la tabla está relacionada con la sucursal
                if ($tablaRelacionada) {
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
                            'situacion' => false,  // Asumimos un valor por defecto para 'situacion'
                            'operacion_id' => $operacione->id,
                            'tabla_id' => $tablaId,
                            'sucursal_id' => $sucursal->id
                        ]);
                    }
                }
            }

            // Confirmar la transacción
            DB::commit();

            $pagina_actual = $request->input('pagina_actual', 1);

            // Responder con éxito
            return response()->json([
                'success' => true,
                'message' => 'Operación actualizada exitosamente junto con las relaciones en RelOperacionsucursal.',
                'operacione' => $operacione,
                'redirect_url' => route('operaciones.index', ['page' => $pagina_actual])
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
    public function destroy($id)
    {
        try {
            $operacione = Operacione::find($id);
            $operacione->update(['estado' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Operación eliminada correctamente.',
                'redirect_url' => route('operaciones.index')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
