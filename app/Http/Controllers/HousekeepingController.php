<?php

namespace App\Http\Controllers;

use App\Models\Housekeeping;
use App\Http\Requests\HousekeepingRequest;
use App\Models\Habitacion;
use App\Models\HousekeepingDetalle;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\Piso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class HousekeepingController
 * @package App\Http\Controllers
 */
class HousekeepingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search'); // Obtener el término de búsqueda
        $query = Housekeeping::where('sucursal_id', Auth::user()->persona->sucursal_id); // Iniciar la consulta base
        $perPage = $request->input('perPage', 10); // valor por defecto 10
        // Obtener las columnas de la tabla 'housekeeping'
        $columns = Schema::getColumnListing('housekeeping');

        // Filtrar solo las columnas de tipo texto
        $textColumns = [];
        foreach ($columns as $column) {
            // Obtener el tipo de la columna desde 'information_schema.columns'
            $columnType = DB::select("
                SELECT data_type
                FROM information_schema.columns
                WHERE table_name = 'housekeeping' AND column_name = ?
            ", [$column]);

            // Verificar si el tipo de la columna es texto (character varying o text)
            if (isset($columnType[0]) && in_array($columnType[0]->data_type, ['character varying', 'text'])) {
                $textColumns[] = $column; // Solo agregamos las columnas de tipo texto
            }
        }

        if ($search) {
            $query->where(function ($q) use ($textColumns, $search) {
                foreach ($textColumns as $column) {
                    // Aplicar LOWER() solo a las columnas de tipo texto
                    $q->orWhereRaw("LOWER($column) LIKE ?", ['%' . strtolower($search) . '%']);
                }
            });
        }

        $housekeeping = $query->orderBy('fecha_programada', 'desc')->paginate($perPage); // Paginación y ordenación

        // Obtener las operaciones para la vista
        $operaciones = Operacione::where('tabla_id', 75)->where('estado', true)->get();

        return view('housekeeping.index', compact('housekeeping', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $housekeeping->perPage());
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $housekeeping = new Housekeeping();
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $habitaciones = Habitacion::where('sucursal_id', $persona->sucursal_id)->orderBy('numero')->get();
        $pisos = Piso::where('sucursal_id', $persona->sucursal_id)->get();

        $empleados = Persona::whereHas('rolpersonas.rol', function ($query) {
            $query->where('descripcion', 'Limpiador');
        })->where('sucursal_id', $persona->sucursal_id)->get();

        return view('housekeeping.create', compact('housekeeping', 'habitaciones', 'pisos', 'empleados'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        DB::beginTransaction();
        try {
            // Crear la jornada
            $housekeeping = Housekeeping::create([
                'fecha_programada' => $request->fecha_programada,
                'observaciones' => $request->observaciones,
                'sucursal_id' => $persona->sucursal_id,
            ]);

            // Decodificar el JSON de habitaciones en un array
            $habitaciones = json_decode($request->habitaciones, true);

            if (!is_array($habitaciones)) {
                throw new \Exception("El formato de las habitaciones no es válido.");
            }

            // Asignar habitaciones y empleados
            foreach ($habitaciones as $habitacion_id) {
                // Obtener el piso de la habitación
                $habitacion = Habitacion::find($habitacion_id);
                if (!$habitacion) {
                    throw new \Exception("Habitación con ID $habitacion_id no encontrada.");
                }

                // Obtener el empleado asignado al piso
                $empleado_id = $request->input("empleados_piso.{$habitacion->piso_id}");

                if (!$empleado_id) {
                    throw new \Exception("No se ha asignado un empleado al piso {$habitacion->piso_id}.");
                }

                // Guardar en housekeeping_detalle
                HousekeepingDetalle::create([
                    'jornada_id' => $housekeeping->id,
                    'habitacion_id' => $habitacion_id,
                    'empleado_id' => $empleado_id, // Ahora se obtiene correctamente
                    'estado' => 'Pendiente',
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Jornada(s) creada(s) exitosamente.',
                'redirect_url' => route('housekeeping.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function progreso($id)
    {
        $housekeeping = Housekeeping::findOrFail($id);

        // Obtener los detalles de la jornada de housekeeping con el estado de limpieza
        $detalles = HousekeepingDetalle::where('jornada_id', $id)
            ->with(['habitacion', 'persona'])
            ->get();

        return view('housekeeping.progreso', compact('housekeeping', 'detalles'));
    }

    public function asignar($id)
    {
        $housekeeping = Housekeeping::findOrFail($id);
        $user = Auth::user();
        // Obtener los detalles de la jornada de housekeeping con el estado de limpieza
        $detalles = HousekeepingDetalle::where('jornada_id', $id)
            ->with(['habitacion', 'persona'])
            ->get();
        $persona = Persona::find($user->persona_id);

        $empleados = Persona::whereHas('rolpersonas.rol', function ($query) {
            $query->where('descripcion', 'Limpiador');
        })->where('sucursal_id', $persona->sucursal_id)->get();

        return view('housekeeping.asignar', compact('housekeeping', 'detalles', 'empleados'));
    }

    public function asignarStore(Request $request)
    {

        DB::beginTransaction();
        try {
            foreach ($request->empleados as $detalleId => $empleadoId) {
                HousekeepingDetalle::where('id', $detalleId)
                    ->update(['empleado_id' => $empleadoId]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Jornada actualizada exitosamente.',
                'redirect_url' => route('housekeeping.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $housekeeping = Housekeeping::find($id);

        return view('housekeeping.show', compact('housekeeping'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $housekeeping = Housekeeping::findOrFail($id);
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $habitaciones = Habitacion::where('sucursal_id', $persona->sucursal_id)->orderBy('numero')->get();
        $pisos = Piso::where('sucursal_id', $persona->sucursal_id)->get();
        // Agrupar los detalles por piso y obtener el empleado asignado por piso
        $empleadoPorPiso = $housekeeping->housekeepingDetalles
            ->groupBy(fn($detalle) => $detalle->habitacion->piso_id)
            ->map(fn($detalles) => $detalles->first()->empleado_id)
            ->toArray();

        $empleados = Persona::whereHas('rolpersonas.rol', function ($query) {
            $query->where('descripcion', 'Limpiador');
        })->where('sucursal_id', $persona->sucursal_id)->get();
        $habitacionesSeleccionadas = $housekeeping->housekeepingDetalles->pluck('habitacion_id')->toArray();

        return view('housekeeping.edit', compact('housekeeping', 'habitaciones', 'pisos', 'empleados', 'habitacionesSeleccionadas', 'empleadoPorPiso'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        DB::beginTransaction();
        try {
            // Buscar la jornada a actualizar
            $housekeeping = Housekeeping::findOrFail($id);

            // Actualizar los datos principales
            $housekeeping->update([
                'fecha_programada' => $request->fecha_programada,
                'observaciones' => $request->observaciones,
                'sucursal_id' => $persona->sucursal_id,
            ]);

            // Decodificar el JSON de habitaciones en un array
            $habitaciones = json_decode($request->habitaciones, true);

            if (!is_array($habitaciones)) {
                throw new \Exception("El formato de las habitaciones no es válido.");
            }

            // Eliminar detalles antiguos antes de agregar los nuevos
            HousekeepingDetalle::where('jornada_id', $housekeeping->id)->delete();

            // Asignar habitaciones y empleados nuevamente
            foreach ($habitaciones as $habitacion_id) {
                // Obtener la habitación
                $habitacion = Habitacion::find($habitacion_id);
                if (!$habitacion) {
                    throw new \Exception("Habitación con ID $habitacion_id no encontrada.");
                }

                // Obtener el empleado asignado al piso
                $empleado_id = $request->input("empleados_piso.{$habitacion->piso_id}");

                if (!$empleado_id) {
                    throw new \Exception("No se ha asignado un empleado al piso {$habitacion->piso_id}.");
                }

                // Guardar en housekeeping_detalle
                HousekeepingDetalle::create([
                    'jornada_id' => $housekeeping->id,
                    'habitacion_id' => $habitacion_id,
                    'empleado_id' => $empleado_id,
                    'estado' => 'Pendiente',
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Jornada actualizada exitosamente.',
                'redirect_url' => route('housekeeping.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Housekeeping::find($id)->delete();

        return redirect()->route('housekeepings.index')
            ->with('success', 'Housekeeping deleted successfully');
    }
}
