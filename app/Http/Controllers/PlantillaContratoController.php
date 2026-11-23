<?php

namespace App\Http\Controllers;

use App\Models\PlantillaContrato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Operacione;
use Carbon\Carbon;

class PlantillaContratoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = PlantillaContrato::where('sucursal_id', Auth::user()->persona->sucursal_id);
        $perPage = request()->input('perPage', 10);
        // Detectar columnas de tipo texto
        $columns = Schema::getColumnListing('plantilla_contrato');
        $textColumns = [];
        foreach ($columns as $column) {
            $columnType = DB::select("
                SELECT data_type
                FROM information_schema.columns
                WHERE table_name = 'plantilla_contrato' AND column_name = ?
            ", [$column]);

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

        $plantillaContratos = $query->orderBy('id', 'desc')->paginate($perPage);
        $operaciones = Operacione::where('tabla_id', 90)->where('estado', true)->get(); // tabla_id personalizado

        return view('plantilla-contrato.index', compact('plantillaContratos', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $plantillaContratos->perPage());
    }

    public function create()
    {
        $plantillaContrato = new PlantillaContrato();
        $tiposAmbiente = \App\Models\Tipoambiente::orderBy('nombre')->get();
        $variables = \App\Models\VariablesPlantilla::where('situacion', true)->select('codigo')->get();
        return view('plantilla-contrato.create', compact('plantillaContrato', 'tiposAmbiente', 'variables'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'titulo' => 'required|string|max:255',
                'tipo_ambiente_id' => 'required|string|max:100',
                'contenido_html' => 'required|string',
                'descripcion' => 'nullable|string',
            ]);

            $user = Auth::user();
            $persona = $user->persona;

            PlantillaContrato::create([
                'titulo' => $request->titulo,
                'tipo_ambiente_id' => $request->tipo_ambiente_id,
                'contenido_html' => $request->contenido_html,
                'descripcion' => $request->descripcion,
                'sucursal_id' => $persona->sucursal_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plantilla creada exitosamente.',
                'redirect_url' => route('plantilla_contrato.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $plantillaContrato = PlantillaContrato::findOrFail($id);
        return view('plantilla-contrato.show', compact('plantillaContrato'));
    }

    public function edit($id)
    {
        $plantillaContrato = PlantillaContrato::findOrFail($id);
        $tiposAmbiente = \App\Models\Tipoambiente::orderBy('nombre')->get();
        $variables = \App\Models\VariablesPlantilla::where('situacion', true)->select('codigo')->get();

        return view('plantilla-contrato.edit', compact('variables', 'plantillaContrato', 'tiposAmbiente'));
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'titulo' => 'required|string|max:255',
                'tipo_ambiente_id' => 'required|string|max:100',
                'contenido_html' => 'required|string',
                'descripcion' => 'nullable|string',
            ]);

            $plantilla = PlantillaContrato::findOrFail($id);

            $plantilla->update([
                'titulo' => $request->titulo,
                'tipo_ambiente_id' => $request->tipo_ambiente_id,
                'contenido_html' => $request->contenido_html,
                'descripcion' => $request->descripcion,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plantilla actualizada exitosamente.',
                'redirect_url' => route('plantilla_contrato.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            PlantillaContrato::findOrFail($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plantilla eliminada correctamente.',
                'redirect_url' => route('plantilla_contrato.index')
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
            $plantilla = PlantillaContrato::findOrFail($id);
            $plantilla->update(['situacion' => 'E']); // si planeas manejar estado/situación

            return response()->json([
                'success' => true,
                'message' => 'Plantilla activada correctamente.',
                'redirect_url' => route('plantilla_contrato.index')
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
            $plantilla = PlantillaContrato::findOrFail($id);
            $plantilla->update(['situacion' => 'D']); // si decides usarlo

            return response()->json([
                'success' => true,
                'message' => 'Plantilla desactivada correctamente.',
                'redirect_url' => route('plantilla_contrato.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
