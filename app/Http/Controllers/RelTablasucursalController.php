<?php

namespace App\Http\Controllers;

use App\Models\RelTablasucursal;
use App\Http\Requests\RelTablasucursalRequest;
use App\Models\Operacione;
use App\Models\RelOperacionsucursal;
use App\Models\RelParametrosucursal;
use App\Models\Tabla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class RelTablasucursalController
 * @package App\Http\Controllers
 */
class RelTablasucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $sucursal_id)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = RelTablasucursal::with('tabla')
            ->where('sucursal_id', $sucursal_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('tabla', function ($sub) use ($search) {
                    $sub->where('descripcion', 'ILIKE', "%{$search}%")
                        ->orWhere('comentario', 'ILIKE', "%{$search}%")
                        ->orWhere('tipo', 'ILIKE', "%{$search}%")
                        ->orWhere('estado', 'ILIKE', "%{$search}%");
                });
            });
        }

        $relTablasucursals = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 30)
            ->where('estado', true)
            ->get();

        return view('rel-tablasucursal.index', compact('relTablasucursals', 'operaciones', 'sucursal_id', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $relTablasucursals->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($sucursal_id)
    {
        // Obtener las tablas que ya están relacionadas con la sucursal
        $tablasExistentes = RelTablasucursal::where('sucursal_id', $sucursal_id)->pluck('tabla_id')->toArray();

        // Obtener las tablas que NO están relacionadas con la sucursal
        $tablas = Tabla::where('estado', true)
            ->whereNotIn('id', $tablasExistentes) // Excluir tablas que ya están en relTablasucursal
            ->get();

        // Pasar un nuevo objeto y el ID de la sucursal para la creación
        $relTablasucursal = new RelTablasucursal();

        return view('rel-Tablasucursal.create', compact('relTablasucursal', 'sucursal_id', 'tablas'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(RelTablasucursalRequest $request)
    {
        DB::beginTransaction();

        try {
            // Crear el registro en la tabla rel_tablasucursals
            $relTablasucursal = RelTablasucursal::create($request->validated());

            // Confirmar la transacción
            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Relación tabla-sucursal creada exitosamente.',
                'relTablasucursal' => $relTablasucursal,
                'redirect_url' => route('rel_tablasucursal.index', $relTablasucursal->sucursal_id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Respuesta de error
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $relTablasucursal = RelTablasucursal::find($id);

        return view('rel-tablasucursal.show', compact('relTablasucursal'));
    }

    /**
     * Show the form for editing the specified resource.
     */



    // public function edit($id)
    // {
    //     $relTablasucursal = RelTablasucursal::find($id);
    //     $tablas = Tabla::where('estado', true)->get();
    //     $sucursal_id =  $relTablasucursal->sucursal_id;

    //     return view('rel-tablasucursal.edit', compact('relTablasucursal', 'tablas', 'sucursal_id'));
    // }

    /**
     * Update the specified resource in storage.
     */
    // public function update(RelTablasucursalRequest $request, RelTablasucursal $relTablasucursal)
    // {
    //     try {
    //         // Actualizar el registro
    //         $relTablasucursal->update($request->validated());

    //         // Respuesta exitosa
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Relación tabla-sucursal actualizada exitosamente.',
    //             'relTablasucursal' => $relTablasucursal,
    //             'redirect_url' => route('rel_tablasucursal.index', $relTablasucursal->sucursal_id)
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, $sucursal_id)
    {
        try {
            // Eliminar el registro
            RelTablasucursal::find($id)->delete();

            // Respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Relación tabla-sucursal eliminada correctamente.',
                'redirect_url' => route('rel_tablasucursal.index', $sucursal_id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function verSucursalOperaciones(Request $request, $id)
    {
        $tablasucursal = RelTablasucursal::findOrFail($id);

        // Query base con relación
        $query = RelOperacionsucursal::where('sucursal_id', $tablasucursal->sucursal_id)
            ->where('tabla_id', $tablasucursal->tabla_id)
            ->with('operacione');

        // 🔹 Búsqueda en Operacione
        if ($request->filled('search')) {
            $search = trim(mb_strtolower($request->search));
            $words = explode(' ', $search);

            $query->whereHas('operacione', function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($q2) use ($word) {
                        $q2->whereRaw('LOWER(tipo) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(descripcion) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(comentario) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(accion) LIKE ?', ["%{$word}%"]);
                    });
                }
            });
        }

        // 🔹 Paginación dinámica
        $perPage = $request->input('perPage', 10);
        $relOperacionsucursals = $query->orderBy('orden', 'asc')
            ->paginate($perPage)
            ->appends([
                'search' => $request->input('search'),
                'perPage' => $perPage
            ]);

        // Otras variables
        $operaciones = Operacione::where('tabla_id', 26)
            ->where('estado', true)
            ->get();

        $sucursal_id = $id;
        $minOrden = RelOperacionsucursal::where('orden', '>', 0)->min('orden');
        $maxOrden = $relOperacionsucursals->max('orden');

        return view('rel-tablasucursal.operaciones', compact(
            'relOperacionsucursals',
            'operaciones',
            'sucursal_id',
            'minOrden',
            'maxOrden',
            'id'
        ))->with('i', (request()->input('page', 1) - 1) * $relOperacionsucursals->perPage());
    }

    public function activar($id)
    {
        try {
            $rel = RelOperacionsucursal::find($id);
            $rel->update(['situacion' => true]);
            $tablasucursal = RelTablasucursal::where('tabla_id', $rel->tabla_id)->first();

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Operacion de sucursal activada exitosamente.',
                'encuesta' => $tablasucursal,
                'redirect_url' => route('rel_tablasucursal.verOperaciones', $tablasucursal->id)
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
            $rel = RelOperacionsucursal::find($id);
            $rel->update(['situacion' => false]);
            $tablasucursal = RelTablasucursal::where('tabla_id', $rel->tabla_id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Operacion de sucursal desactivada exitosamente.',
                'encuesta' => $tablasucursal,
                'redirect_url' => route('rel_tablasucursal.verOperaciones', $tablasucursal->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    public function moveOrderOperacion($id, $direction)
    {
        try {
            $relope = RelOperacionsucursal::find($id);

            if (!$relope) {
                return response()->json(['success' => false, 'message' => 'Menú opción no encontrada.']);
            }

            if ($direction === 'Subir') {
                $otherrel = RelOperacionsucursal::where('orden', '<', $relope->orden)->where('tabla_id', $relope->tabla_id)
                    ->where('sucursal_id', $relope->sucursal_id)
                    ->orderBy('orden', 'desc')
                    ->first();

                if ($otherrel) {
                    $this->swapOrder($relope, $otherrel);
                }
            } else if ($direction === 'Bajar') {
                $otherrel = RelOperacionsucursal::where('orden', '>', $relope->orden)->where('tabla_id', $relope->tabla_id)
                    ->where('sucursal_id', $relope->sucursal_id)
                    ->orderBy('orden', 'asc')
                    ->first();

                if ($otherrel) {
                    $this->swapOrder($relope, $otherrel);
                }
            }
            $tablasucursal = RelTablasucursal::where('tabla_id', $relope->tabla_id)->where('sucursal_id', $relope->sucursal_id)
                ->first();

            return response()->json([
                'success' => true,
                'redirect_url' => route('rel_tablasucursal.verOperaciones', $tablasucursal->id)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function swapOrder($rel1, $rel2)
    {
        $tempOrder = $rel1->orden;
        $rel1->orden = $rel2->orden;
        $rel2->orden = $tempOrder;

        $rel1->save();
        $rel2->save();
    }
}
