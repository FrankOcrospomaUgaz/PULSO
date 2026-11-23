<?php

namespace App\Http\Controllers;

use App\Models\RelParametrosucursal;
use App\Http\Requests\RelParametrosucursalRequest;
use App\Models\Igv;
use App\Models\Operacione;
use App\Models\Parametro;
use App\Models\Persona;
use App\Models\Sucursal;
use App\Models\Tipodocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class RelParametrosucursalController
 * @package App\Http\Controllers
 */
class RelParametrosucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index(Request $request)
    {
        $sucursal_id = $request->sucursal_id;
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = RelParametrosucursal::with('parametro')
            ->where('sucursal_id', $sucursal_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                // Buscar en el propio RelParametrosucursal
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

        $relParametrosucursals = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 36)
            ->where('estado', true)
            ->get();

        return view('rel-parametrosucursal.index', compact('relParametrosucursals', 'operaciones', 'sucursal_id', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $relParametrosucursals->perPage());
    }

    public function setup()
    {
        try {
            // Obtener la persona asociada al usuario autenticado
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal_id = $persona->sucursal_id;

            // Obtener los parámetros asociados a la sucursal del usuario
            $parametros_sucursal = RelParametrosucursal::where('sucursal_id', $sucursal_id)
                ->with('parametro.categoriaparametro')
                ->orderBy('id')
                ->get();

            // Organizar los parámetros por categoría
            $parametros = [];
            foreach ($parametros_sucursal as $relParametro) {
                $categoria = $relParametro->parametro->categoriaparametro->descripcion;
                $parametros[$categoria][] = (object) [
                    'parametro_id' => $relParametro->parametro->id,
                    'descripcion' => $relParametro->parametro->descripcion,
                    'valor' => $relParametro->valor,
                ];
            }

            $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 2)
                ->get();
            $igvs = Igv::all();

            // dd($parametros);
            // Retornar la vista con las variables esperadas por el Blade
            return view('rel-parametrosucursal.configuraciónSistema', [
                'clase' => 'ParametrosSucursal',
                'parametros' => $parametros,
                'tipodocumentos' => $tiposDocumento,
                'igvs' => $igvs,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ocurrió un error al cargar los parámetros: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($sucursal_id)
    {
        $relParametrosucursal = new RelParametrosucursal();

        // Obtener los IDs de los parámetros ya relacionados con la sucursal
        $parametrosRelacionados = RelParametrosucursal::where('sucursal_id', $sucursal_id)
            ->pluck('parametro_id')
            ->toArray();

        // Filtrar los parámetros que no están relacionados con la sucursal
        $parametros = Parametro::where('estado', true)
            ->where('tipo', 'SUCURSAL')
            ->whereNotIn('id', $parametrosRelacionados)
            ->get();

        return view('rel-parametrosucursal.create', compact('relParametrosucursal', 'parametros', 'sucursal_id'));
    }
    public function updateParametros(Request $request)
    {
        DB::beginTransaction(); // Iniciar una transacción

        try {
            // Obtener el usuario autenticado y su sucursal
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal_id = $persona->sucursal_id;

            // Obtener todos los parámetros de tipo "SUCURSAL" que no estén eliminados
            $parametros = Parametro::where('tipo', 'SUCURSAL')
                ->whereNull('deleted_at')
                ->get();

            // Iterar sobre los parámetros para actualizar su valor
            foreach ($parametros as $parametro) {
                $valor = $request->input('parametro_' . $parametro->id, $parametro->valor);
                // Obtener las horas de entrada y salida desde el JSON


                if ($parametro->id == 7) {
                    // Obtener las horas de entrada y salida desde la solicitud
                    $hora_entrada = $request->hora_entrada;
                    $hora_salida = $request->hora_salida;

                    // Construir la cadena en el formato deseado sin escapar las comillas
                    $nuevo_valor = '["hora_entrada":"' . $hora_entrada . '","hora_salida":"' . $hora_salida . '"]';

                    // Actualizar el valor del parámetro en la tabla relacional con el nuevo valor formateado
                    RelParametrosucursal::where([
                        ['parametro_id', '=', $parametro->id],
                        ['sucursal_id', '=', $sucursal_id],
                    ])->update(['valor' => $nuevo_valor]);
                } else {
                    // Actualizar el valor del parámetro en la tabla relacional
                    RelParametrosucursal::where([
                        ['parametro_id', '=', $parametro->id],
                        ['sucursal_id', '=', $sucursal_id],
                    ])->update(['valor' => $valor]);
                }
            }

            DB::commit(); // Confirmar la transacción

            return response()->json([
                'success' => true,
                'message' => 'Parámetros actualizados correctamente.',
                'redirect_url' => route('rel_parametromisucursal/setup.index'),

            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir en caso de error

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function import($sucursal_id)
    {
        $sucursales = Sucursal::where('situacion', 'A')->get();
        return view('rel-parametrosucursal.importar', compact('sucursales', 'sucursal_id'));
    }


    public function importar(Request $request)
    {
        // Validar las entradas
        $request->validate([
            'sucursal_origen_id' => 'required|exists:sucursal,id',
            'sucursaldestino_id' => 'required|exists:sucursal,id',
        ]);

        $sucursalOrigenId = $request->input('sucursal_origen_id');
        $sucursalDestinoId = $request->input('sucursaldestino_id');

        try {
            // Filtrar las relaciones de parámetros que pertenecen a la empresa de origen
            $parametrosOrigen = RelParametrosucursal::where('sucursal_id', $sucursalOrigenId)->get();

            // Iniciar la transacción para asegurar la consistencia de los datos
            DB::beginTransaction();

            // Insertar cada relación en la empresa de destino
            foreach ($parametrosOrigen as $parametro) {
                RelParametrosucursal::updateOrCreate(
                    [
                        'sucursal_id' => $sucursalDestinoId,
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
                'redirect_url' => route('rel_parametrosucursal.index', $sucursalDestinoId),
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


    public function show($id)
    {
        $sucursal = RelParametrosucursal::find($id);

        return view('rel-parametrosucursal.show', compact('sucursal'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(RelParametrosucursalRequest $request)
    {
        DB::beginTransaction(); // Iniciar transacción

        try {
            // Crear el registro en la tabla rel_parametrosucursals
            $relParametrosucursal = RelParametrosucursal::create($request->validated());

            // Confirmar la transacción
            DB::commit();

            // Devolver respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Relación de parámetro-sucursal vinculada exitosamente.',
                'relParametrosucursal' => $relParametrosucursal,
                'redirect_url' => route('rel_parametrosucursal.index', $relParametrosucursal->sucursal_id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir transacción si hay un error

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
        $relParametrosucursal = RelParametrosucursal::find($id);
        $parametros = Parametro::where('estado', true)
            ->where('tipo', 'SUCURSAL')
            ->get();
        $sucursal_id = $relParametrosucursal->sucursal_id;
        return view('rel-parametrosucursal.edit', compact('relParametrosucursal', 'parametros', 'sucursal_id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RelParametrosucursalRequest $request, RelParametrosucursal $relParametrosucursal)
    {
        try {
            // Actualizar el registro
            $relParametrosucursal->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Relación de parámetro-sucursal actualizada exitosamente.',
                'relParametrosucursal' => $relParametrosucursal,
                'redirect_url' => route('rel_parametrosucursal.index', $relParametrosucursal->sucursal_id)
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
    public function destroy($id, $sucursal_id)
    {
        try {
            // Eliminar el registro
            RelParametrosucursal::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Relación de parámetro-sucursal eliminada correctamente.',
                'redirect_url' => route('rel_parametrosucursal.index', $sucursal_id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
