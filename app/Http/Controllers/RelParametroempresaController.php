<?php

namespace App\Http\Controllers;

use App\Models\RelParametroempresa;
use App\Http\Requests\RelParametroempresaRequest;
use App\Models\Operacione;
use App\Models\Parametro;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class RelParametroempresaController
 * @package App\Http\Controllers
 */
class RelParametroempresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request, $empresa_id)
{
    $search = $request->input('search');
    $perPage = $request->input('perPage', 10);

    $query = RelParametroempresa::with('parametro')
        ->where('empresa_id', $empresa_id);

    if ($search) {
        $query->where(function ($q) use ($search) {
            // Buscar en el propio RelParametroempresa
            $q->where('valor', 'ILIKE', "%{$search}%");

            // Buscar en los campos relacionados de Parametro
            $q->orWhereHas('parametro', function ($sub) use ($search) {
                $sub->where('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('valor', 'ILIKE', "%{$search}%")
                    ->orWhere('tipo', 'ILIKE', "%{$search}%")
                    ->orWhere('obligatorio', 'ILIKE', "%{$search}%")
                    ->orWhere('estado', 'ILIKE', "%{$search}%");
            });
        });
    }

    $relParametroempresas = $query->paginate($perPage)->appends([
        'search' => $search,
        'perPage' => $perPage,
    ]);

    $operaciones = Operacione::where('tabla_id', 47)
        ->where('estado', true)
        ->get();

    return view('rel-parametroempresa.index', compact('relParametroempresas', 'operaciones', 'empresa_id', 'search', 'perPage'))
        ->with('i', (request()->input('page', 1) - 1) * $relParametroempresas->perPage());
}

    /**
     * Show the form for creating a new resource.
     */
    public function create($empresa_id)
    {
        $relParametroempresa = new RelParametroempresa();
    
        // Obtener los IDs de los parámetros ya relacionados con la empresa
        $parametrosRelacionados = RelParametroempresa::where('empresa_id', $empresa_id)
            ->pluck('parametro_id')
            ->toArray();
    
        // Filtrar los parámetros que no están relacionados con la empresa
        $parametros = Parametro::where('estado', true)
            ->where('tipo', 'EMPRESA')
            ->whereNotIn('id', $parametrosRelacionados)
            ->get();
    
        return view('rel-parametroempresa.create', compact('relParametroempresa', 'parametros', 'empresa_id'));
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(RelParametroempresaRequest $request)
    {
        DB::beginTransaction();

        try {
            $relParametroempresa = RelParametroempresa::create($request->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Relación de parámetro-empresa vinculada exitosamente.',
                'relParametroempresa' => $relParametroempresa,
                'redirect_url' => route('rel_parametroempresa.index', $relParametroempresa->empresa_id)
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
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $relParametroempresa = RelParametroempresa::find($id);
        $parametros = Parametro::where('estado', true)
        ->where('tipo', 'EMPRESA')
        ->get();
        $empresa_id = $relParametroempresa->empresa_id;

        return view('rel-parametroempresa.edit', compact('relParametroempresa', 'parametros', 'empresa_id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RelParametroempresaRequest $request, RelParametroempresa $relParametroempresa)
    {
        try {
            $relParametroempresa->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Relación de parámetro-empresa actualizada exitosamente.',
                'relParametroempresa' => $relParametroempresa,
                'redirect_url' => route('rel_parametroempresa.index', $relParametroempresa->empresa_id)
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
    public function destroy($id, $empresa_id)
    {
        try {
            RelParametroempresa::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Relación de parámetro-empresa eliminada correctamente.',
                'redirect_url' => route('rel_parametroempresa.index', $empresa_id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the list of parameters for the specified company.
     */
    public function verParametros($empresa_id)
    {
        $relParametroempresas = RelParametroempresa::where('empresa_id', $empresa_id)->paginate();
        $operaciones = Operacione::where('tabla_id', 36)->where('estado', true)->get();

        return view('rel-parametroempresa.index', compact('relParametroempresas', 'operaciones', 'empresa_id'))
             ->with('i', (request()->input('page', 1) - 1) * $relParametroempresas->perPage());
    }

    public function import($empresa_id)
    {
        $empresas = Empresa::all();
        return view('rel-parametroempresa.importar', compact('empresas', 'empresa_id'));
    }

    public function importar(Request $request)
    {
        // Validar las entradas
        $request->validate([
            'empresa_origen_id' => 'required|exists:empresas,id',
            'empresadestino_id' => 'required|exists:empresas,id',
        ]);
    
        $empresaOrigenId = $request->input('empresa_origen_id');
        $empresaDestinoId = $request->input('empresadestino_id');
    
        try {
            // Filtrar las relaciones de parámetros que pertenecen a la empresa de origen
            $parametrosOrigen = RelParametroempresa::where('empresa_id', $empresaOrigenId)->get();
    
            // Iniciar la transacción para asegurar la consistencia de los datos
            DB::beginTransaction();
    
            // Insertar cada relación en la empresa de destino
            foreach ($parametrosOrigen as $parametro) {
                RelParametroempresa::updateOrCreate(
                    [
                        'empresa_id' => $empresaDestinoId,
                        'parametro_id' => $parametro->parametro_id,
                    ],
                    [
                        'valor' => $parametro->valor,
                    ]
                );
            }
    
            // Confirmar la transacción
            DB::commit();
    
            // Respuesta de éxito
            return response()->json([
                'success' => true,
                'message' => 'Parámetros importados exitosamente.',
                'redirect_url' => route('rel_parametroempresa.index', $empresaDestinoId),
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();
    
            // Responder con el error
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
    

}
