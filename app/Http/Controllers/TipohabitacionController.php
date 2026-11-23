<?php

namespace App\Http\Controllers;

use App\Models\Tipohabitacion;
use App\Http\Requests\TipohabitacionRequest;
use App\Models\Operacione;
use App\Models\Persona;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Amenity;
use App\Models\Habitacion;

/**
 * Class TipohabitacionController
 * @package App\Http\Controllers
 */
class TipohabitacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $perPage = $request->input('perPage', 10);

        $query = Tipohabitacion::where('sucursal_id', $persona->sucursal_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('estado', 'ILIKE', "%{$search}%")
                    ->orWhereRaw('CAST(id AS TEXT) ILIKE ?', ["%{$search}%"])
                    ->orWhereRaw('CAST(capacidad AS TEXT) ILIKE ?', ["%{$search}%"])
                    ->orWhereRaw('CAST(precio AS TEXT) ILIKE ?', ["%{$search}%"]);
            });
        }

        $tipohabitacions = $query->orderBy('nombre', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        $operaciones = Operacione::where('tabla_id', 20)->where('estado', true)->get();

        return view('tipohabitacion.index', compact('tipohabitacions', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $tipohabitacions->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipohabitacion = new Tipohabitacion();
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        return view('tipohabitacion.create', compact('tipohabitacion', 'sucursal_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipohabitacionRequest $request)
    {
        try {
            // Validar y obtener los datos del request
            $data = $request->validated();

            // Decodificar y convertir el JSON de precios por hora si estÃ¡ presente
            if ($request->has('json_precio_horas')) {
                // Convertir a string JSON antes de guardar
                $data['json_precio_horas'] = json_encode(json_decode($request->json_precio_horas, true));
            }

            // Crear el tipo de habitaciÃ³n con los datos procesados
            $tipohabitacion = Tipohabitacion::create($data);

            // Crear las amenidades asociadas, si las hay
            if ($request->has('amenities')) {
                foreach ($request->input('amenities') as $amenityData) {
                    Amenity::create([
                        'tipohabitacion_id' => $tipohabitacion->id,
                        'nombre' => $amenityData['nombre'],
                        'icon' => $amenityData['icon'] ?? null,
                    ]);
                }
            }

            // Obtener la pÃ¡gina actual para redirigir despuÃ©s de guardar
            $pagina_actual = $request->input('pagina_actual', 1);
    
            return response()->json([
                'success' => true,
                'message' => 'Tipo de habitaciÃ³n creado exitosamente.',
                'tipohabitacion' => $tipohabitacion,
                'redirect_url' => route('tipohabitacion.index', ['page' => $pagina_actual])
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
        $tipohabitacion = Tipohabitacion::find($id);
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        return view('tipohabitacion.edit', compact('tipohabitacion', 'sucursal_id'));
    }

    /**
     * Update the specified resource in storage.
     */public function update(TipohabitacionRequest $request, Tipohabitacion $tipohabitacion)
{
    try {
        $data = $request->validated();

        // Permite json_precio_horas nulo o vacío sin romper.
        if ($request->has('json_precio_horas')) {
            $jsonPrecioHoras = trim((string) $request->input('json_precio_horas'));
            if ($jsonPrecioHoras === '' || strtolower($jsonPrecioHoras) === 'null') {
                $data['json_precio_horas'] = '[]';
            } else {
                $decoded = json_decode($jsonPrecioHoras, true);
                $data['json_precio_horas'] = is_array($decoded) ? json_encode($decoded) : '[]';
            }
        }

        $tipohabitacion->update($data);

        // Amenities opcionales: si no llegan, tratarlos como array vacío.
        $amenitiesInput = $request->input('amenities', []);
        if (!is_array($amenitiesInput)) {
            $amenitiesInput = [];
        }

        $formAmenityIds = array_keys($amenitiesInput);
        $existingAmenityIds = $tipohabitacion->amenities()->pluck('id')->toArray();
        $amenitiesToDelete = array_diff($existingAmenityIds, $formAmenityIds);
        Amenity::whereIn('id', $amenitiesToDelete)->delete();

        foreach ($amenitiesInput as $id => $amenityData) {
            $nombre = $amenityData['nombre'] ?? null;
            if (empty($nombre)) {
                continue;
            }

            $amenity = Amenity::find($id);
            if ($amenity) {
                $amenity->update([
                    'nombre' => $nombre,
                    'icon' => $amenityData['icon'] ?? null,
                ]);
            } else {
                Amenity::create([
                    'tipohabitacion_id' => $tipohabitacion->id,
                    'nombre' => $nombre,
                    'icon' => $amenityData['icon'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tipo de habitación actualizado exitosamente.',
            'tipohabitacion' => $tipohabitacion,
            'redirect_url' => route('tipohabitacion.index')
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
            // Eliminar la habitaciÃ³n y sus amenidades
            $tipohabitacion = Tipohabitacion::find($id);
            $tipohabitacion->amenities()->delete();
            $tipohabitacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de habitaciÃ³n eliminado correctamente.',
                'redirect_url' => route('tipohabitacion.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
public function imagenes(Request $request, $id)
{
    try {
        // Obtener el tipo de habitaciÃ³n
        $tipohabitacion = Tipohabitacion::findOrFail($id);

        // Obtener las imÃ¡genes relacionadas con el tipo de habitaciÃ³n
        $query = $tipohabitacion->imagenes();

        // Obtener el tÃ©rmino de bÃºsqueda si existe
        $search = $request->input('search'); 
        
        // Si hay un tÃ©rmino de bÃºsqueda, agregarlo a la consulta
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('descripcion', 'LIKE', '%' . $search . '%')
                  ->orWhere('url', 'LIKE', '%' . $search . '%');
            });
        }

        // PaginaciÃ³n (por defecto 10 imÃ¡genes por pÃ¡gina)
        $imagenes = $query->paginate(10);

        return view('tipohabitacion.imagenes', compact('tipohabitacion', 'imagenes'));
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->getMessage()
        ], 500);
    }
}



}
