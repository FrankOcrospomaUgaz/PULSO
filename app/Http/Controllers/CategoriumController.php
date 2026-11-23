<?php

namespace App\Http\Controllers;

use App\Models\Categorium;
use App\Http\Requests\CategoriumRequest;
use App\Models\Operacione;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\RelCategoriasucursal;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class CategoriaController
 * @package App\Http\Controllers
 */
class CategoriumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $operaciones = Operacione::where('tabla_id', 57)
            ->where('estado', true)
            ->get();
        $perPage = $request->input('perPage', 10);
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);

        // Query base: categorías ligadas a la sucursal
        $query = Categorium::whereHas('relCategoriasucursal', function ($q) use ($sucursal) {
            $q->where('sucursal_id', $sucursal->id);
        });

        // Si hay búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('abreviatura', 'ILIKE', "%{$search}%")
                    ->orWhere('nivel', 'ILIKE', "%{$search}%")
                    ->orWhere('orden', '::text ILIKE', "%{$search}%");
            });
        }

        // Ordenar por ID y paginar
        $categorias = $query->orderBy('id', 'asc')->paginate($perPage);

        return view('categoria.index', compact('categorias', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $categorias->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        $categoria = new Categorium();

        // Obtener el usuario autenticado
        $user = Auth::user();

        // Acceder a persona asociada
        $persona = Persona::find($user->persona_id);

        // Acceder a la sucursal de la persona
        $sucursal = Sucursal::find($persona->sucursal_id);

        // Obtener el empresa_id de la sucursal
        $empresa_id = $sucursal->empresa_id;
        $sucursal_id = $sucursal->id;
        $categorias = Categorium::whereNull('categoria_id')
            ->whereHas('relCategoriasucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->with('categorias')
            ->get();

        // Pasar la lista de empresas (si es necesario) y el empresa_id actual
        return view('categoria.create', compact('categoria', 'categorias', 'empresa_id', 'sucursal_id'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoriumRequest $request)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Insertar en la tabla 'categoria'
            $categoria = Categorium::create($request->validated());

            // Insertar en la tabla 'rel_categoriasucursal'
            $relCategoriaSucursal = new RelCategoriasucursal();
            $relCategoriaSucursal->categoria_id = $categoria->id;
            $relCategoriaSucursal->sucursal_id = $request->input('sucursal_id');
            $relCategoriaSucursal->tipocarta = $request->input('tipocarta', 'RECEPCIÓN');
            $relCategoriaSucursal->situacion = $request->input('situacion', 'E');
            $relCategoriaSucursal->save();

            // Confirmar la transacción
            DB::commit();

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Categoría creada exitosamente.',
                'categoria' => $categoria,
                'redirect_url' => route('categoria.index', ['page' => $pagina_actual])
            ]);
        } catch (\Exception $e) {
            // Si ocurre un error, revertir la transacción
            DB::rollBack();

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
        $categoria = Categorium::with('relCategoriasucursal')->find($id);

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $sucursal_id = $sucursal->id;
        $empresa_id = $sucursal->empresa_id;

        $categorias = Categorium::whereNull('categoria_id')
            ->whereHas('relCategoriasucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->with('categorias')
            ->get();

        // Obtener el tipocarta actual o asignar null si no existe relación
        $tipocarta_actual = $categoria->relCategoriasucursal?->tipocarta ?? null;

        return view('categoria.edit', compact('categoria', 'categorias', 'empresa_id', 'sucursal_id', 'tipocarta_actual'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $categoria = Categorium::find($id);
            // Actualizar campos de Categorium manualmente
            $categoria->descripcion = $request->input('descripcion');
            $categoria->abreviatura = $request->input('abreviatura');
            $categoria->categoria_id = $request->input('categoria_id');
            $categoria->empresa_id = $request->input('empresa_id');

            // Para la imagen
            if ($request->hasFile('imagen')) {
                $file = $request->file('imagen');
                $path = $file->store('categoria_images', 'public');
                $categoria->imagen = $path;
            }

            $categoria->save();

            // Buscar o crear la relación relCategoriasucursal
            $relacion = \App\Models\RelCategoriasucursal::firstOrNew(['categoria_id' => $categoria->id]);


            // Actualizar tipocarta
            $relacion->tipocarta = $request->input('tipocarta');

            // Asignar sucursal_id si es nuevo registro
            if (!$relacion->exists) {
                $relacion->sucursal_id = $request->input('sucursal_id');
            }

            $relacion->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Categoría y tipo de carta actualizados exitosamente.',
                'categoria' => $categoria,
                'redirect_url' => route('categoria.index')
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            Categorium::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoría eliminada correctamente.',
                'redirect_url' => route('categoria.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
