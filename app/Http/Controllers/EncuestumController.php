<?php

namespace App\Http\Controllers;

use App\Models\Encuestum;
use App\Http\Requests\EncuestumRequest;
use App\Models\DetEncuestum;
use App\Models\Movhabitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\Respuestum;
use App\Models\Sucursal;
use App\Models\ValordetRespuestum;
use Carbon\Carbon;

/**
 * Class EncuestumController
 * @package App\Http\Controllers
 */
class EncuestumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search'); // Obtener el término de búsqueda
        $query = Encuestum::where('sucursal_id', Auth::user()->persona->sucursal_id); // Iniciar la consulta base
        $perPage = $request->input('perPage', 10); // valor por defecto 10
        // Obtener las columnas de la tabla 'encuesta'
        $columns = Schema::getColumnListing('encuesta');

        // Filtrar solo las columnas de tipo texto
        $textColumns = [];
        foreach ($columns as $column) {
            // Obtener el tipo de la columna desde 'information_schema.columns'
            $columnType = DB::select("
                SELECT data_type
                FROM information_schema.columns
                WHERE table_name = 'encuesta' AND column_name = ?
            ", [$column]);

            // Verificar si el tipo de la columna es texto (character varying o text)
            if (isset($columnType[0]) && in_array($columnType[0]->data_type, ['character varying', 'text'])) {
                $textColumns[] = $column;
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

        $encuestas = $query->orderBy('id', 'desc')->paginate($perPage); // Paginación y ordenación

        // Obtener las operaciones para la vista
        $operaciones = Operacione::where('tabla_id', 71)->where('estado', true)->get();

        return view('encuestum.index', compact('encuestas', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $encuestas->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return view('encuestum.create',);
    }

    public function verPreguntas($id)
    {
        $query = DetEncuestum::with(['categoriapregunta', 'tipopregunta', 'encuesta', 'sucursal'])
            ->where('encuesta_id', $id); // Filtra por encuesta_id

        // Obtener datos paginados
        $detEncuestas = $query->paginate(10);

        // Listas para los filtros
        $situaciones = DetEncuestum::where('encuesta_id', $id)->select('situacion')->distinct()->pluck('situacion');
        $operaciones = Operacione::where('tabla_id', 72)->where('estado', true)->get();

        return view('det-encuestum.index', compact('detEncuestas', 'situaciones',  'operaciones', 'id'));
    }

    public function estadisticas($id)
    {
        $query = DetEncuestum::with(['categoriapregunta', 'tipopregunta', 'encuesta', 'sucursal'])
            ->where('encuesta_id', $id);

        $detEncuestas = $query->paginate(10);

        $situaciones = DetEncuestum::where('encuesta_id', $id)
            ->select('situacion')
            ->distinct()
            ->pluck('situacion');

        $operaciones = Operacione::where('tabla_id', 72)
            ->where('estado', true)
            ->get();

        // 📊 Respuestas por responsable
        $sucursal_id = auth()->user()->Persona->sucursal_id; // ajusta según corresponda
        $respuestasPorResponsable = Movhabitacion::where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->with(['movimiento.Responsable']) // cargamos responsable
            ->get()
            ->groupBy(function ($item) {
                return $item->movimiento->Responsable->nombres ?? 'Sin responsable';
            })
            ->map(function ($items, $responsable) use ($sucursal_id) {
                // IDs de movimientos de este responsable
                $movimientoIds = $items->pluck('movimiento_id')->unique();

                // Contar respuestas asociadas a esos movimientos
                $respuestasCount = Respuestum::whereIn('movimiento_id', $movimientoIds)
                    ->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at')
                    ->count();
             
                return [
                    'responsable'   => $responsable,
                    'respuestas'    => $respuestasCount,
                    'alojamientos'  => $items->count(),
                ];
            })
            ->values();
      //  dd($respuestasPorResponsable);
        return view('encuestum.estadisticas', compact(
            'detEncuestas',
            'situaciones',
            'operaciones',
            'respuestasPorResponsable'
        ));
    }

    public function verRespuestas($id)
    {
        $query = DetEncuestum::with(['categoriapregunta', 'tipopregunta', 'encuesta', 'sucursal'])
            ->where('encuesta_id', $id); // Filtra por encuesta_id
        // Obtener datos paginados
        $detEncuestas = $query->paginate(10);

        // Listas para los filtros
        $situaciones = DetEncuestum::where('encuesta_id', $id)->select('situacion')->distinct()->pluck('situacion');
        $respuestas = Respuestum::where('encuesta_id', $id)->paginate(10);

        $operaciones = Operacione::where('tabla_id', 73)->where('estado', true)->get();

        return view('det-respuestum.index', compact('detEncuestas', 'situaciones', 'operaciones', 'respuestas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validación de los datos
            $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'fechavencimiento' => 'nullable|date',
                'horavencimiento' => 'nullable|date_format:H:i',
                'mensaje_finalizacion' => 'required|string',
                'imprimible' => 'nullable|in:S,N',
            ]);

            // Obtener el último ID de la tabla, asegurando que esté correctamente ordenado
            $ultimoRegistro = Encuestum::orderBy('id', 'desc')->first();
            $nuevoId = $ultimoRegistro ? $ultimoRegistro->id + 1 : 1;

            // Procesar fecha y hora de vencimiento
            $fechaVencimiento = $request->fechavencimiento;
            $horaVencimiento = $request->horavencimiento;

            if ($fechaVencimiento && $horaVencimiento) {
                $fechavencimiento = Carbon::createFromFormat('Y-m-d H:i', "$fechaVencimiento $horaVencimiento");
            } elseif ($fechaVencimiento) {
                $fechavencimiento = Carbon::parse($fechaVencimiento);
            } else {
                $fechavencimiento = null;
            }

            $user = Auth::user();
            $persona = Persona::find($user->persona_id);

            // Crear la encuesta en la base de datos con el nuevo ID
            $encuesta = Encuestum::create([
                'id' => $nuevoId, // Forzar el autoincremento
                'uuid' => \Illuminate\Support\Str::uuid(),
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'fecha' => now(),
                'fechavencimiento' => $fechavencimiento,
                'mensaje_finalizacion' => $request->mensaje_finalizacion,
                'imprimible' => $request->imprimible ?? 'N',
                'validacion' => $request->validacion ?? 'N',
                'estado' => 'VIGENTE',
                'situacion' => 'E',
                'sucursal_id' => $persona->sucursal_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Encuesta creada exitosamente.',
                'redirect_url' => route('encuesta.index')
            ]);
        } catch (\Exception $e) {
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
        $encuestum = Encuestum::find($id);

        return view('encuestum.show', compact('encuestum'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $encuestum = Encuestum::find($id);

        return view('encuestum.edit', compact('encuestum'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validación de los datos
            $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'fechavencimiento' => 'nullable|date',
                'horavencimiento' => 'nullable|date_format:H:i',
                'mensaje_finalizacion' => 'required|string',
                'imprimible' => 'nullable|in:S,N',
            ]);

            // Buscar la encuesta a actualizar
            $encuesta = Encuestum::findOrFail($id);

            // Procesar fecha y hora de vencimiento
            $fechaVencimiento = $request->fechavencimiento;
            $horaVencimiento = $request->horavencimiento;

            if ($fechaVencimiento && $horaVencimiento) {
                $fechavencimiento = Carbon::createFromFormat('Y-m-d H:i', "$fechaVencimiento $horaVencimiento");
            } elseif ($fechaVencimiento) {
                $fechavencimiento = Carbon::parse($fechaVencimiento);
            } else {
                $fechavencimiento = null;
            }

            // Actualizar datos en la base de datos
            $encuesta->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'fechavencimiento' => $fechavencimiento,
                'mensaje_finalizacion' => $request->mensaje_finalizacion,
                'imprimible' => $request->imprimible ?? 'N',
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Encuesta actualizado exitosamente.',
                'redirect_url' => route('encuesta.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            Encuestum::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Encuesta eliminada correctamente.',
                'redirect_url' => route('encuesta.index')
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
            $encuesta = Encuestum::find($id);
            $encuesta->update(['situacion' => 'E']);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Encuesta activada exitosamente.',
                'encuesta' => $encuesta,
                'redirect_url' => route('encuesta.index')
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
            $encuesta = Encuestum::find($id);
            $encuesta->update(['situacion' => 'D']);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Encuesta desactivada exitosamente.',
                'encuesta' => $encuesta,
                'redirect_url' => route('encuesta.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function finalizar($id)
    {
        try {
            $encuesta = Encuestum::find($id);
            $encuesta->update(['estado' => 'FINALIZADO']);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Encuesta finalizado exitosamente.',
                'encuesta' => $encuesta,
                'redirect_url' => route('encuesta.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
public function detalles($id)
{
    $valordetRespuesta = ValordetRespuestum::whereHas('detRespuestum', function ($query) use ($id) {
        $query->where('respuesta_id', $id);
    })
    ->paginate();

    return view('valordet-respuestum.index', compact('valordetRespuesta', 'id'))
        ->with('i', (request()->input('page', 1) - 1) * $valordetRespuesta->perPage());
}


}
