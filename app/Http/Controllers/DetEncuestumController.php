<?php


namespace App\Http\Controllers;

use App\Http\Requests\DetEncuestumRequest;
use App\Models\DetEncuestum;
use App\Models\Categoriapreguntum;
use App\Models\Tipopreguntum;
use App\Models\Encuestum;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DetEncuestumController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index(Request $request)
    {
        $search   = $request->input('search');
        $perPage  = $request->input('perPage', 10);

        $query = DetEncuestum::with(['categoriapregunta', 'tipopregunta', 'encuesta', 'sucursal']);

        // 🔹 Filtros
        if ($request->filled('situacion')) {
            $query->where('situacion', $request->situacion);
        }

        if ($request->filled('encuesta_id')) {
            $query->where('encuesta_id', $request->encuesta_id);
        }

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        // 🔹 Búsqueda general (en texto propio + relaciones)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(enunciado) LIKE ?", ['%' . strtolower($search) . '%'])
                    ->orWhereRaw("LOWER(opciones) LIKE ?", ['%' . strtolower($search) . '%'])
                    // Relación categoría
                    ->orWhereHas('categoriapregunta', function ($q) use ($search) {
                        $q->whereRaw("LOWER(nombre) LIKE ?", ['%' . strtolower($search) . '%']);
                    })
                    // Relación encuesta
                    ->orWhereHas('encuesta', function ($q) use ($search) {
                        $q->whereRaw("LOWER(nombre) LIKE ?", ['%' . strtolower($search) . '%']);
                    })
                    // Relación sucursal
                    ->orWhereHas('sucursal', function ($q) use ($search) {
                        $q->whereRaw("LOWER(nombre) LIKE ?", ['%' . strtolower($search) . '%']);
                    });
            });
        }

        // 🔹 Paginación
        $detEncuestas = $query->orderBy('orden', 'asc')->paginate($perPage);

        // 🔹 Listas para filtros
        $situaciones = DetEncuestum::select('situacion')->distinct()->pluck('situacion');
        $encuestas   = Encuestum::all();
        $sucursales  = Sucursal::all();
        $operaciones = Operacione::where('tabla_id', 72)->where('estado', true)->get();

        return view('det-encuestum.index', compact(
            'detEncuestas',
            'situaciones',
            'encuestas',
            'sucursales',
            'operaciones'
        ))->with('i', (request()->input('page', 1) - 1) * $detEncuestas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($encuesta_id)
    {
        $detEncuestum = new DetEncuestum();
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $categorias = Categoriapreguntum::where('sucursal_id', $persona->sucursal_id)->get(); // Suponiendo que tienes un modelo para esto
        $tipos = Tipopreguntum::all(); // Lista de ti

        return view('det-encuestum.create', compact('detEncuestum', 'categorias', 'tipos', 'encuesta_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DetEncuestumRequest $request)
    {
        DB::beginTransaction();

        try {
            // Obtener y limpiar opciones
            $opcionesInput = $request->input('opciones');

            if ($this->isJson($opcionesInput)) {
                $opciones = json_decode($opcionesInput, true);
            } else {
                $opciones = explode(',', $opcionesInput);
            }

            $opciones = array_map('trim', $opciones);
            $opciones = array_values(array_filter($opciones));

            // Crear nueva pregunta
            $detEncuestum = DetEncuestum::create([
                'encuesta_id' => $request->input('encuesta_id'),
                'enunciado' => $request->input('enunciado'),
                'categoriapregunta_id' => $request->input('categoriapregunta_id'),
                'categoriapregunta' => Categoriapreguntum::find($request->input('categoriapregunta_id'))->nombre,
                'sucursal_id' => Encuestum::find($request->input('encuesta_id'))->sucursal_id,
                'tipopregunta_id' => $request->input('tipopregunta_id'),
                'opciones' => json_encode($opciones, JSON_UNESCAPED_UNICODE) ?? '[]', // Guardar limpio
                'obligatorio' => $request->has('obligatorio') ? 1 : 0
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pregunta creada exitosamente.',
                'detEncuestum' => $detEncuestum,
                'redirect_url' => route('encuesta.preguntas', ['id' => $request->input('encuesta_id')])
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
        $detEncuestum = DetEncuestum::find($id);

        return view('det-encuestum.show', compact('detEncuestum'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $detEncuestum = DetEncuestum::find($id);
        $encuesta_id = $detEncuestum->encuesta_id;
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        // Variables adicionales para los selects y otras opciones del formulario
        $categorias = Categoriapreguntum::where('sucursal_id', $persona->sucursal_id)->get(); // Suponiendo que tienes un modelo para esto
        $tipos = Tipopreguntum::all(); // Lista de tipos de preguntas
        $encuestas = Encuestum::all(); // Lista de encuestas disponibles
        $sucursales = Sucursal::all(); // Lista de sucursales disponibles

        return view('det-encuestum.edit', compact(
            'detEncuestum',
            'id',
            'categorias',
            'tipos',
            'encuestas',
            'sucursales',
            'encuesta_id'
        ));
    }
    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DetEncuestumRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $detEncuestum = DetEncuestum::findOrFail($id);

            // Obtener y limpiar opciones
            $opcionesInput = $request->input('opciones');

            if ($this->isJson($opcionesInput)) {
                $opciones = json_decode($opcionesInput, true);
            } else {
                $opciones = explode(',', $opcionesInput);
            }

            $opciones = array_map('trim', $opciones);
            $opciones = array_values(array_filter($opciones));

            // Actualizar los datos
            $detEncuestum->update([
                'enunciado' => $request->input('enunciado'),
                'categoriapregunta_id' => $request->input('categoriapregunta_id'),
                'tipopregunta_id' => $request->input('tipopregunta_id'),
                'opciones' => json_encode($opciones, JSON_UNESCAPED_UNICODE), // Guardar limpio
                'obligatorio' => $request->has('obligatorio') ? 1 : 0
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pregunta actualizada exitosamente.',
                'detEncuestum' => $detEncuestum,
                'redirect_url' => route('encuesta.preguntas', ['id' => $request->input('encuesta_id')])
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
        try {
            // Buscar la respuesta antes de eliminarla
            $pregunta = DetEncuestum::findOrFail($id);
            $encuestaId = $pregunta->encuesta_id; // Guardar el ID de la encuesta antes de eliminar

            // Eliminar la respuesta
            $pregunta->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pregunta eliminada correctamente.',
                'redirect_url' => route('encuesta.preguntas', $encuestaId) // Usar la variable almacenada
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
