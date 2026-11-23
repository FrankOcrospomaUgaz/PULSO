<?php

namespace App\Http\Controllers;

use App\Models\Huesped;
use App\Http\Requests\HuespedRequest;
use App\Models\Movhabitacion;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\Sucursal;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class HuespedController
 * @package App\Http\Controllers
 */
class HuespedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sucursal_id = Auth::user()->persona->sucursal_id;
        $search = $request->input('search');
        $tipopersona = $request->input('tipopersona');
        $genero = $request->input('genero');
        $no_grato = $request->input('no_grato');
        $perPage = $request->input('perPage', 10);

        $query = Persona::with('personamaestro')
            ->whereHas('huespedes', function ($q) {
                $q->whereNotNull('movhabitacion_id')
                ->whereHas('movhabitacion', function ($subq) {
                    $subq->whereNull('deleted_at');
                });
            })
            ->where('sucursal_id', $sucursal_id);

        if ($tipopersona && $tipopersona !== 'Todos') {
            $query->whereHas('personamaestro', function($q) use ($tipopersona) {
                $q->where('tipopersona', $tipopersona);
            });
        }
        if ($genero && $genero !== '0') {
            $query->whereHas('personamaestro', function($q) use ($genero) {
                $q->where('sexo', $genero);
            });
        }
        if ($no_grato !== null && $no_grato !== '' && $no_grato !== 'Todos') {
            if ($no_grato === '1' || $no_grato === 'SI' || $no_grato === 'true') {
                $query->where('no_grato', true);
            } elseif ($no_grato === '0' || $no_grato === 'NO' || $no_grato === 'false') {
                $query->where(function ($q) {
                    $q->where('no_grato', false)->orWhereNull('no_grato');
                });
            }
        }
        if ($search) {
            // Separar palabras (por espacios)
            $palabras = preg_split('/\s+/', trim($search));

            $query->where(function ($q) use ($palabras) {
                foreach ($palabras as $palabra) {
                    $q->where(function ($sub) use ($palabra) {
                        $sub->where('nombres', 'ILIKE', '%' . $palabra . '%')
                            ->orWhere('apellidos', 'ILIKE', '%' . $palabra . '%')
                            ->orWhereHas('personamaestro', function ($q2) use ($palabra) {
                                $q2->where('nrodoc', 'ILIKE', '%' . $palabra . '%');
                            });
                    });
                }
            });
        }


        $personas = $query->orderBy('id', 'desc')->paginate($perPage);

        $operaciones = Operacione::where('tabla_id', 91)
            ->where('estado', true)
            ->get();

        // Si la petición es AJAX, solo renderizar la tabla
        if ($request->ajax()) {
            return view('huesped.index', compact('personas', 'operaciones', 'sucursal_id'))->render();
        }

        return view('huesped.index', compact('personas', 'operaciones', 'sucursal_id'))
            ->with('i', (request()->input('page', 1) - 1) * $personas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $huesped = new Huesped();
        return view('huesped.create', compact('huesped'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HuespedRequest $request)
    {
        Huesped::create($request->validated());

        return redirect()->route('huespeds.index')
            ->with('success', 'Huesped created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $huesped = Huesped::find($id);

        return view('huesped.show', compact('huesped'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $huesped = Huesped::find($id);

        return view('huesped.edit', compact('huesped'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HuespedRequest $request, Huesped $huesped)
    {
        $huesped->update($request->validated());

        return redirect()->route('huespeds.index')
            ->with('success', 'Huesped updated successfully');
    }

    public function destroy($id)
    {
        Huesped::find($id)->delete();

        return redirect()->route('huespeds.index')
            ->with('success', 'Huesped deleted successfully');
    }
    public function Alojamientos($id)
    {
        try {
            // Obtener la sucursal del usuario autenticado
            $sucursal_id = Auth::user()->persona->sucursal_id;

            // Obtener todos los movhabitacion_id de esta persona desde la tabla huesped
            $movHabitacionIds = Huesped::where('persona_id', $id)
                ->pluck('movhabitacion_id');

            // Traer los movimientos de habitación que estén en esos IDs y en la sucursal actual
            $movimientoHabitacion = Movhabitacion::with(['habitacion'])
                ->where('sucursal_id', $sucursal_id)
                ->whereIn('id', $movHabitacionIds)
                ->orderBy('fechaingreso', 'desc')
                ->get();

            return view('huesped.historial-alojamientos', compact('movimientoHabitacion'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Exporta los huéspedes filtrados a Excel
     */
    public function generarExcelHuesped(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::findOrFail($persona->sucursal_id);

        // Filtros recibidos por GET/Request
        $tipopersona = $request->input('tipopersona', 'Todos');
        $genero = $request->input('genero', '0');
        $no_grato = $request->input('no_grato', 'Todos');
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10);

        // Query base
        $query = Persona::where('sucursal_id', $sucursal->id);

        if ($tipopersona && $tipopersona !== 'Todos') {
            $query->whereHas('personamaestro', function ($q) use ($tipopersona) {
                $q->where('tipopersona', $tipopersona);
            });
        }
        if ($genero && $genero !== '0') {
            $query->whereHas('personamaestro', function ($q) use ($genero) {
                $q->where('sexo', $genero);
            });
        }
        if ($no_grato !== null && $no_grato !== '' && $no_grato !== 'Todos') {
            if ($no_grato === '1' || $no_grato === 'SI' || $no_grato === 'true') {
                $query->where('no_grato', true);
            } elseif ($no_grato === '0' || $no_grato === 'NO' || $no_grato === 'false') {
                $query->where(function ($q) {
                    $q->where('no_grato', false)->orWhereNull('no_grato');
                });
            }
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'like', "%$search%")
                  ->orWhere('apellidos', 'like', "%$search%")
                  ->orWhereHas('personamaestro', function ($q2) use ($search) {
                      $q2->where('nrodoc', 'like', "%$search%");
                  });
            });
        }

        $huespedes = $query->with(['personamaestro', 'ubigeo', 'huespedes'])->get();

        // Exportar usando un export personalizado
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\HuespedExport($huespedes),
            'REPORTE_HUESPEDES.xlsx'
        );
    }
       /**
     * Exporta los huéspedes filtrados a PDF
     */
    public function generarPDFHuesped(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::findOrFail($persona->sucursal_id);

        // Filtros recibidos por GET/Request
        $tipopersona = $request->input('tipopersona', 'Todos');
        $genero = $request->input('genero', '0');
        $no_grato = $request->input('no_grato', 'Todos');
        $search = $request->input('search', '');

        // Query base
        $query = Persona::with('personamaestro')
            ->whereHas('huespedes', function ($q) {
                $q->whereNotNull('movhabitacion_id')
                ->whereHas('movhabitacion', function ($subq) {
                    $subq->whereNull('deleted_at');
                });
            })
            ->where('sucursal_id', $sucursal->id);

        if ($tipopersona && $tipopersona !== 'Todos') {
            $query->whereHas('personamaestro', function ($q) use ($tipopersona) {
                $q->where('tipopersona', $tipopersona);
            });
        }
        if ($genero && $genero !== '0') {
            $query->whereHas('personamaestro', function ($q) use ($genero) {
                $q->where('sexo', $genero);
            });
        }
        if ($no_grato !== null && $no_grato !== '' && $no_grato !== 'Todos') {
            if ($no_grato === '1' || $no_grato === 'SI' || $no_grato === 'true') {
                $query->where('no_grato', true);
            } elseif ($no_grato === '0' || $no_grato === 'NO' || $no_grato === 'false') {
                $query->where(function ($q) {
                    $q->where('no_grato', false)->orWhereNull('no_grato');
                });
            }
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'like', "%$search%")
                  ->orWhere('apellidos', 'like', "%$search%")
                  ->orWhereHas('personamaestro', function ($q2) use ($search) {
                      $q2->where('nrodoc', 'like', "%$search%");
                  });
            });
        }

        $huespedes = $query->with(['personamaestro', 'ubigeo', 'huespedes'])->get();

        // Renderizar la vista PDF (debes crear huesped/reportpdf.blade.php)
        $pdf = Pdf::loadView('huesped.reportpdf', [
            'huespedes' => $huespedes,
            'sucursal' => $sucursal,
            'tipopersona' => $tipopersona,
            'genero' => $genero,
            'search' => $search,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('REPORTE_HUESPEDES.pdf');
    }
}
