<?php

namespace App\Http\Controllers;

use App\Models\Seccione;
use App\Http\Requests\SeccioneRequest;
use App\Models\Menuopcione;
use App\Models\Operacione;
use App\Models\Perfile;
use App\Models\RelSeccionopcionmenu;
use App\Models\RelSeccionsucursal;
use App\Models\RelSeccionsucursalperfil;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class SeccioneController
 * @package App\Http\Controllers
 */
class SeccioneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = Seccione::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('abreviatura', 'ILIKE', "%{$search}%")
                    ->orWhere('orden', 'ILIKE', "%{$search}%")
                    ->orWhere('subtitulo', 'ILIKE', "%{$search}%")
                    ->orWhere('comentario', 'ILIKE', "%{$search}%");
            });
        }

        $secciones = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 17)
            ->where('estado', true)
            ->get();

        return view('seccione.index', compact('secciones', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $secciones->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $seccione = new Seccione();
        $menuOpciones = Menuopcione::all();

        // Formatear las opciones de menú para el autocomplete
        return view('seccione.create', compact('seccione'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SeccioneRequest $request)
    {
        try {
            DB::beginTransaction(); // Inicia la transacción

            // Crear la sección con los datos validados
            $seccione = Seccione::create($request->validated());

            // Obtener todas las sucursales
            $sucursales = Sucursal::all();

            // Crear la relación con todas las sucursales (situacion = 'A')
            foreach ($sucursales as $sucursal) {
                RelSeccionsucursal::create([
                    'situacion' => 'A',
                    'seccion_id' => $seccione->id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // Obtener todos los perfiles
            $perfiles = Perfile::all();

            // Crear la relación con todos los perfiles, dejando menuopcionesdefecto_id como null
            foreach ($sucursales as $sucursal) {
                foreach ($perfiles as $perfil) {
                    RelSeccionsucursalperfil::create([
                        'perfil_id' => $perfil->id,
                        'sucursal_id' => $sucursal->id,
                        'seccion_id' => $seccione->id,
                        'menuopcionesdefecto_id' => null
                    ]);
                }
            }

            // 📌 REGISTRAR OPCIONES DE MENÚ ASOCIADAS
            $opcionesMenuArray = $this->normalizarOpcionesMenu($request);

            if (!empty($opcionesMenuArray)) {
                foreach ($opcionesMenuArray as $opcionmenuId) {
                    RelSeccionopcionmenu::create([
                        'seccion_id' => $seccione->id,
                        'opcionmenu_id' => $opcionmenuId
                    ]);
                }
            }

            DB::commit(); // Confirma la transacción

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Sección creada exitosamente.',
                'seccion' => $seccione,
                'redirect_url' => route('secciones.index', ['page' => $pagina_actual])
            ]);
        } catch (\Exception $e) {
            DB::rollback(); // Revertir cambios en caso de error

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
        $seccione = Seccione::find($id);
        $menuOpciones = Menuopcione::all();
        $RelSeccionopcionmenu = RelSeccionopcionmenu::where('seccion_id', $id)->get();
        // Formatear las opciones de menú para el autocomplete
        return view('seccione.edit', compact('seccione', 'menuOpciones', 'RelSeccionopcionmenu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SeccioneRequest $request, Seccione $seccione)
    {
        try {
            // Actualizar la sección
            $seccione->update($request->validated());

            // Obtener los IDs de opciones de menú enviados desde el formulario
            $opcionesMenuIds = $this->normalizarOpcionesMenu($request);

            // Obtener las opciones de menú actuales en la BD para la sección
            $opcionesActuales = RelSeccionopcionmenu::where('seccion_id', $seccione->id)->pluck('opcionmenu_id')->toArray();

            // Identificar los nuevos elementos a agregar
            $opcionesAgregar = array_diff($opcionesMenuIds, $opcionesActuales);

            // Identificar los elementos a eliminar
            $opcionesEliminar = array_diff($opcionesActuales, $opcionesMenuIds);

            // Insertar nuevas relaciones
            foreach ($opcionesAgregar as $opcionId) {
                RelSeccionopcionmenu::create([
                    'seccion_id' => $seccione->id,
                    'opcionmenu_id' => $opcionId,
                ]);
            }

            // Eliminar relaciones que ya no están en la lista
            RelSeccionopcionmenu::where('seccion_id', $seccione->id)
                ->whereIn('opcionmenu_id', $opcionesEliminar)
                ->delete();

            // Actualizar las sucursales (se mantiene igual)
            $sucursales = \App\Models\Sucursal::all();
            foreach ($sucursales as $sucursal) {
                RelSeccionsucursal::updateOrCreate(
                    [
                        'seccion_id' => $seccione->id,
                        'sucursal_id' => $sucursal->id,
                    ],
                    [
                        'situacion' => $request->get('situacion', 'A'),
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Sección actualizada exitosamente.',
                'seccion' => $seccione,
                'redirect_url' => route('secciones.index')
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
            $seccione = Seccione::find($id);
            $seccione->update(['situacion' => 'A']);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Sección activada exitosamente.',
                'encuesta' => $seccione,
                'redirect_url' => route('secciones.index')
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
            $seccione = Seccione::find($id);
            $seccione->update(['situacion' => 'D']);

            return response()->json([
                'success' => true,
                'message' => 'Sección desactivada exitosamente.',
                'encuesta' => $seccione,
                'redirect_url' => route('secciones.index')
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
            Seccione::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sección eliminada correctamente.',
                'redirect_url' => route('secciones.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    private function normalizarOpcionesMenu(Request $request): array
    {
        $opciones = json_decode($request->input('opciones_menu_json', '[]'), true);

        if (!is_array($opciones)) {
            return [];
        }

        $ids = collect($opciones)
            ->filter(fn($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return Menuopcione::whereIn('id', $ids)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();
    }
}
