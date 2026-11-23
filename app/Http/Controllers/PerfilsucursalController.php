<?php

namespace App\Http\Controllers;

use App\Models\Perfilsucursal;
use App\Http\Requests\PerfilsucursalRequest;
use App\Models\Menuopcione;
use App\Models\Operacione;
use App\Models\Perfil;
use App\Models\Perfile;
use App\Models\Permisousuario;
use App\Models\RelOperacionsucursalperfil;
use App\Models\RelSeccionsucursalperfil;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class PerfilsucursalController
 * @package App\Http\Controllers
 */
class PerfilsucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = Perfilsucursal::with(['perfile', 'sucursal']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                // Buscar por ID directos
                $q->where('perfil_id', 'ILIKE', "%{$search}%")
                    ->orWhere('sucursal_id', 'ILIKE', "%{$search}%");

                // Buscar en la relación Perfil
                $q->orWhereHas('perfile', function ($sub) use ($search) {
                    $sub->where('descripcion', 'ILIKE', "%{$search}%")
                        ->orWhere('abreviatura', 'ILIKE', "%{$search}%");
                });

                // Buscar en la relación Sucursal
                $q->orWhereHas('sucursal', function ($sub) use ($search) {
                    $sub->where('razonsocial', 'ILIKE', "%{$search}%")
                        ->orWhere('nombrecomercial', 'ILIKE', "%{$search}%");
                });
            });
        }

        $perfilsucursals = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 22) // Ajusta el ID si es distinto
            ->where('estado', true)
            ->get();

        return view('perfilsucursal.index', compact('perfilsucursals', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $perfilsucursals->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $perfilsucursal = new Perfilsucursal();
        return view('perfilsucursal.create', compact('perfilsucursal'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PerfilsucursalRequest $request)
    {
        try {
            $perfilsucursal = Perfilsucursal::create($request->validated());

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Perfilsucursal creado exitosamente.',
                'perfilsucursal' => $perfilsucursal,
                'redirect_url' => route('perfilsucursal.index', ['page' => $pagina_actual])
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
        $perfilsucursal = Perfilsucursal::find($id);
        return view('perfilsucursal.edit', compact('perfilsucursal'));
    }

    public function seccionsucursalperfiledit($id)
    {
        $relseccionsucursalperfil = RelSeccionsucursalperfil::find($id);
        $opciones = Menuopcione::where('estado', true)->get();

        return view('perfilsucursal.editseccion', compact('relseccionsucursalperfil', 'opciones'));
    }

    public function seccionsucursalperfilupdate(Request $request, $id)
    {
        // Validar los datos del formulario
        $request->validate([
            'opcion_id' => 'nullable|exists:menuopciones,id',
        ]);

        DB::beginTransaction();
        try {
            // Encontrar la instancia de RelSeccionsucursalperfil
            $relseccionsucursalperfil = RelSeccionsucursalperfil::findOrFail($id);

            // Actualizar el campo menuopcionesdefecto_id
            $relseccionsucursalperfil->menuopcionesdefecto_id = $request->opcion_id ? $request->opcion_id : null;

            // Guardar los cambios
            $relseccionsucursalperfil->save();

            // Obtener la instancia de Perfilsucursal
            $perfilsucursal = Perfilsucursal::where('perfil_id', $relseccionsucursalperfil->perfil_id)
                ->where('sucursal_id', $relseccionsucursalperfil->sucursal_id)
                ->first();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Secciones por perfil sucursal actualizado exitosamente.',
                'perfilsucursal' => $perfilsucursal,
                'redirect_url' => route('perfilsucursal.verSecciones', $perfilsucursal->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al actualizar la sección. Por favor, inténtalo de nuevo.']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PerfilsucursalRequest $request, Perfilsucursal $perfilsucursal)
    {
        try {
            $perfilsucursal->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Perfilsucursal actualizado exitosamente.',
                'perfilsucursal' => $perfilsucursal,
                'redirect_url' => route('perfilsucursal.index')
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
            Perfilsucursal::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Perfilsucursal eliminado correctamente.',
                'redirect_url' => route('perfilsucursal.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function verOperacionesPerfil(Request $request, $id)
    {
        $perfilsucursal = Perfilsucursal::find($id);

        $query = RelOperacionSucursalPerfil::where('perfil_id', $perfilsucursal->perfil_id)
            ->where('sucursal_id', $perfilsucursal->sucursal_id)
            ->with('operacione'); // Cargar relación operacione

        // Búsqueda extrema insensible a mayúsculas/minúsculas
        if ($request->has('search') && $request->search != '') {
            $search = trim(strtolower($request->search)); // Limpiar espacios y pasar a minúsculas
            $words = explode(' ', $search); // Separar por palabras

            $query->whereHas('operacione', function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($q2) use ($word) {
                        $q2->whereRaw('LOWER(tipo) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(descripcion) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(comentario) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(accion) LIKE ?', ["%{$word}%"]);
                    });
                }
            });
        }

        // Determinar cuántos elementos por página (por defecto 10)
        $perPage = $request->input('perPage', 10);

        $operacionesRelacionadas = $query->orderBy('id')->paginate($perPage);

        // Obtener todas las operaciones activas de la tabla específica
        $operaciones = Operacione::where('tabla_id', 46)
            ->where('estado', true)
            ->get();

        $sucursal_id = $perfilsucursal->sucursal_id;

        return view('rel-operacionsucursalperfil.index', compact(
            'operacionesRelacionadas',
            'operaciones',
            'id',
            'sucursal_id'
        ))->with('i', (request()->input('page', 1) - 1) * $operacionesRelacionadas->perPage());
    }


    public function verSeccionesSucursalPerfil(Request $request, $id)
    {
        $perfilsucursal = Perfilsucursal::findOrFail($id);

        // Operaciones activas de la tabla específica
        $operaciones = Operacione::where('tabla_id', 48)
            ->where('estado', true)
            ->get();

        // Query principal con relaciones
        $query = RelSeccionsucursalperfil::where('perfil_id', $perfilsucursal->perfil_id)
            ->where('sucursal_id', $perfilsucursal->sucursal_id)
            ->with('seccione');

        // 🔹 Búsqueda avanzada por sección
        if ($request->filled('search')) {
            $search = trim(mb_strtolower($request->search));
            $words = explode(' ', $search);

            $query->whereHas('seccione', function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($q2) use ($word) {
                        $q2->whereRaw('LOWER(descripcion) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(abreviatura) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(comentario) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(subtitulo) LIKE ?', ["%{$word}%"]);
                    });
                }
            });
        }

        // 🔹 Paginación con perPage dinámico
        $perPage = $request->input('perPage', 10);
        $relseccionsucursalperfiles = $query->orderBy('id')
            ->paginate($perPage)
            ->appends([
                'search' => $request->input('search'),
                'perPage' => $perPage
            ]);

        return view('perfilsucursal.seccion', compact(
            'relseccionsucursalperfiles',
            'operaciones',
            'id',
            'perfilsucursal'
        ))->with('i', (request()->input('page', 1) - 1) * $relseccionsucursalperfiles->perPage());
    }


    public function verPermisoUsuario(Request $request, $id)
    {
        $perfilsucursal = Perfilsucursal::findOrFail($id);

        // Operaciones activas de la tabla específica
        $operaciones = Operacione::where('tabla_id', 49)
            ->where('estado', true)
            ->get();

        // Query principal
        $query = Permisousuario::where('perfil_id', $perfilsucursal->perfil_id)
            ->where('sucursal_id', $perfilsucursal->sucursal_id)
            ->with(['menuopcione.modulo', 'menuopcione.tabla', 'menuopcione.tipoopcione']);

        // 🔹 Búsqueda avanzada case-insensitive
        if ($request->filled('search')) {
            $search = trim(mb_strtolower($request->search));
            $words = explode(' ', $search);

            $query->whereHas('menuopcione', function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($q2) use ($word) {
                        // Campos de Menuopcione
                        $q2->whereRaw('LOWER(descripcion) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(accion) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(acceso_rapido) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(condiciones_ar::text) LIKE ?', ["%{$word}%"]);

                        // Campos de Modulo
                        $q2->orWhereHas('modulo', function ($qm) use ($word) {
                            $qm->whereRaw('LOWER(descripcion) LIKE ?', ["%{$word}%"])
                                ->orWhereRaw('LOWER(abreviatura) LIKE ?', ["%{$word}%"]);
                        });

                        // Campos de Tabla
                        $q2->orWhereHas('tabla', function ($qt) use ($word) {
                            $qt->whereRaw('LOWER(descripcion) LIKE ?', ["%{$word}%"])
                                ->orWhereRaw('LOWER(comentario) LIKE ?', ["%{$word}%"])
                                ->orWhereRaw('LOWER(tipo) LIKE ?', ["%{$word}%"]);
                        });

                        // Campos de Tipoopcione
                        $q2->orWhereHas('tipoopcione', function ($tt) use ($word) {
                            $tt->whereRaw('LOWER(descripcion) LIKE ?', ["%{$word}%"])
                                ->orWhereRaw('LOWER(abreviatura) LIKE ?', ["%{$word}%"]);
                        });
                    });
                }
            });
        }

        // Paginación
        $perPage = $request->input('perPage', 10);
        $permisos = $query->orderBy('id')->paginate($perPage)->appends([
            'search' => $request->input('search'),
            'perPage' => $perPage
        ]);

        $sucursal_id = $perfilsucursal->sucursal_id;

        return view('perfilsucursal.permisousuario', compact(
            'permisos',
            'operaciones',
            'id',
            'sucursal_id'
        ))->with('i', (request()->input('page', 1) - 1) * $permisos->perPage());
    }





    public function activar($id)
    {
        try {
            $relacion = RelOperacionSucursalPerfil::findOrFail($id);
            $perfilsucursal = Perfilsucursal::where('perfil_id', $relacion->perfil_id)
                ->where('sucursal_id', $relacion->sucursal_id)
                ->first();
            $relacion->update(['situacion' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Perfil de sucursal activada exitosamente.',
                'encuesta' => $perfilsucursal,
                'redirect_url' => route('verOperacionesPerfil', $perfilsucursal->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar la relación especificada.
     */
    public function desactivar($id)
    {
        try {
            $relacion = RelOperacionSucursalPerfil::findOrFail($id);
            $perfilsucursal = Perfilsucursal::where('perfil_id', $relacion->perfil_id)
                ->where('sucursal_id', $relacion->sucursal_id)
                ->first();
            $relacion->update(['situacion' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Perfil de sucursal desactivada exitosamente.',
                'encuesta' => $perfilsucursal,
                'redirect_url' => route('verOperacionesPerfil', $perfilsucursal->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function activarPermiso($id)
    {
        try {
            $relacion = Permisousuario::findOrFail($id);
            $perfilsucursal = Perfilsucursal::where('perfil_id', $relacion->perfil_id)
                ->where('sucursal_id', $relacion->sucursal_id)
                ->first();
            $relacion->update(['situacion' => true]);


            return response()->json([
                'success' => true,
                'message' => 'Permiso activado exitosamente.',
                'perfil' => $perfilsucursal,
                'redirect_url' => route('verPermisoUsuario', $perfilsucursal->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar la relación especificada.
     */
    public function desactivarPermiso($id)
    {
        try {
            $relacion = Permisousuario::findOrFail($id);
            $perfilsucursal = Perfilsucursal::where('perfil_id', $relacion->perfil_id)
                ->where('sucursal_id', $relacion->sucursal_id)
                ->first();
            $relacion->update(['situacion' => false]);


            return response()->json([
                'success' => true,
                'message' => 'Permiso desactivado exitosamente.',
                'perfil' => $perfilsucursal,
                'redirect_url' => route('verPermisoUsuario', $perfilsucursal->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function import($id)
    {


        $sucursal_id =  Perfilsucursal::find($id)->sucursal_id;
        $perfiles = Perfilsucursal::where('sucursal_id', $sucursal_id)
            ->where('id', '!=', $id)
            ->get();
        //dd($perfiles);
        return view('perfilsucursal.importar', compact('perfiles', 'id'));
    }
    public function importar(Request $request)
    {
        // Validar la entrada
        $request->validate([
            'perfil_origen_id' => 'required|exists:perfilsucursal,id',
            'perfildestino_id' => 'required|exists:perfilsucursal,id',
        ]);

        $perfilOrigenId = $request->input('perfil_origen_id');
        $perfilDestinoId = $request->input('perfildestino_id');


        $perfilorigen =  Perfilsucursal::find($perfilOrigenId);
        $perfildestino =  Perfilsucursal::find($perfilDestinoId);

        try {
            // Obtener los permisos del perfil de origen
            $permisosOrigen = Permisousuario::where('perfil_id', $perfilorigen->perfil_id)->where('sucursal_id', $perfilorigen->sucursal_id)->get();

            // Iniciar la transacción
            DB::beginTransaction();

            // Sobrescribir los permisos en el perfil de destino
            foreach ($permisosOrigen as $permiso) {
                Permisousuario::updateOrCreate(
                    [
                        'perfil_id' => $perfildestino->perfil_id,
                        'sucursal_id' => $permiso->sucursal_id,
                        'menuopcion_id' => $permiso->menuopcion_id,
                    ],
                    [
                        'situacion' => $permiso->situacion,
                        'updated_at' => now(),
                    ]
                );
            }

            // Confirmar la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permisos importados exitosamente.',
                'redirect_url' => route('verPermisoUsuario', $perfilDestinoId),
            ]);
        } catch (\Exception $e) {
            // Revertir en caso de error
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
