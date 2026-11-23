<?php

namespace App\Http\Controllers;

use App\Models\Salon;
use App\Http\Requests\SalonRequest;
use App\Models\Operacione;
use App\Models\SalonPackage;
use App\Models\SalonService;
use App\Models\Sucursal;
use App\Support\LandingAssetImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalonController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search'); // Obtener el término de búsqueda
        $query = Salon::where('sucursal_id', Auth::user()->persona->sucursal_id); // Iniciar la consulta base
        $perPage = $request->input('perPage', 10);
        // Obtener las columnas de la tabla 'salon'
        $columns = Schema::getColumnListing('salon');

        // Filtrar solo las columnas de tipo texto
        $textColumns = [];
        foreach ($columns as $column) {
            // Obtener el tipo de la columna desde 'information_schema.columns'
            $columnType = DB::select("
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = 'salon' AND column_name = ?
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

        $salones = $query->orderBy('titulo', 'asc')->paginate($perPage); // Paginación y ordenación

        // Obtener las operaciones para la vista (tabla_id 82)
        $operaciones = Operacione::where('tabla_id', 82)->where('estado', true)->get();

        // Definir las situaciones
        $situaciones = ['Activo', 'Desactivado'];

        return view('salon.index', compact('salones', 'operaciones', 'situaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $salones->perPage());
    }


    public function create()
    {
        $salon = new Salon();
        $sucursal_id = Auth::user()->persona->sucursal_id;
        return view('salon.create', compact('salon', 'sucursal_id'));
    }

public function store(Request $request)
{
    DB::beginTransaction();

    try {
        $validator = Validator::make($request->all(), [
            'sucursal_id'        => 'required|integer|exists:sucursal,id',
            'titulo'             => 'required|string|max:255',
            'descripcion_corta'  => 'nullable|string|max:255',
            'descripcion_larga'  => 'nullable|string|max:1000',
            'situacion'          => 'nullable|in:A,D',
            'imagen'             => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Procesar imagen si se sube
        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
            $image = $request->file('imagen');
            $data['imagen'] = LandingAssetImage::store($image);
        }

        $data['situacion'] = $data['situacion'] ?? 'A'; // Por defecto 'A'

        Salon::create($data);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Salón creado exitosamente.',
            'redirect_url' => route('salon.index', ['page' => $request->input('pagina_actual', 1)])
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'errors' => $e->getMessage()
        ], 500);
    }
}


    public function show($id)
    {
        $salon = Salon::find($id);
        return view('salon.show', compact('salon'));
    }

    public function edit($id)
    {
        $salon = Salon::find($id);
        $sucursal_id = Auth::user()->persona->sucursal_id;
        return view('salon.edit', compact('salon', 'sucursal_id'));
    }
public function update(Request $request, Salon $salon)
{
    DB::beginTransaction();

    try {
        $validator = Validator::make($request->all(), [
            'sucursal_id'        => 'required|integer|exists:sucursal,id',
            'titulo'             => 'required|string|max:255',
            'descripcion_corta'  => 'nullable|string|max:255',
            'descripcion_larga'  => 'nullable|string|max:1000',
            'situacion'          => 'nullable|in:A,D',
            'imagen'             => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Procesar imagen si se sube
        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
            // Eliminar la imagen anterior si existe
            if (!empty($request->imagen_antigua) && file_exists(public_path($request->imagen_antigua))) {
                unlink(public_path($request->imagen_antigua));
            }

            $image = $request->file('imagen');
            $data['imagen'] = LandingAssetImage::store($image);
        } else {
            // Mantener imagen antigua si no se sube una nueva
            $data['imagen'] = $request->imagen_antigua ?? $salon->imagen;
        }

        $salon->update($data);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Salón actualizado exitosamente.',
            'redirect_url' => route('salon.index', ['page' => $request->input('pagina_actual', 1)])
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
            Salon::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Salón eliminado correctamente.',
                'redirect_url' => route('salon.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function servicios($id)
    {
        try {
            // Obtener el salón
            $salon = Salon::findOrFail($id);
            //dd($salon);
            // Obtener los servicios relacionados con el salón
            $servicios = SalonService::where('salon_id', $salon->id)->get();
                    // Obtener las operaciones para la vista (tabla_id 82)
        $operaciones = Operacione::where('tabla_id', 84)->where('estado', true)->get();

            return view('salon-service.index', compact('salon', 'servicios', 'operaciones'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    public function paquetes($id)
    {
        try {
            // Obtener el salón
            $salon = Salon::findOrFail($id);

            // Obtener los paquetes relacionados con el salón
            $paquetes = SalonPackage::where('salon_id', $salon->id)->get();
        $operaciones = Operacione::where('tabla_id', 83)->where('estado', true)->get();

            return view('salon-package.index', compact('salon', 'paquetes', 'operaciones'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
