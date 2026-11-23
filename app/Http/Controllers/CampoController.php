<?php

namespace App\Http\Controllers;

use App\Models\Campo;
use App\Http\Requests\CampoRequest;
use App\Models\Operacione;
use App\Models\RelCamposucursal;
use App\Models\RelTablasucursal;
use App\Models\Sucursal;
use App\Models\Tabla;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
/**
 * Class CampoController
 * @package App\Http\Controllers
 */
class CampoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Campo::query();
        $perPage = $request->input('perPage', 10);
        // Obtener todas las columnas de la tabla 'campos'
        $columns = Schema::getColumnListing('campos');

        if ($search) {
            // Agregar una condición OR para cada columna
            $query->where(function($q) use ($columns, $search) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }

        $campos = $query->paginate($perPage);
        $operaciones = Operacione::where('tabla_id', 6)->where('estado', true)->get();

        return view('campo.index', compact('campos', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $campos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $campo = new Campo();
        $tablas = Tabla::all(); // Obtener todas las tablas

        return view('campo.create', compact('campo', 'tablas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CampoRequest $request)
    {
        DB::beginTransaction(); // Iniciar la transacción
    
        try {
            // Validar los datos del request
            $data = $request->validated();
    
            // Establecer el valor predeterminado de 'validacion' a 'N' si no se ha marcado el checkbox
            $data['validacion'] = $request->has('validacion') ? 'S' : 'N';
    
            // Crear el campo con los datos validados
            $campo = Campo::create($data);
            $tablaId = $campo->tabla_id;  // Asumimos que el campo 'tabla_id' está en el modelo Campo
    
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
                    // Obtener el último valor de 'orden' para esa sucursal y tabla
                    $ultimoOrden = RelCamposucursal::where('tabla_id', $tablaId)
                                                   ->where('sucursal_id', $sucursal->id)
                                                   ->max('orden');
    
                    // Si no existe un valor previo, comenzar desde 1
                    $nuevoOrden = $ultimoOrden ? $ultimoOrden + 1 : 1;
    
                    // Insertar el registro en RelCamposucursal con el nuevo valor de 'orden'
                    RelCamposucursal::create([
                        'tipo' => 'G',  // Asignar el tipo que viene del request
                        'orden' => $nuevoOrden,  // Asignar el valor secuencial de 'orden'
                        'situacion' => false,  // Asumimos que la situación es 'A' (Activado)
                        'campo_id' => $campo->id,
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
                'message' => 'Campo creado exitosamente junto con las relaciones en RelCamposucursal.',
                'campo' => $campo,
                'redirect_url' => route('campos.index', ['page' => $pagina_actual])
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
        $campo = Campo::find($id);
        $tablas = Tabla::all(); // Obtener todas las tablas

        return view('campo.edit', compact('campo', 'tablas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CampoRequest $request, Campo $campo)
    {
        try {
            $data = $request->validated();
            $data['validacion'] = $request->has('validacion') ? 'S' : 'N'; // Actualizar el campo 'validacion'

            // Actualizar el campo
            $campo->update($data);

            // Respuesta JSON exitosa
            return response()->json([
                'success' => true,
                'message' => 'Campo actualizado exitosamente.',
                'campo' => $campo,
                'redirect_url' => route('campos.index')
            ]);
        } catch (Exception $e) {
            // Respuesta JSON en caso de error
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
            $campo = Campo::find($id);
            if ($campo) {
                $campo->delete(); // Eliminación física, cambiar si es necesaria eliminación lógica

                // Respuesta JSON exitosa
                return response()->json([
                    'success' => true,
                    'message' => 'Campo eliminado correctamente.',
                    'redirect_url' => route('campos.index')
                ]);
            } else {
                throw new Exception("El campo no existe.");
            }
        } catch (Exception $e) {
            // Respuesta JSON en caso de error
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
