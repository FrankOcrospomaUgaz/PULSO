<?php

namespace App\Http\Controllers;

use App\Models\Ambiente;
use App\Models\Operacione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AmbienteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Ambiente::query();
        $perPage = $request->input('perPage', 10);
        $columns = Schema::getColumnListing('ambiente');

        $textColumns = [];
        foreach ($columns as $column) {
            $columnType = DB::select("SELECT data_type FROM information_schema.columns WHERE table_name = 'ambiente' AND column_name = ?", [$column]);
            if (isset($columnType[0]) && in_array($columnType[0]->data_type, ['character varying', 'text'])) {
                $textColumns[] = $column;
            }
        }

        if ($search) {
            $query->where(function ($q) use ($textColumns, $search) {
                foreach ($textColumns as $column) {
                    $q->orWhereRaw("LOWER($column) LIKE ?", ['%' . strtolower($search) . '%']);
                }
            });
        }

        $ambientes = $query->orderBy('numero', 'asc')->paginate($perPage);

        $operaciones = Operacione::where('tabla_id', 86)->where('estado', true)->get();

        $situaciones = ['Activo', 'Desactivado'];

        return view('ambiente.index', compact('ambientes', 'operaciones', 'situaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $ambientes->perPage());
    }

    public function create()
    {
        $ambiente = new Ambiente();
        $sucursal_id = Auth::user()->persona->sucursal_id;
        // Lista de tipos de ambiente (puedes ajustar los nombres según tu dominio)
        $tipos = \App\Models\Tipoambiente::orderBy('nombre')->get();
        // Lista de edificios ordenados por nombre
        $edificios = \App\Models\Edificio::where('sucursal_id', $sucursal_id)->orderBy('nombre')->get();

        // Lista de pisos ordenados por número o nombre
        $pisos = \App\Models\Piso::where('sucursal_id', $sucursal_id)->orderBy('nombre')->get();

        return view('ambiente.create', compact('ambiente', 'tipos', 'edificios', 'pisos', 'sucursal_id'));
    }


    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'numero' => 'required|string|max:255',

                'monto' => 'nullable|numeric',
                'garantia' => 'nullable|numeric',
                'tipoambiente_id' => 'required|integer|exists:tipoambiente,id',
                'piso_id' => 'required|integer|exists:piso,id',
                'edificio_id' => 'required|integer|exists:edificio,id',
                'sucursal_id' => 'required|integer|exists:sucursal,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            Ambiente::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ambiente creado exitosamente.',
                'redirect_url' => route('ambiente.index', ['page' => $request->input('pagina_actual', 1)]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $ambiente = Ambiente::findOrFail($id);
        return view('ambiente.show', compact('ambiente'));
    }

    public function edit($id)
    {
        $ambiente = Ambiente::findOrFail($id);
        $sucursal_id = Auth::user()->persona->sucursal_id;
        // Lista de tipos de ambiente (puedes ajustar los nombres según tu dominio)
        $tipos = \App\Models\Tipoambiente::orderBy('nombre')->get();
        // Lista de edificios ordenados por nombre
        $edificios = \App\Models\Edificio::where('sucursal_id', $sucursal_id)->orderBy('nombre')->get();

        // Lista de pisos ordenados por número o nombre
        $pisos = \App\Models\Piso::where('sucursal_id', $sucursal_id)->orderBy('nombre')->get();

        return view('ambiente.edit', compact('ambiente', 'tipos', 'edificios', 'pisos'));
    }

    public function update(Request $request, Ambiente $ambiente)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'numero' => 'required|string|max:255',
                'tipoambiente_id' => 'required|integer|exists:tipoambiente,id',
                'monto' => 'nullable|numeric',
                'garantia' => 'nullable|numeric',
                'piso_id' => 'required|integer|exists:piso,id',
                'edificio_id' => 'required|integer|exists:edificio,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            $ambiente->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ambiente actualizado exitosamente.',
                'redirect_url' => route('ambiente.index', ['page' => $request->input('pagina_actual', 1)]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Ambiente::findOrFail($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ambiente eliminado correctamente.',
                'redirect_url' => route('ambiente.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
    public function obtenerDatos($id)
    {
        $ambiente = Ambiente::find($id);

        if (!$ambiente) {
            return response()->json(['success' => false, 'message' => 'Ambiente no encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'monto' => $ambiente->monto,
            'garantia' => $ambiente->garantia,
        ]);
    }
}
