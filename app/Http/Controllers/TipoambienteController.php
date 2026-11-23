<?php

namespace App\Http\Controllers;

use App\Models\Tipoambiente;
use App\Models\Operacione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class TipoambienteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Tipoambiente::query();
        $perPage = $request->input('perPage', 10);
        $columns = Schema::getColumnListing('tipoambiente');

        $textColumns = [];
        foreach ($columns as $column) {
            $columnType = DB::select("SELECT data_type FROM information_schema.columns WHERE table_name = 'tipoambiente' AND column_name = ?", [$column]);
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

        $tipoambientes = $query->orderBy('nombre', 'asc')->paginate($perPage);

        $operaciones = Operacione::where('tabla_id', 89)->where('estado', true)->get();

        return view('tipoambiente.index', compact('tipoambientes', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $tipoambientes->perPage());
    }

    public function create()
    {
        $tipoambiente = new Tipoambiente();
        return view('tipoambiente.create', compact('tipoambiente'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            Tipoambiente::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tipo de ambiente creado exitosamente.',
                'redirect_url' => route('tipoambiente.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $tipoambiente = Tipoambiente::findOrFail($id);
        return view('tipoambiente.show', compact('tipoambiente'));
    }

    public function edit($id)
    {
        $tipoambiente = Tipoambiente::findOrFail($id);
        return view('tipoambiente.edit', compact('tipoambiente'));
    }

    public function update(Request $request, Tipoambiente $tipoambiente)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $tipoambiente->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tipo de ambiente actualizado exitosamente.',
                'redirect_url' => route('tipoambiente.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Tipoambiente::findOrFail($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de ambiente eliminado correctamente.',
                'redirect_url' => route('tipoambiente.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
