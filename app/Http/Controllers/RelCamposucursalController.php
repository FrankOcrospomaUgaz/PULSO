<?php

namespace App\Http\Controllers;

use App\Models\RelCamposucursal;
use App\Models\Operacione;
use App\Models\RelTablasucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelCamposucursalController extends Controller
{
    public function verCampos(Request $request, $id)
    {
        // Buscar el registro de RelTablasucursal
        $tablaSucursal = RelTablasucursal::findOrFail($id);

        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = RelCamposucursal::with('campo')
            ->where('sucursal_id', $tablaSucursal->sucursal_id)
            ->where('tabla_id', $tablaSucursal->tabla_id)
            ->orderBy('orden', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                // Buscar en RelCamposucursal
                $q->where('tipo', 'ILIKE', "%{$search}%")
                    ->orWhere('orden', 'ILIKE', "%{$search}%")
                    ->orWhere('diccionario', 'ILIKE', "%{$search}%")
                    ->orWhere('situacion', 'ILIKE', "%{$search}%");

                // Buscar en la relación Campo
                $q->orWhereHas('campo', function ($sub) use ($search) {
                    $sub->where('descripcion', 'ILIKE', "%{$search}%")
                        ->orWhere('comentario', 'ILIKE', "%{$search}%")
                        ->orWhere('diccionario', 'ILIKE', "%{$search}%")
                        ->orWhere('validacion', 'ILIKE', "%{$search}%")
                        ->orWhere('msgvalidacion', 'ILIKE', "%{$search}%");
                });
            });
        }

        $relCamposucursals = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        // Operaciones
        $operaciones = Operacione::where('tabla_id', 50)
            ->where('estado', true)
            ->get();

        // Valores de mínimo y máximo orden
        $minOrden = RelCamposucursal::where('orden', '>', 0)->min('orden');
        $maxOrden = $relCamposucursals->max('orden');

        return view('rel-tablasucursal.campos', compact(
            'relCamposucursals',
            'operaciones',
            'id',
            'minOrden',
            'maxOrden',
            'search',
            'perPage'
        ))->with('i', (request()->input('page', 1) - 1) * $relCamposucursals->perPage());
    }


    public function activar($id)
    {
        try {
            $relCampo = RelCamposucursal::find($id);
            $relCampo->update(['situacion' => true]);

            $tablaSucursal = RelTablasucursal::where('tabla_id', $relCampo->tabla_id)->first();
            return response()->json([
                'success' => true,
                'message' => 'Perfil de sucursal activada exitosamente.',
                'encuesta' => $tablaSucursal,
                'redirect_url' => route('rel_tablasucursal.verCampos', $tablaSucursal->id)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function desactivar($id)
    {
        try {
            $relCampo = RelCamposucursal::find($id);
            $relCampo->update(['situacion' => false]);

            $tablaSucursal = RelTablasucursal::where('tabla_id', $relCampo->tabla_id)->first();
            return response()->json([
                'success' => true,
                'message' => 'Perfil de sucursal desactivada exitosamente.',
                'encuesta' => $tablaSucursal,
                'redirect_url' => route('rel_tablasucursal.verCampos', $tablaSucursal->id)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function moveOrderCampo($id, $direction)
    {
        try {
            $relCampo = RelCamposucursal::find($id);

            if ($direction === 'Subir') {
                $otherCampo = RelCamposucursal::where('orden', '<', $relCampo->orden)
                    ->where('tabla_id', $relCampo->tabla_id)
                    ->orderBy('orden', 'desc')
                    ->first();

                if ($otherCampo) {
                    $this->swapOrder($relCampo, $otherCampo);
                }
            } else if ($direction === 'Bajar') {
                $otherCampo = RelCamposucursal::where('orden', '>', $relCampo->orden)
                    ->where('tabla_id', $relCampo->tabla_id)
                    ->orderBy('orden', 'asc')
                    ->first();

                if ($otherCampo) {
                    $this->swapOrder($relCampo, $otherCampo);
                }
            }

            $tablaSucursal = RelCamposucursal::where('tabla_id', $relCampo->tabla_id)->first();
            return response()->json([
                'success' => true,
                'redirect_url' => route('rel_tablasucursal.verCampos', $tablaSucursal->id)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function swapOrder($relCampo1, $relCampo2)
    {
        $tempOrder = $relCampo1->orden;
        $relCampo1->orden = $relCampo2->orden;
        $relCampo2->orden = $tempOrder;

        $relCampo1->save();
        $relCampo2->save();
    }
}
