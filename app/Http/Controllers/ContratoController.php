<?php

namespace App\Http\Controllers;

use App\Helpers\NumeroTexto;
use App\Models\Ambiente;
use App\Models\CompromisoPago;
use App\Models\Contrato;
use App\Models\Operacione;
use App\Models\RelParametrosucursal;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use NumberFormatter;

class ContratoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = Contrato::with(['ambiente.tipoambiente', 'arrendatario', 'arrendador', 'edificio']);

        // ✅ Columnas de texto en la tabla contrato
        $columns = Schema::getColumnListing('contrato');
        $textColumns = [];
        foreach ($columns as $column) {
            $columnType = DB::select("
            SELECT data_type 
            FROM information_schema.columns 
            WHERE table_name = 'contrato' AND column_name = ?", [$column]);
            if (isset($columnType[0]) && in_array($columnType[0]->data_type, ['character varying', 'text'])) {
                $textColumns[] = $column;
            }
        }

        // ✅ Filtro por búsqueda
        if ($search) {
            $query->where(function ($q) use ($textColumns, $search) {
                foreach ($textColumns as $column) {
                    $q->orWhereRaw("LOWER($column) LIKE ?", ['%' . strtolower($search) . '%']);
                }
            })
                // 🔹 Buscar en Ambiente
                ->orWhereHas('ambiente', function ($q) use ($search) {
                    $q->whereRaw("LOWER(numero) LIKE ?", ['%' . strtolower($search) . '%'])
                        ->orWhereRaw("CAST(monto AS TEXT) LIKE ?", ['%' . strtolower($search) . '%']);
                })
                // 🔹 Buscar en Arrendatario
                ->orWhereHas('arrendatario', function ($q) use ($search) {
                    $q->whereRaw("LOWER(nombres) LIKE ?", ['%' . strtolower($search) . '%'])
                        ->orWhereRaw("LOWER(apellidos) LIKE ?", ['%' . strtolower($search) . '%'])
                        ->orWhereRaw("LOWER(email) LIKE ?", ['%' . strtolower($search) . '%']);
                })
                // 🔹 Buscar en Arrendador
                ->orWhereHas('arrendador', function ($q) use ($search) {
                    $q->whereRaw("LOWER(nombres) LIKE ?", ['%' . strtolower($search) . '%'])
                        ->orWhereRaw("LOWER(apellidos) LIKE ?", ['%' . strtolower($search) . '%']);
                })
                // 🔹 Buscar en Edificio
                ->orWhereHas('edificio', function ($q) use ($search) {
                    $q->whereRaw("LOWER(nombre) LIKE ?", ['%' . strtolower($search) . '%'])
                        ->orWhereRaw("LOWER(direccion) LIKE ?", ['%' . strtolower($search) . '%']);
                });
        }

        // ✅ Paginación
        $contratos = $query->orderByDesc('id')->paginate($perPage);

        // ✅ Operaciones
        $operaciones = Operacione::where('tabla_id', 87)
            ->where('estado', true)
            ->get();

        return view('contrato.index', compact('contratos', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $contratos->perPage());
    }

    public function create()
    {
        $contrato = new Contrato();
        $sucursal_id = Auth::user()->persona->sucursal_id;
        $pagoanticipado = RelParametrosucursal::where('sucursal_id', $sucursal_id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Pago anticipado de alquiler');
            })
            ->value('valor');
        $ambientes = \App\Models\Ambiente::where('sucursal_id', $sucursal_id)->where('situacion', 'L')->get();
        $personas = \App\Models\Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 3);
        })->where('sucursal_id', $sucursal_id)->orderBy('nombres')->get();
        $edificios = \App\Models\Edificio::where('sucursal_id', $sucursal_id)->orderBy('nombre')->get();
        $ultimoContrato = Contrato::orderBy('numero_contrato', 'desc')->first();
        $numeroContratoGenerado = $ultimoContrato
            ? str_pad(((int) $ultimoContrato->numero_contrato) + 1, 5, '0', STR_PAD_LEFT)
            : '00001';
        return view('contrato.create', compact('contrato', 'pagoanticipado', 'ambientes', 'personas', 'edificios', 'sucursal_id', 'numeroContratoGenerado'));
    }


    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'numero_contrato'        => 'required|string|max:100',
                'fecha_inicio'           => 'required|date',
                'fecha_fin'              => 'required|date|after_or_equal:fecha_inicio',
                'dia_pago'               => 'required|integer|min:1|max:31',
                'monto'                  => 'required|numeric|min:0',
                'garantia'               => 'nullable|numeric|min:0',
                'cubre_luz'              => 'boolean',
                'cubre_agua'             => 'boolean',
                'descripcion'            => 'nullable|string|max:1000',
                'informacion_adicional'  => 'nullable|string|max:2000',
                'ambiente_id'            => 'required|exists:ambiente,id',
                'persona_id'             => 'required|exists:persona,id',
                'arrendador_id'          => 'required|exists:persona,id',
                'edificio_id'            => 'required|exists:edificio,id',
                'sucursal_id'            => 'required|integer|exists:sucursal,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            // ✅ Generar PDF llamando directamente a la función generarVistaPrevia
            $pdfResponse = $this->generarVistaPrevia($request);

            if ($pdfResponse->status() !== 200) {
                return response()->json(['success' => false, 'errors' => 'No se pudo generar el PDF del contrato.'], 500);
            }

            // Guardar el contenido del PDF
            $pdfContent = $pdfResponse->getContent();
            $pdfFileName = time() . '_contrato.pdf';
            $pdfPath = 'storage/contratos/' . $pdfFileName;

            $destinationPath = public_path('storage/contratos');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            file_put_contents(public_path($pdfPath), $pdfContent);
            $data['pdfcontrato'] = $pdfPath;

            // Crear contrato
            $contrato = Contrato::create($data);

            // Guardar compromisos si se enviaron
            if ($request->filled('compromisos_pago')) {
                $compromisos = json_decode($request->input('compromisos_pago'), true);

                foreach ($compromisos as $compromiso) {
                    CompromisoPago::create([
                        'contrato_id'        => $contrato->id,
                        'persona_id'         => $data['persona_id'],
                        'ambiente_id'        => $data['ambiente_id'],
                        'fecha_vencimiento'  => $compromiso['fecha_vencimiento'],
                        'numero_cuota'       => $compromiso['numero_cuota'],
                        'monto'              => $compromiso['monto'],
                        'sucursal_id'        => $data['sucursal_id'],
                        'estado'             => 'pendiente'
                    ]);
                }
            }
            $ambiente = Ambiente::find($data['ambiente_id']);
            if ($ambiente) {
                $ambiente->situacion = 'A'; // Cambiar a 'Ocupado'
                $ambiente->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrato creado exitosamente.',
                'redirect_url' => route('contrato.index', ['page' => $request->input('pagina_actual', 1)])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $contrato = Contrato::findOrFail($id);
        return view('contrato.show', compact('contrato'));
    }

    public function edit($id)
    {
        $contrato = Contrato::findOrFail($id);
        $sucursal_id = Auth::user()->persona->sucursal_id;
        $pagoanticipado = RelParametrosucursal::where('sucursal_id', $sucursal_id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Pago anticipado de alquiler');
            })
            ->value('valor');

        $ambientes = \App\Models\Ambiente::where('sucursal_id', $sucursal_id)->get();
        $personas = \App\Models\Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 3);
        })->where('sucursal_id', $sucursal_id)->orderBy('nombres')->get();
        $edificios = \App\Models\Edificio::where('sucursal_id', $sucursal_id)->orderBy('nombre')->get();

        return view('contrato.edit', compact('contrato', 'pagoanticipado', 'ambientes', 'personas', 'edificios'))
            ->with('sucursal_id', $sucursal_id);
    }

    public function update(Request $request, Contrato $contrato)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'numero_contrato'        => 'required|string|max:100',
                'fecha_inicio'           => 'required|date',
                'fecha_fin'              => 'required|date|after_or_equal:fecha_inicio',
                'dia_pago'               => 'required|integer|min:1|max:31',
                'monto'                  => 'required|numeric|min:0',
                'garantia'               => 'nullable|numeric|min:0',
                'cubre_luz'              => 'boolean',
                'cubre_agua'             => 'boolean',
                'descripcion'            => 'nullable|string|max:1000',
                'informacion_adicional' => 'nullable|string|max:2000',
                'ambiente_id'            => 'required|exists:ambiente,id',
                'persona_id'             => 'required|exists:persona,id',
                'edificio_id'            => 'required|exists:edificio,id',
                'pdfcontrato'            => 'nullable|mimes:pdf|max:5120', // Máx 5MB
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            // Procesar el nuevo PDF si se sube
            if ($request->hasFile('pdfcontrato') && $request->file('pdfcontrato')->isValid()) {
                // Eliminar el archivo anterior si existe
                if (!empty($contrato->pdfcontrato) && file_exists(public_path($contrato->pdfcontrato))) {
                    unlink(public_path($contrato->pdfcontrato));
                }

                $pdf = $request->file('pdfcontrato');
                $pdfName = time() . '_' . $pdf->getClientOriginalName();
                $destinationPath = public_path('storage/contratos');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $pdf->move($destinationPath, $pdfName);
                $data['pdfcontrato'] = 'storage/contratos/' . $pdfName;
            } else {
                // Mantener el archivo PDF anterior si no se sube uno nuevo
                $data['pdfcontrato'] = $contrato->pdfcontrato;
            }

            $contrato->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrato actualizado exitosamente.',
                'redirect_url' => route('contrato.index', ['page' => $request->input('pagina_actual', 1)])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }
    }


    public function destroy($id)
    {
        try {
            Contrato::findOrFail($id)->delete();
            return response()->json([
                'success' => true,
                'message' => 'Contrato eliminado correctamente.',
                'redirect_url' => route('contrato.index')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }
    }

    public function anular($id)
    {
        try {
            Contrato::findOrFail($id)->update(['situacion' => 'C']);
            return response()->json([
                'success' => true,
                'message' => 'Contrato cancelado correctamente.',
                'redirect_url' => route('contrato.index')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }
    }


    public function finalizar($id)
    {
        try {
            $contrato = Contrato::findOrFail($id);
            $contrato->update(['situacion' => 'R']); // aquí ya cambia la situacion

            $ambiente = Ambiente::find($contrato->ambiente_id);
            if ($ambiente) {
                $ambiente->situacion = 'L'; // Cambiar a Libre (entiendo que 'L' es libre, no ocupado)
                $ambiente->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Contrato finalizado correctamente.',
                'redirect_url' => route('contrato.index')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }
    }

    public function ambientesPorEdificio($edificioId)
    {
        $ambientes = \App\Models\Ambiente::with('piso')
            ->where('situacion', 'L') // Solo ambientes libres
            ->where('edificio_id', $edificioId)
            ->get()
            ->map(function ($ambiente) {
                return [
                    'id' => $ambiente->id,
                    'numero' => $ambiente->numero,
                    'piso_nombre' => $ambiente->piso->nombre ?? '',
                ];
            });

        return response()->json($ambientes);
    }


    public function generarVistaPrevia(Request $request)
    {
        $ambiente = \App\Models\Ambiente::with('tipoambiente.plantillaContrato')->findOrFail($request->ambiente_id);
        $html = $ambiente->tipoambiente->plantillaContrato->contenido_html ?? '';

        if (empty($html)) {
            return response()->json(['error' => 'No hay contenido HTML.'], 400);
        }
        $inicio = Carbon::parse($request->fecha_inicio);
        $fin = Carbon::parse($request->fecha_fin);
        $diff = $inicio->diff($fin);

        $duracion = '';
        if ($diff->y > 0) {
            $duracion .= $diff->y . ' año' . ($diff->y > 1 ? 's' : '');
        }
        if ($diff->m > 0) {
            $duracion .= ($duracion ? ' y ' : '') . $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '');
        }
        if ($duracion === '') {
            $duracion = '0 meses'; // en caso de fechas iguales o mal ingresadas
        }
        $arrendatario = \App\Models\Persona::findOrFail($request->persona_id);
        $arrendador = \App\Models\Persona::findOrFail($request->arrendador_id);

        $numeroTexto = new NumeroTexto();
        $datos = [
            '{{ nombre_arrendador }}' => $arrendador->nombres . ' ' . $arrendador->apellidos ?? 'Arrendador Desconocido',
            '{{ dni_arrendador }}' =>  $arrendador->personamaestro->nrodoc  ?? '00000000',
            '{{ direccion_arrendador }}' => $arrendador->direccion  ??  'Av. Siempre Viva 123',
            '{{ nombre_arrendatario }}' => $arrendatario->nombres . ' ' . $arrendatario->apellidos ?? 'Arrendatario Desconocido',
            '{{ dni_arrendatario }}' => $arrendatario->personamaestro->nrodoc  ?? '00000000',
            '{{ celular_arrendatario }}' => $arrendatario->telefonomovil ?? '987654321',
            '{{ direccion_departamento }}' => $request->direccion_departamento ?? 'Lambayeque',
            '{{ ciudad_departamento }}' => $request->ciudad_departamento ?? 'Chiclayo',
            '{{ duracion_contrato }}' => $duracion,
            '{{ fecha_inicio }}' => $request->fecha_inicio,
            '{{ fecha_fin }}' => $request->fecha_fin,
            '{{ dias_anticipacion }}' => '30',
            '{{ monto_alquiler }}' => $request->monto,
            '{{ monto_alquiler_literal }}' => str_replace('SON: ', '', $numeroTexto->convertirLetras($request->monto, 'PEN')),

            '{{ dia_pago }}' => $request->dia_pago,
            '{{ monto_garantia }}' => $request->garantia,
            '{{ monto_garantia_literal }}' => str_replace('SON: ', '', $numeroTexto->convertirLetras($request->garantia, 'PEN')),

            '{{ uso_inmueble }}' => 'uso comercial',
            '{{ fecha_firma }}' => now()->format('d/m/Y'),
        ];

        foreach ($datos as $variable => $valor) {
            $html = str_replace($variable, $valor, $html);
        }

        $pdf = Pdf::loadHTML($html)->setPaper('A4');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf');
    }
}
