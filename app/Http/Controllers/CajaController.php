<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Http\Requests\CajaRequest;
use App\Models\Operacione;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $perPage = $request->input('perPage', 10);
        // Query base
        $query = Caja::where('sucursal_id', $persona->sucursal_id);

        // Si existe búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'ILIKE', "%{$search}%")
                    ->orWhere('serie', 'ILIKE', "%{$search}%")
                    ->orWhere('situacion', 'ILIKE', "%{$search}%");
            });
        }

        // Paginación ordenada por número
        $cajas = $query->orderBy('numero', 'asc')->paginate($perPage);

        // Operaciones activas de la tabla 'caja'
        $operaciones = Operacione::where('tabla_id', 60)
            ->where('estado', true)
            ->get();

        return view('caja.index', compact('cajas', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $cajas->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $caja = new Caja();
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        return view('caja.create', compact('caja', 'sucursal_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CajaRequest $request)
    {
        try {
            Caja::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Caja creada exitosamente.',
                'redirect_url' => route('caja.index')
            ]);
        } catch (\Exception $e) {
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
        $caja = Caja::find($id);
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        return view('caja.edit', compact('caja', 'sucursal_id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CajaRequest $request, Caja $caja)
    {
        try {
            DB::transaction(function () use ($request, $caja) {
                $datos = $request->validated();

                $caja->update($datos);
                $this->actualizarSerieVentasSucursal(
                    (int) $caja->sucursal_id,
                    (string) $caja->serie
                );
            });

            return response()->json([
                'success' => true,
                'message' => 'Caja actualizada exitosamente.',
                'redirect_url' => route('caja.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincroniza la serie completa de las ventas de la sucursal.
     *
     * La caja almacena solo la serie base (por ejemplo, 010), mientras que
     * movventa almacena el prefijo del comprobante junto con esa serie
     * (por ejemplo, B010 o F010).
     */
    private function actualizarSerieVentasSucursal(int $sucursalId, string $serieCaja): void
    {
        $tiposDocumento = DB::table('tipodocumento as td')
            ->join('movimiento as m', 'm.tipodocumento_id', '=', 'td.id')
            ->join('movventa as mv', 'mv.movimiento_id', '=', 'm.id')
            ->where('mv.sucursal_id', $sucursalId)
            ->select('td.id', 'td.abreviatura')
            ->distinct()
            ->get();

        foreach ($tiposDocumento as $tipoDocumento) {
            $nuevaSerie = (string) $tipoDocumento->abreviatura.$serieCaja;

            DB::table('movventa')
                ->where('sucursal_id', $sucursalId)
                ->whereIn('movimiento_id', function ($query) use ($tipoDocumento) {
                    $query->select('id')
                        ->from('movimiento')
                        ->where('tipodocumento_id', $tipoDocumento->id);
                })
                ->where(function ($query) use ($nuevaSerie) {
                    $query->whereNull('serie')
                        ->orWhere('serie', '<>', $nuevaSerie);
                })
                ->update([
                    'serie' => $nuevaSerie,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            Caja::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Caja eliminada correctamente.',
                'redirect_url' => route('caja.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar una Caja especificada.
     */
    public function activar($id)
    {
        try {
            $caja = Caja::find($id);
            $caja->update(['situacion' => 'A']);

            return response()->json([
                'success' => true,
                'message' => 'Caja digital activada exitosamente.',
                'encuesta' => $caja,
                'redirect_url' => route('caja.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar una Caja especificada.
     */
    public function desactivar($id)
    {
        try {
            $caja = Caja::find($id);
            $caja->update(['situacion' => 'I']);

            return response()->json([
                'success' => true,
                'message' => 'Caja digital desactivada exitosamente.',
                'encuesta' => $caja,
                'redirect_url' => route('caja.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
