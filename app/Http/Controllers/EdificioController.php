<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use App\Models\Operacione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class EdificioController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Edificio::query();
        $perPage = $request->input('perPage', 10);
        $columns = Schema::getColumnListing('edificio');
        $textColumns = [];

        foreach ($columns as $column) {
            $columnType = DB::select("SELECT data_type FROM information_schema.columns WHERE table_name = 'edificio' AND column_name = ?", [$column]);

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

        $edificios = $query->orderBy('nombre', 'asc')->paginate($perPage);
        $operaciones = Operacione::where('tabla_id', 85)->where('estado', true)->get();

        return view('edificio.index', compact('edificios', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $edificios->perPage());
    }

    public function create()
    {
        $edificio = new Edificio();
        $sucursal_id = Auth::user()->persona->sucursal_id;
        return view('edificio.create', compact('edificio', 'sucursal_id'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'nombre'    => 'required|string|max:255',
                'direccion' => 'nullable|string|max:255',
                'sucursal_id' => 'required|integer|exists:sucursal,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            Edificio::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Edificio creado exitosamente.',
                'redirect_url' => route('edificio.index', ['page' => $request->input('pagina_actual', 1)])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $edificio = Edificio::find($id);
        return view('edificio.show', compact('edificio'));
    }

    public function edit($id)
    {
        $edificio = Edificio::find($id);
        return view('edificio.edit', compact('edificio'));
    }

    public function update(Request $request, Edificio $edificio)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'nombre'    => 'required|string|max:255',
                'direccion' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            $edificio->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Edificio actualizado exitosamente.',
                'redirect_url' => route('edificio.index', ['page' => $request->input('pagina_actual', 1)])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Edificio::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Edificio eliminado correctamente.',
                'redirect_url' => route('edificio.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
