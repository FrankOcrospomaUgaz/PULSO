<?php

namespace App\Http\Controllers;

use App\Models\{Ambiente, Banco, Billeteradigital, Caja, CompromisoPago, Contrato, DetMovcaja, DetMovventum, Edificio, Facturador, Igv, Mediopago, Movcaja, Movimiento, Movventum, Operacione, Pasarelapago, Persona, RelParametrosucursal, RelProductosucursal, RelTurnocaja, Sucursal, Tarjetum, Tipodocumento, Turno, Unidad};
use App\Services\CorrelativoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema, Validator};

class CompromisoPagoController extends Controller
{
    public function index(Request $request, $contratoId)
    {
        $perPage = $request->input('perPage', 10);

        // Query base
        $query = CompromisoPago::where('contrato_id', $contratoId)
            ->with(['persona', 'ambiente.tipoambiente']);

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_cuota', 'ILIKE', "%{$search}%")
                    ->orWhere('monto', '::text ILIKE', "%{$search}%")
                    ->orWhere('forma_pago', 'ILIKE', "%{$search}%")
                    ->orWhereHas('persona', function ($sub) use ($search) {
                        $sub->where('nombre', 'ILIKE', "%{$search}%")
                            ->orWhere('apellido', 'ILIKE', "%{$search}%");
                    })
                    ->orWhereHas('ambiente', function ($sub) use ($search) {
                        $sub->where('descripcion', 'ILIKE', "%{$search}%");
                    });
            });
        }

        // Paginación
        $compromisoPagos = $query->orderBy('fecha_pago', 'asc')->paginate($perPage);

        // Contrato relacionado
        $contrato = Contrato::findOrFail($contratoId);

        // Operaciones de la tabla compromiso_pago
        $operaciones = Operacione::where('tabla_id', 88)
            ->where('estado', true)
            ->get();

        return view('compromiso-pago.index', compact('compromisoPagos', 'operaciones', 'contratoId', 'contrato'))
            ->with('i', (request()->input('page', 1) - 1) * $compromisoPagos->perPage());
    }


    public function create($contratoId)
    {
        $item = new CompromisoPago();
        $sucursal_id = Auth::user()->persona->sucursal_id;
        $contrato = Contrato::findOrFail($contratoId);
        $personas = Persona::where('sucursal_id', $sucursal_id)->orderBy('nombres')->get();
        $ambientes = Ambiente::where('sucursal_id', $sucursal_id)->with('piso')->get();
        $edificios = Edificio::where('sucursal_id', $sucursal_id)->get();

        return view('compromiso-pago.create', compact('item', 'contrato', 'personas', 'ambientes', 'edificios', 'sucursal_id'));
    }

    public function store(Request $request, $contratoId)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'personamaestro_id'   => 'required|exists:personamaestro,id',
                'ambiente_id'         => 'nullable|exists:ambiente,id',
                'fecha_vencimiento'   => 'required|date',
                'fecha_pago'          => 'nullable|date',
                'numero_cuota'        => 'required|integer|min:1',
                'monto'               => 'required|numeric|min:0',
                'forma_pago'          => 'nullable|string|max:100',
                'estado'              => 'nullable|string|max:50',
                'sucursal_id'         => 'required|exists:sucursal,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            $data['contrato_id'] = $contratoId;

            CompromisoPago::create($data);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Compromiso de pago creado correctamente.',
                'redirect_url' => route('contrato.compromiso_pago.index', $contratoId)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }
    }

    public function edit($contratoId, $id)
    {
        $item = CompromisoPago::findOrFail($id);
        $sucursal_id = Auth::user()->persona->sucursal_id;
        $contrato = Contrato::findOrFail($contratoId);
        $personas = Persona::where('sucursal_id', $sucursal_id)->orderBy('nombres')->get();
        $ambientes = Ambiente::where('sucursal_id', $sucursal_id)->with('piso')->get();
        $edificios = Edificio::where('sucursal_id', $sucursal_id)->get();

        return view('compromiso-pago.edit', compact('item', 'contrato', 'personas', 'ambientes', 'edificios', 'sucursal_id'));
    }

    public function pagar($id)
    {

        $compromiso = CompromisoPago::findOrFail($id);
        $totalMovventas = $compromiso->movventas->sum('total');
        $sucursal_id = Auth::user()->persona->sucursal_id;
        $contrato = Contrato::findOrFail($compromiso->contrato_id);

        $personas = \App\Models\Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 3);
        })->where('sucursal_id', $sucursal_id)->orderBy('nombres')->get();
        $ambientes = Ambiente::where('sucursal_id', $sucursal_id)->with('piso')->get();
        $edificios = Edificio::where('sucursal_id', $sucursal_id)->get();



        $sucursal_id = Auth::user()->persona->sucursal_id;
        // Obtener todos los productos disponibles para ser agregados al detalle del movimiento
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $personas = Persona::where('sucursal_id', $sucursal->id)->get();
        $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 2)
            ->get();
        $tipoDocumentoParametro = RelParametrosucursal::where('sucursal_id', $sucursal->id)
            ->where('parametro_id', 10) // ID del parámetro para IGV
            ->value('valor'); // Tomamos directamente el campo 'valor'
        // Tipo de documento predeterminado (Ticket)
        $tipoDocumentoPredeterminado = $tiposDocumento->where('id', $tipoDocumentoParametro)->first();
        $cajas = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->get();
        // Obtener los tipos de documento relacionados con `tipo_movimiento_id = 2`
        $medio_pagos = Mediopago::where('situacion', true)->get();
        $pos = Pasarelapago::where('situacion', true)->get(); // Tabla para P.O.S.
        $tarjetas = Tarjetum::where('situacion', true)->get(); // Tabla para Tarjetas
        $bancos = Banco::where('situacion', true)->get(); // Tabla para Bancos
        $billeteras = Billeteradigital::where('situacion', true)->get(); // Tabla para Billeteras Digitales

        $medioEfectivo = $medio_pagos->firstWhere('id', 1); // Asumiendo que el nombre es 'EFECTIVO'

        $detallesMovimientos = [
            [
                'tipo' => 'Pagado', // o el valor correcto según tu lógica
                'monto' => $compromiso->monto - $totalMovventas,
                'mediopago_id' => $medioEfectivo?->id ?? null,
                'mediopago_nombre' => $medioEfectivo?->descripcion ?? 'Efectivo',
                'detalles' => '',
                'notas' => '',
                'fecha_vencimiento' => now()->format('Y-m-d'),
            ],
        ];

        // Generar detalle de venta por servicio de arrendamiento
        $detallesVenta = [
            [
                'producto_id' => 0, // Puedes usar 0 o null si no hay producto real
                'descripcion' => 'Servicio de arrendamiento ambiente',
                'codigo' => 'ARR-SERV',
                'unidad' => [
                    'descripcion' => 'Servicio(s)'
                ],
                'cantidad' => 1,
                'precio_unitario' => $compromiso->monto - $totalMovventas,
                'monto' => $compromiso->monto - $totalMovventas,
                'total' => $compromiso->monto - $totalMovventas,
                'json_producto' => json_encode([
                    'precio_compra' => 0,
                    'precio_venta' => $compromiso->monto - $totalMovventas,
                    'igv' => 0,
                    'situacion' => 'N'
                ]),
                'situacion' => 'N'
            ]
        ];


        return view('compromiso-pago.pagar', compact('compromiso', 'detallesVenta', 'detallesMovimientos', 'pos', 'tarjetas', 'bancos', 'billeteras', 'tiposDocumento', 'cajas', 'medio_pagos', 'tipoDocumentoPredeterminado', 'contrato', 'personas', 'ambientes', 'edificios', 'sucursal_id', 'totalMovventas'));
    }

    public function pagostore(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validación de datos
            $validator = Validator::make($request->all(), [
                'sucursal_id' => 'required|exists:sucursal,id',

                'estado' => 'nullable|string',
                'moneda' => 'required|string|in:PEN,USD',
                'monto_apagar' => 'required|numeric|min:0',
                'detalles_json' => 'required|json',
                'detalles_movimientos_json' => 'required|json',
                'persona_id' => 'nullable|exists:persona,id',
                'caja_id' => 'required|exists:caja,id',
                'comentario' => 'nullable|string',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            $user = Auth::user();
            $personasucursal = Persona::find($user->persona_id);
            $sucursal = Sucursal::find($personasucursal->sucursal_id);
            // Datos generales
            $usuario = Auth::user();
            $responsable = $usuario->persona;
            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;
            $caja = Caja::find($request->caja_id);
            $turnoCaja = RelTurnocaja::conCajaActiva($request->caja_id)->first();
            $turnoId = $turnoCaja ? $turnoCaja->movcajainicio->turno_id : $request->turno_id;
            $tipodocumento = Tipodocumento::findOrFail($request->tipodocumento_id);
            // Crear Movimiento
            $movimiento = Movimiento::create([
                'numero' => $this->generateNumero($request->tipodocumento_id),
                'fecha' => now(),
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $responsable->id,
                'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                'persona_id' => $request->persona_id,
                'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                'comentario' => $request->comentario ?? '',
                'situacion' => 'A',
                'tipomovimiento_id' => Tipodocumento::find($request->tipodocumento_id)->tipomovimiento_id,
                'tipodocumento_id' => $request->tipodocumento_id,
                'sucursal_id' => $request->sucursal_id,
            ]);

            // Procesar detalles para obtener los igv_id de los productos
            $detalles = json_decode($request->detalles_json, true);
            // Buscar el valor del IGV predeterminado en la tabla rel_parametrosucursal
            $igvPredeterminado = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                ->where('parametro_id', 8) // ID del parámetro para IGV
                ->value('valor'); // Tomamos directamente el campo 'valor'
            $igvObjeto = Igv::find($igvPredeterminado);

            // Ahora se crea el Movventa con los valores calculados
            $movventa = Movventum::create([
                'fecha' => now(),
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $responsable->id,
                'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                'persona_id' => $request->persona_id,
                'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                'comentario' => $request->comentario ?? '',
                'serie' => $tipodocumento->abreviatura . '' . $caja->serie,
                'anio' => now()->year,
                'tipo_detalle' => 'DETALLADO',
                'estado' => $request->estado ?? 'N',
                'moneda' => $request->moneda,
                'tipocambio' => 3.71, // Suponiendo que esta tasa es fija
                'subtotal' => $request->monto_apagar -  $request->monto_apagar * $igvObjeto->porcentaje_igv / 100,
                'tipo_pago' => 'CONTADO',
                'igv' => $request->monto_apagar * $igvObjeto->porcentaje_igv / 100,
                'total' => $request->monto_apagar,
                'movimiento_id' => $movimiento->id,
                'sucursal_id' => $request->sucursal_id,
                'json_sucursal' => json_encode(Sucursal::find($request->sucursal_id)),
                'compromiso_pago_id' => $request->compromiso_id, // Asignar el ID del compromiso de pago
            ]);

            // Después se procesan los detalles de la venta
            foreach ($detalles as $detalle) {

                $unidad = Unidad::where('descripcion', $detalle['unidad'])->first();
                DetMovventum::create([
                    'movventa_id' => $movventa->id,
                    'tipo_detalle' => 'DETALLADO',
                    'codigo' => $detalle['codigo'],
                    'descripcion' => $detalle['descripcion'],
                    'producto_id' => null,
                    'json_producto' => json_encode($detalle),
                    'unidad_id' => $unidad ? $unidad->id : null,
                    'json_unidad' => $unidad ? json_encode($unidad) : null,
                    'igv_id' => $igvObjeto->id, // Asignar el igv_id desde la relación
                    'json_igv' =>  $igvObjeto ? json_encode($igvObjeto) : null,
                    'cantidad' => $detalle['cantidad'],
                    'monto' => $detalle['precio_unitario'] * $detalle['cantidad'],
                    'dcto_porcentaje' => $detalle['dcto_porcentaje'] ?? 0,
                    'monto_original' => $detalle['total'],
                    'comentario' => $detalle['notas'] ?? '',
                    'det_categoria' => json_encode($detalle['categoria'] ?? []),
                    'opc_categoria' => json_encode($detalle['opc_categoria'] ?? []),
                    'det_movventa_id' => null,
                    'complementos' => json_encode([]),
                    'sucursal_id' => $request->sucursal_id,
                ]);
            }


            // Crear Movcaja
            $movcaja = Movcaja::create([
                'conceptopago_id' => 4,
                'moneda' => $request->moneda,
                'tipocambio' => 3.71,
                'total' => $request->monto_apagar,
                'caja_id' => $request->caja_id,
                'caja' => $caja->numero,
                'turno_id' => $turnoId,
                'json_turno' => json_encode(Turno::find($turnoId)),
                'movimiento_id' => $movimiento->id,
                'sucursal_id' => $request->sucursal_id,
            ]);

            // Procesar Detalles de Movcaja
            $detallesMovCaja = json_decode($request->detalles_movimientos_json, true);

            foreach ($detallesMovCaja as $detalle) {
                $detalleMovcaja = DetMovcaja::create([
                    'movcaja_id' => $movcaja->id,
                    'tipo' => $detalle['tipo'] ?? null,
                    'fechavencimiento' => $detalle['fechavencimiento'] ?? null,
                    'fechapago' => now(),

                    // Medio de pago
                    'mediopago_id' => $detalle['mediopago_id'] ?? null,
                    'mediopago' => $detalle['mediopago_id']
                        ? Mediopago::find($detalle['mediopago_id'])->descripcion ?? ''
                        : '',

                    'numero' => $detalle['numero'] ?? '',

                    // Inicializar campos dinámicos
                    'tarjeta_id' => null,
                    'tarjeta' => '',
                    'banco_id' => null,
                    'banco' => '',
                    'billeteradigital_id' => null,
                    'billeteradigital' => '',
                    'pos_id' => null,
                    'pos' => '',

                    // Monto y otros campos
                    'monto' => $detalle['monto'] ?? 0.00,
                    'comentario' => $detalle['comentario'] ?? '',
                    'situacion' => $detalle['situacion'] ?? 'A',
                    'sucursal_id' => $movcaja->sucursal_id,
                ]);

                // Obtener el medio de pago en minúsculas
                $medioPago = strtolower($detalleMovcaja->mediopago);

                // Si el medio de pago es "Efectivo", saltar la asignación de detalles adicionales
                if ($medioPago === 'efectivo') {
                    continue;
                }

                // Recorrer el JSON de detalles si no es efectivo
                foreach ($detalle['detalles'] ?? [] as $item) {
                    // Banco
                    if (!empty($item['banco'])) {
                        $bancoDescripcion = trim($item['banco']);
                        $detalleMovcaja->update([
                            'banco_id' => Banco::where('descripcion', $bancoDescripcion)->value('id'),
                            'banco' => $bancoDescripcion
                        ]);
                    }

                    // Tarjeta
                    if (!empty($item['tarjeta'])) {
                        $partes = explode(' - ', $item['tarjeta']);
                        if (count($partes) >= 2) {
                            $nombreTarjeta = trim($partes[0]);
                            $tipoTarjeta = strtolower(trim($partes[1]));
                            $tipo = str_contains($tipoTarjeta, 'débito') ? 'D' : 'C';

                            $detalleMovcaja->update([
                                'tarjeta_id' => Tarjetum::where('descripcion', $nombreTarjeta)
                                    ->where('tipo', $tipo)
                                    ->value('id'),
                                'tarjeta' => $item['tarjeta']
                            ]);
                        }
                    }

                    // Billetera Digital
                    if (!empty($item['billetera'])) {
                        $billeteraDescripcion = trim($item['billetera']);
                        $detalleMovcaja->update([
                            'billeteradigital_id' => Billeteradigital::where('descripcion', $billeteraDescripcion)->value('id'),
                            'billeteradigital' => $billeteraDescripcion
                        ]);
                    }

                    // POS
                    if (!empty($item['pos'])) {
                        $posDescripcion = trim($item['pos']);
                        $detalleMovcaja->update([
                            'pos_id' => Pasarelapago::where('descripcion', $posDescripcion)->value('id'),
                            'pos' => $posDescripcion
                        ]);
                    }
                }
            }

            $montoapagar = (float) $request->monto_apagar;
            $montopagado = (float) $request->monto_pagado;
            $total       = (float) $request->total_compromiso;

            $compromisoPago = CompromisoPago::findOrFail($request->compromiso_id);

            if ($total == ($montoapagar + $montopagado)) {
                $compromisoPago->update([
                    'fecha_pago' => now(),
                    'estado' => 'pagado',
                    'forma_pago' => strtolower($detalleMovcaja->mediopago ?? 'Efectivo'),
                ]);
            }


            $facturadorController = new FacturadorController();

            if ($tipodocumento->abreviatura !== 'T') {
                $solicitudFacturacion = $facturadorController->enviarFacturacionElectronica(
                    $movventa,
                    collect($detalles),
                    collect($detalles),
                    $persona,
                    Sucursal::find($request->sucursal_id),
                    Facturador::where('sucursal_id', $request->sucursal_id)->first(),
                    $movcaja,
                    collect($detallesMovCaja),
                    false
                );
                // 🔴 Si hubo un error lógico en la solicitud, cancelar la transacción
                if (isset($solicitudFacturacion['success']) && $solicitudFacturacion['success'] === false) {
                    throw new \Exception($solicitudFacturacion['message'] ?? 'Error en la facturación electrónica');
                }
            }
            $tipoimpresion = RelParametroSucursal::where('sucursal_id', $sucursal->id)
                ->whereHas('parametro', function ($query) {
                    $query->where('descripcion', 'Tipo Impresión Comprobante');
                })
                ->value('valor');
            if ($tipoimpresion == 'Ticket') {
                $pdfUrl = route('movventa.imprimirComprobante', ['id' => $movventa->id]);
            } else {
                $pdfUrl = route('movventa.generarPDF', ['id' => $movventa->id]);
            }

            DB::commit();

            if (isset($solicitudFacturacion)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pago registrado con éxito.',
                    'redirect_url' => route('contrato.compromiso_pago.index', $compromisoPago->contrato_id),
                    'pdf_url' => $pdfUrl,
                    'facturacion_response' => $solicitudFacturacion
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'pdf_url' => $pdfUrl,

                    'message' => 'Pago registrado con éxito.',
                    'redirect_url' => route('contrato.compromiso_pago.index', $compromisoPago->contrato_id)
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $contratoId, $id)
    {
        DB::beginTransaction();

        try {
            $compromisopago = CompromisoPago::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'persona_id'          => 'required|exists:persona,id',
                'ambiente_id'         => 'nullable|exists:ambiente,id',
                'fecha_vencimiento'   => 'required|date',
                'fecha_pago'          => 'nullable|date',
                'numero_cuota'        => 'required|integer|min:1',
                'monto'               => 'required|numeric|min:0',
                'forma_pago'          => 'nullable|string|max:100',
                'estado'              => 'nullable|string|max:50',
                'sucursal_id'         => 'required|exists:sucursal,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            $data['contrato_id'] = $contratoId;

            $compromisopago->update($data);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Compromiso de pago actualizado correctamente.',
                'redirect_url' => route('contrato.compromiso_pago.index', $contratoId)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }
    }

    public function destroy($contratoId, $id)
    {
        try {
            $CompromisoPago = CompromisoPago::findOrFail($id);
            $CompromisoPago->estado = 'anulado';
            $CompromisoPago->save();
            return response()->json([
                'success' => true,
                'message' => 'Compromiso pago anulado correctamente.',
                'redirect_url' => route('contrato.compromisos.index', $contratoId)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }
    }
    private function generateNumero($tipoDocumentoId, $reservar = true)
    {
        // Obtener el tipo de documento
        $tipoDocumento = Tipodocumento::find($tipoDocumentoId);

        if (!$tipoDocumento) {
            return response()->json(['success' => false, 'message' => 'Tipo de documento no encontrado']);
        }

        // Obtener la abreviatura (ej. B, F, etc.)
        $abreviatura = $tipoDocumento->abreviatura ?? 'N/A';

        // Obtener la persona del usuario autenticado
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        // Obtener la primera caja activa de la sucursal del usuario
        $caja = Caja::where('situacion', 'A')
            ->where('sucursal_id', $persona->sucursal_id)
            ->first();

        if (!$caja) {
            return response()->json(['success' => false, 'message' => 'No hay caja activa para esta sucursal']);
        }

        // Formar la serie completa: abreviatura + serie de caja (ej. B001, F001)
        $serie = $abreviatura . str_pad($caja->serie, 3, "0", STR_PAD_LEFT);

        if ($reservar) {
            return CorrelativoService::nextNumeroVenta($persona->sucursal_id, $tipoDocumentoId, $serie);
        }

        return CorrelativoService::peekNumeroVenta($persona->sucursal_id, $tipoDocumentoId, $serie);
    }
    public function historial($id)
    {
        try {

            $movcajaIds = DB::table('movventa as mv')
                ->join('movimiento as m', 'mv.movimiento_id', '=', 'm.id')
                ->join('movcaja as mc', 'm.id', '=', 'mc.movimiento_id')
                ->where('mv.compromiso_pago_id', $id)
                ->where('m.situacion', '!=', 'D')
                ->pluck('mc.id'); // Obtén los IDs de movcaja
            // Obtener los movimientos de caja y sus detalles
            $movcajas = Movcaja::with([
                'detMovcaja.Mediopago',
                'movimiento.tipomovimiento',
                'conceptopago',
                'caja',
                'turno',
                'movimiento.responsable.personamaestro',
                'movimiento.persona.personamaestro'
            ])->whereIn('id', $movcajaIds)->get();

            // Agrupar los detalles si es necesario para la vista
            $detallesAgrupados = $movcajas->flatMap->detMovcaja->groupBy('Mediopago.descripcion')->map(function ($items) {
                return [
                    'mediopago' => $items->first()->Mediopago->descripcion,
                    'monto' => $items->sum('monto'),
                ];
            });

            // Pasar también la nota y mediopago a la vista
            return view('compromiso-pago.historial-ventas', compact('movcajas', 'detallesAgrupados'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
