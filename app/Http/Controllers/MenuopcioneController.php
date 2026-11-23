<?php

namespace App\Http\Controllers;

use App\Models\Menuopcione;
use App\Models\Tabla;
use App\Models\Modulo;
use App\Models\Tipoopcione;
use App\Http\Requests\MenuopcioneRequest;
use App\Models\Caja;
use App\Models\Operacione;
use App\Models\Perfile;
use App\Models\Permisousuario;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class MenuopcioneController
 * @package App\Http\Controllers
 */
class MenuopcioneController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $search   = $request->input('search');
        $perPage  = $request->input('perPage', 10);

        $query = Menuopcione::with(['modulo', 'tabla', 'tipoopcione']);

        // Obtener columnas de la tabla 'menuopciones'
        $columns = Schema::getColumnListing('menuopciones');

        if ($search) {
            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $column) {
                    $q->orWhereRaw("CAST($column AS TEXT) ILIKE ?", ["%{$search}%"]);
                }
            });
        }

        $menuopciones = $query->orderBy('orden', 'asc')->paginate($perPage);

        $operaciones = Operacione::where('tabla_id', 2)
            ->where('estado', true)
            ->get();

        $tabla = Tabla::find(2);

        $minOrden = Menuopcione::where('orden', '>', 0)->min('orden');
        $maxOrden = Menuopcione::max('orden');

        return view('menuopcione.index', compact(
            'menuopciones',
            'operaciones',
            'minOrden',
            'maxOrden',
            'tabla'
        ))->with('i', (request()->input('page', 1) - 1) * $menuopciones->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $menuopcione = new Menuopcione();
        $tablas = Tabla::all();
        $modulos = Modulo::all();
        $tipoopciones = Tipoopcione::all();

        return view('menuopcione.create', compact('menuopcione', 'tablas', 'modulos', 'tipoopciones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MenuopcioneRequest $request)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Obtener el último valor de 'orden' y sumar 1 para el nuevo registro
            $ultimoOrden = Menuopcione::max('orden');
            $nuevoOrden = $ultimoOrden + 1;

            // Determinar el valor de 'acceso_rapido'
            $accesoRapido = $request->input('acceso_rapido') === 'S' ? 'S' : 'N';

            // Crear el menú opción con los datos validados, el nuevo valor de 'orden', y 'acceso_rapido'
            $menuopcione = Menuopcione::create(array_merge(
                $request->validated(),
                [
                    'orden' => $nuevoOrden,
                    'acceso_rapido' => $accesoRapido
                ]
            ));

            // Obtener todas las sucursales y perfiles
            $sucursales = Sucursal::where('situacion', 'A')->get();
            $perfiles = Perfile::all();

            // Recorrer todas las sucursales
            foreach ($sucursales as $sucursal) {
                // Recorrer todos los perfiles
                foreach ($perfiles as $perfil) {
                    // Verificar si ya existen permisos para este perfil y sucursal en Permisousuario
                    $existePermiso = Permisousuario::where('perfil_id', $perfil->id)
                        ->where('sucursal_id', $sucursal->id)
                        ->exists();

                    // Solo insertar si ya existe algún permiso para este perfil y sucursal
                    if ($existePermiso) {
                        // Insertar el registro en Permisousuario
                        Permisousuario::create([
                            'situacion' => true,  // Asumimos que la situación es 'A' (Activo)
                            'menuopcion_id' => $menuopcione->id,
                            'perfil_id' => $perfil->id,
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
                'message' => 'Menú opción creada exitosamente junto con los permisos de usuario.',
                'menuopcione' => $menuopcione,
                'redirect_url' => route('menuopciones.index', ['page' => $pagina_actual])
            ]);
        } catch (\Exception $e) {
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
        $menuopcione = Menuopcione::find($id);
        $tablas = Tabla::all();
        $modulos = Modulo::all();
        $tipoopciones = Tipoopcione::all();

        return view('menuopcione.edit', compact('menuopcione', 'tablas', 'modulos', 'tipoopciones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MenuopcioneRequest $request, Menuopcione $menuopcione)
    {
        try {
            // Verificar si el campo 'acceso_rapido' está en el request y es igual a 'S'
            $accesoRapido = $request->input('acceso_rapido') === 'S' ? 'S' : 'N';

            // Actualizar el menú opción con los datos validados y el valor de 'acceso_rapido'
            $menuopcione->update(array_merge(
                $request->validated(),
                ['acceso_rapido' => $accesoRapido]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Menú opción actualizada exitosamente.',
                'menuopcione' => $menuopcione,
                'redirect_url' => route('menuopciones.index')
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
            $menuopcione = Menuopcione::find($id);
            $menuopcione->update(['estado' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Menú opción eliminada correctamente.',
                'redirect_url' => route('menuopciones.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move the order of the menu option.
     */
    public function moveOrder($id, $direction)
    {
        try {
            $menuopcione = Menuopcione::find($id);

            if (!$menuopcione) {
                return response()->json(['success' => false, 'message' => 'Menú opción no encontrada.']);
            }

            if ($direction === 'Subir') {
                $otherMenuopcion = Menuopcione::where('orden', '<', $menuopcione->orden)
                    ->orderBy('orden', 'desc')
                    ->first();

                if ($otherMenuopcion) {
                    $this->swapOrder($menuopcione, $otherMenuopcion);
                }
            } else if ($direction === 'Bajar') {
                $otherMenuopcion = Menuopcione::where('orden', '>', $menuopcione->orden)
                    ->orderBy('orden', 'asc')
                    ->first();

                if ($otherMenuopcion) {
                    $this->swapOrder($menuopcione, $otherMenuopcion);
                }
            }

            // Redireccionar a la vista con la URL para recargar dinámicamente
            return response()->json([
                'success' => true,
                'redirect_url' => route('menuopciones.index')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function swapOrder($menuopcion1, $menuopcion2)
    {
        $tempOrder = $menuopcion1->orden;
        $menuopcion1->orden = $menuopcion2->orden;
        $menuopcion2->orden = $tempOrder;

        $menuopcion1->save();
        $menuopcion2->save();
    }
    public function my_workshift()
    {
        $cajas = Caja::where('sucursal_id', Auth::user()->persona->sucursal_id)->get();


        return view('menuopcione.turnocajero', compact('cajas'));
    }
}
