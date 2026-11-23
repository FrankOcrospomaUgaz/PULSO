<?php

namespace App\Http\Controllers;

use App\Models\Parametro;
use App\Models\Categoriaparametro;
use App\Models\Tabla;
use App\Http\Requests\ParametroRequest;
use App\Models\Operacione;
use App\Models\RelParametrosucursal;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class ParametroController
 * @package App\Http\Controllers
 */
class ParametroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = Parametro::with('categoriaparametro', 'tabla');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('valor', 'ILIKE', "%{$search}%")
                    ->orWhere('tipo', 'ILIKE', "%{$search}%")
                    ->orWhere('obligatorio', 'ILIKE', "%{$search}%")
                    ->orWhereHas('categoriaparametro', function ($rel) use ($search) {
                        $rel->where('descripcion', 'ILIKE', "%{$search}%");
                    })
                    ->orWhereHas('tabla', function ($rel) use ($search) {
                        $rel->where('descripcion', 'ILIKE', "%{$search}%")
                            ->orWhere('comentario', 'ILIKE', "%{$search}%");
                    });
            });
        }

        $parametros = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 9)->where('estado', true)->get();

        return view('parametro.index', compact('parametros', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $parametros->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parametro = new Parametro();
        $categoriaparametros = CategoriaParametro::all();
        $tablas = Tabla::all();

        return view('parametro.create', compact('parametro', 'categoriaparametros', 'tablas'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(ParametroRequest $request)
    {
        try {
            DB::beginTransaction(); // Iniciar la transacción

            // Crear el parámetro
            $parametro = Parametro::create($request->validated());

            // Obtener todas las sucursales existentes
            $sucursales = Sucursal::all();

            // Insertar en rel_parametrosucursal para cada sucursal
            foreach ($sucursales as $sucursal) {
                RelParametrosucursal::create([
                    'valor' => $request->valor,
                    'parametro_id' => $parametro->id,
                    'sucursal_id' => $sucursal->id,
                ]);
            }

            DB::commit(); // Confirmar la transacción

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Parametro creado exitosamente.',
                'parametro' => $parametro,
                'redirect_url' => route('parametros.index', ['page' => $pagina_actual])
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción en caso de error

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
        $parametro = Parametro::find($id);
        $categoriaparametros = Categoriaparametro::all();
        $tablas = Tabla::all();

        return view('parametro.edit', compact('parametro', 'categoriaparametros', 'tablas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ParametroRequest $request, Parametro $parametro)
    {
        try {
            $parametro->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Parametro actualizado exitosamente.',
                'parametro' => $parametro,
                'redirect_url' => route('parametros.index')
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
            Parametro::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parametro eliminado correctamente.',
                'redirect_url' => route('parametros.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
