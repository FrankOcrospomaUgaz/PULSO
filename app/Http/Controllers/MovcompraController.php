<?php

namespace App\Http\Controllers;

use App\Models\Anulacion;
use App\Models\Banco;
use App\Models\Billeteradigital;
use App\Models\Caja;
use App\Models\Categorium;
use App\Models\Conceptopago;
use App\Models\DetMovcaja;
use App\Models\DetMovcompra;
use App\Models\Igv;
use App\Models\Kardex;
use App\Models\Mediopago;
use App\Models\Movcaja;
use App\Models\Movcompra;
use App\Models\Movimiento;
use App\Models\Pasarelapago;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\RelParametrosucursal;
use App\Models\RelProductosucursal;
use App\Models\RelTurnocaja;
use App\Models\Stockproducto;
use App\Models\Sucursal;
use App\Models\Tarjetum;
use App\Models\Tipodocumento;
use App\Models\Turno;
use App\Models\Unidad;
use App\Services\CorrelativoService;
use App\Services\CuentaCobrarpagarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MovcompraController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::findOrFail($user->persona_id);
        $sucursal = Sucursal::findOrFail($persona->sucursal_id);

        $perPage = (int) ($request->get('perPage', 10));
        $search = trim((string) $request->get('search', ''));

        $query = Movcompra::with([
            'movimiento.tipodocumento',
            'movimiento.Persona.personamaestro',
            'movimiento.Responsable.personamaestro',
            'movcaja.conceptopago',
        ])
            ->where('sucursal_id', $sucursal->id)
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('serie', 'like', "%{$search}%")
                    ->orWhere('anio', 'like', "%{$search}%")
                    ->orWhereHas('movimiento', function ($mq) use ($search) {
                        $mq->where('numero', 'like', "%{$search}%")
                            ->orWhere('persona', 'like', "%{$search}%")
                            ->orWhere('usuario', 'like', "%{$search}%")
                            ->orWhere('comentario', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('caja_id')) {
            $query->whereHas('movcaja', fn($q) => $q->where('caja_id', $request->caja_id));
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereHas('movimiento', fn($q) => $q->whereDate('fecha', '>=', $request->fecha_inicio));
        }

        if ($request->filled('fecha_fin')) {
            $query->whereHas('movimiento', fn($q) => $q->whereDate('fecha', '<=', $request->fecha_fin));
        }

        if ($request->filled('tipodocumento_id')) {
            $query->whereHas('movimiento', fn($q) => $q->where('tipodocumento_id', $request->tipodocumento_id));
        }

        if ($request->filled('conceptopago_id')) {
            $query->whereHas('movcaja', fn($q) => $q->where('conceptopago_id', $request->conceptopago_id));
        }

        $movcompras = $query->paginate($perPage)->withQueryString();

        $operaciones = collect();
        $cajas = Caja::where('sucursal_id', $sucursal->id)->orderBy('numero')->get();
        $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 1)->orderBy('nombre')->get();
        $conceptospagos = Conceptopago::where('tipo', 'E')->orderBy('descripcion')->get();

        $totalPaginaSubtotal = $movcompras
            ->filter(fn($mc) => $mc->movimiento->getRawOriginal('situacion') === 'A')
            ->sum('subtotal');
        $totalPaginaIgv = $movcompras
            ->filter(fn($mc) => $mc->movimiento->getRawOriginal('situacion') === 'A')
            ->sum('igv');
        $totalPagina = $movcompras
            ->filter(fn($mc) => $mc->movimiento->getRawOriginal('situacion') === 'A')
            ->sum('total');

        return view('movcompra.index', compact(
            'movcompras',
            'operaciones',
            'totalPagina',
            'totalPaginaIgv',
            'totalPaginaSubtotal',
            'cajas',
            'tiposDocumento',
            'conceptospagos'
        ));
    }

    public function create($personaCreadaId = null)
    {
        $personaCreada = $personaCreadaId ? Persona::find($personaCreadaId) : null;
        $user = Auth::user();
        $persona = Persona::findOrFail($user->persona_id);
        $sucursal = Sucursal::findOrFail($persona->sucursal_id);
        $sucursal_id = $sucursal->id;

        $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 1)->get();
        $tipoDocumentoPredeterminado = $tiposDocumento->firstWhere('abreviatura', 'B') ?? $tiposDocumento->first();

        $serieInput = '000';
        $abreviatura = $tipoDocumentoPredeterminado->abreviatura ?? 'B';
        $serieCompleta = $abreviatura . str_pad($serieInput, 3, '0', STR_PAD_LEFT);
        $correlativo = CorrelativoService::peekNumeroCompra(
            $sucursal_id,
            $tipoDocumentoPredeterminado->id,
            $serieCompleta
        );

        $categorias = Categorium::whereNull('categoria_id')
            ->whereHas('relCategoriasucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id)->where('tipocarta', '!=', 'NINGUNA');
            })
            ->with('categorias')
            ->get();

        $cajas = Caja::where('sucursal_id', $sucursal->id)->orderBy('situacion')->get();
        $responsables = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1);
        })->where('sucursal_id', $sucursal_id)->get();

        $medio_pagos = Mediopago::where('situacion', true)->get();
        $pos = Pasarelapago::where('situacion', true)->get();
        $tarjetas = Tarjetum::where('situacion', true)->get();
        $bancos = Banco::where('situacion', true)->get();
        $billeteras = Billeteradigital::where('situacion', true)->get();

        $tipocambio = RelParametrosucursal::where('sucursal_id', $sucursal_id)
            ->where('parametro_id', 12)
            ->value('valor') ?? 3.71;

        $igvPredeterminadoId = RelParametrosucursal::where('sucursal_id', $sucursal_id)
            ->where('parametro_id', 8)
            ->value('valor');
        $porcentajeIgv = $igvPredeterminadoId ? (float) (Igv::find($igvPredeterminadoId)->porcentaje_igv ?? 18) : 18;

        $movcompra = new Movcompra();

        return view('movcompra.create', compact(
            'movcompra',
            'personaCreada',
            'tiposDocumento',
            'tipoDocumentoPredeterminado',
            'serieInput',
            'correlativo',
            'categorias',
            'cajas',
            'responsables',
            'medio_pagos',
            'pos',
            'tarjetas',
            'bancos',
            'billeteras',
            'sucursal_id',
            'sucursal',
            'tipocambio',
            'porcentajeIgv'
        ));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'sucursal_id' => 'required|exists:sucursal,id',
                'tipodocumento_id' => 'required|exists:tipodocumento,id',
                'serie' => 'required|string|max:255',
                'numero_movimiento_manual' => ['nullable', 'regex:/^\d{1,8}$/'],
                'fecha_compra' => 'required|date',
                'tipo_detalle' => 'required|string|in:DETALLADO,GLOSA',
                'incluye_igv' => 'required|in:S,N',
                'moneda' => 'required|string|in:PEN,USD',
                'tipocambio' => 'required|numeric|min:0',
                'subtotal' => 'required|numeric|min:0',
                'igv' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'afecta_caja' => 'required|in:S,N',
                'afecta_kardex' => 'required|in:S,N',
                'detalles_json' => 'required|json',
                'persona_id' => 'nullable|exists:persona,id',
                'responsable_id' => 'required|exists:persona,id',
                'comentario' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            if ($request->afecta_caja === 'S') {
                $validatorCaja = Validator::make($request->all(), [
                    'caja_id' => 'required|exists:caja,id',
                    'detalles_movimientos_json' => 'required|json',
                ]);
                if ($validatorCaja->fails()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validatorCaja->errors(),
                    ], 422);
                }
            }

            $usuario = Auth::user();
            $responsable = Persona::findOrFail($request->responsable_id);
            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;
            $tipodocumento = Tipodocumento::findOrFail($request->tipodocumento_id);
            $serieCompleta = $tipodocumento->abreviatura . str_pad($request->serie, 3, '0', STR_PAD_LEFT);

            $numeroMovimiento = CorrelativoService::nextNumeroCompra(
                $request->sucursal_id,
                $request->tipodocumento_id,
                $serieCompleta
            );
            $numeroManual = trim((string) $request->input('numero_movimiento_manual', ''));
            if ($numeroManual !== '' && $numeroManual !== '0') {
                $numeroMovimiento = str_pad((string) ((int) $numeroManual), 8, '0', STR_PAD_LEFT);
            }

            $detallesMovCaja = $request->afecta_caja === 'S'
                ? json_decode($request->detalles_movimientos_json, true)
                : [];
            $tipoPago = $this->resolverTipoPago($detallesMovCaja);

            $movimiento = Movimiento::create([
                'numero' => $numeroMovimiento,
                'fecha' => $request->fecha_compra,
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $responsable->id,
                'responsable' => trim($responsable->nombres . ' ' . $responsable->apellidos),
                'persona_id' => $request->persona_id,
                'persona' => $persona ? trim($persona->nombres . ' ' . $persona->apellidos) : 'CLIENTES VARIOS',
                'comentario' => $request->comentario ?? '',
                'situacion' => 'A',
                'tipomovimiento_id' => 1,
                'tipodocumento_id' => $request->tipodocumento_id,
                'sucursal_id' => $request->sucursal_id,
            ]);

            $movcompra = Movcompra::create([
                'serie' => $serieCompleta,
                'anio' => date('Y', strtotime($request->fecha_compra)),
                'tipo_detalle' => $request->tipo_detalle,
                'incluye_igv' => $request->incluye_igv,
                'tipo_pago' => $tipoPago,
                'afecta_caja' => $request->afecta_caja,
                'moneda' => $request->moneda,
                'tipocambio' => $request->tipocambio,
                'subtotal' => $request->subtotal,
                'igv' => $request->igv,
                'total' => $request->total,
                'afecta_kardex' => $request->afecta_kardex,
                'movimiento_id' => $movimiento->id,
                'sucursal_id' => $request->sucursal_id,
            ]);

            $detalles = json_decode($request->detalles_json, true);
            $this->guardarDetallesCompra($movcompra, $detalles, $request);

            if ($request->afecta_kardex === 'S') {
                $this->actualizarStockYKardex($movcompra, $detalles, $movimiento, $usuario, $request);
            }

            if ($request->afecta_caja === 'S') {
                $this->registrarMovimientoCaja($movcompra, $movimiento, $request, $usuario, $responsable, $persona, $detallesMovCaja);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compra registrada con éxito.',
                'redirect_url' => route('movcompra.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $movcompra = Movcompra::with(['detMovcompras.unidad', 'detMovcompras.igv', 'movimiento.Persona.personamaestro'])
            ->findOrFail($id);

        $user = Auth::user();
        $sucursal_id = $user->persona->sucursal_id;
        $sucursal = Sucursal::findOrFail($sucursal_id);

        $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 1)->get();
        $tipoDocumentoPredeterminado = $tiposDocumento->firstWhere('id', $movcompra->movimiento->tipodocumento_id);
        $serieInput = substr($movcompra->serie, 1);
        $correlativo = str_pad((string) $movcompra->movimiento->numero, 8, '0', STR_PAD_LEFT);

        $categorias = Categorium::whereNull('categoria_id')
            ->whereHas('relCategoriasucursal', function ($query) use ($sucursal_id) {
                $query->where('sucursal_id', $sucursal_id)->where('tipocarta', '!=', 'NINGUNA');
            })
            ->with('categorias')
            ->get();

        $cajas = Caja::where('sucursal_id', $sucursal_id)->orderBy('situacion')->get();
        $responsables = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1);
        })->where('sucursal_id', $sucursal_id)->get();

        $medio_pagos = Mediopago::where('situacion', true)->get();
        $pos = Pasarelapago::where('situacion', true)->get();
        $tarjetas = Tarjetum::where('situacion', true)->get();
        $bancos = Banco::where('situacion', true)->get();
        $billeteras = Billeteradigital::where('situacion', true)->get();

        $tipocambio = $movcompra->tipocambio;
        $porcentajeIgv = RelParametrosucursal::where('sucursal_id', $sucursal_id)
            ->where('parametro_id', 8)
            ->value('valor');
        $porcentajeIgv = $porcentajeIgv ? (float) (Igv::find($porcentajeIgv)->porcentaje_igv ?? 18) : 18;

        $detallesMovimientos = [];
        $movcaja = $movcompra->movcaja;
        if ($movcaja) {
            $detallesMovimientos = $movcaja->detMovcaja->map(function ($det) {
                return [
                    'tipo' => $det->tipo,
                    'monto' => (float) $det->monto,
                    'mediopago_id' => $det->mediopago_id,
                    'medio_pago' => $det->mediopago,
                    'notas' => $det->comentario,
                    'fecha_vencimiento' => $det->fechavencimiento,
                    'detalles' => array_filter([
                        $det->banco ? ['banco' => $det->banco] : null,
                        $det->tarjeta ? ['tarjeta' => $det->tarjeta] : null,
                        $det->billeteradigital ? ['billetera' => $det->billeteradigital] : null,
                        $det->pos ? ['pos' => $det->pos] : null,
                    ]),
                ];
            })->values()->all();
        }

        return view('movcompra.edit', compact(
            'movcompra',
            'tiposDocumento',
            'tipoDocumentoPredeterminado',
            'serieInput',
            'correlativo',
            'categorias',
            'cajas',
            'responsables',
            'medio_pagos',
            'pos',
            'tarjetas',
            'bancos',
            'billeteras',
            'sucursal_id',
            'sucursal',
            'tipocambio',
            'porcentajeIgv',
            'detallesMovimientos'
        ));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $movcompra = Movcompra::with('movimiento')->findOrFail($id);

            if ($movcompra->movimiento->getRawOriginal('situacion') !== 'A') {
                return response()->json([
                    'success' => false,
                    'errors' => 'No se puede editar una compra anulada.',
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'fecha_compra' => 'required|date',
                'tipo_detalle' => 'required|string|in:DETALLADO,GLOSA',
                'incluye_igv' => 'required|in:S,N',
                'moneda' => 'required|string|in:PEN,USD',
                'tipocambio' => 'required|numeric|min:0',
                'subtotal' => 'required|numeric|min:0',
                'igv' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'afecta_kardex' => 'required|in:S,N',
                'detalles_json' => 'required|json',
                'persona_id' => 'nullable|exists:persona,id',
                'responsable_id' => 'required|exists:persona,id',
                'comentario' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $responsable = Persona::findOrFail($request->responsable_id);
            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;

            $movcompra->movimiento->update([
                'fecha' => $request->fecha_compra,
                'responsable_id' => $responsable->id,
                'responsable' => trim($responsable->nombres . ' ' . $responsable->apellidos),
                'persona_id' => $request->persona_id,
                'persona' => $persona ? trim($persona->nombres . ' ' . $persona->apellidos) : 'CLIENTES VARIOS',
                'comentario' => $request->comentario ?? '',
            ]);

            $movcompra->update([
                'tipo_detalle' => $request->tipo_detalle,
                'incluye_igv' => $request->incluye_igv,
                'moneda' => $request->moneda,
                'tipocambio' => $request->tipocambio,
                'subtotal' => $request->subtotal,
                'igv' => $request->igv,
                'total' => $request->total,
                'afecta_kardex' => $request->afecta_kardex,
            ]);

            DetMovcompra::where('movcompra_id', $movcompra->id)->delete();
            $detalles = json_decode($request->detalles_json, true);
            $this->guardarDetallesCompra($movcompra, $detalles, $request);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compra actualizada con éxito.',
                'redirect_url' => route('movcompra.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function anular($id)
    {
        DB::beginTransaction();

        try {
            $movcompra = Movcompra::with(['detMovcompras', 'movcaja', 'movimiento'])->findOrFail($id);
            $movimiento = $movcompra->movimiento;

            if ($movimiento->getRawOriginal('situacion') !== 'A') {
                return response()->json([
                    'success' => false,
                    'errors' => 'La compra ya está anulada.',
                ], 422);
            }

            $usuario = Auth::user();
            $movimiento->update(['situacion' => 'D']);

            Anulacion::create([
                'movimiento_id' => $movimiento->id,
                'usuario_id' => $usuario->id,
                'fecha' => now()->toDateTimeString(),
                'motivo' => request('motivo') ?? 'Anulación de compra',
            ]);

            if ($movcompra->getRawOriginal('afecta_kardex') === 'S') {
                foreach ($movcompra->detMovcompras as $detalle) {
                    if (!$detalle->producto_id) {
                        continue;
                    }

                    $stockProducto = Stockproducto::where('producto_id', $detalle->producto_id)
                        ->where('unidadbase_id', $detalle->unidad_id)
                        ->where('sucursal_id', $movcompra->sucursal_id)
                        ->first();

                    if ($stockProducto) {
                        $stockProducto->stockbase -= $detalle->cantidad;
                        $stockProducto->save();
                    }

                    Kardex::where('producto_id', $detalle->producto_id)
                        ->where('movimiento_id', $movimiento->id)
                        ->update(['situacion' => 'D']);
                }
            }

            $movcaja = $movcompra->movcaja;
            if ($movcaja && $movcaja->movimiento) {
                $movcaja->movimiento->update(['situacion' => 'D']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compra anulada correctamente.',
                'redirect_url' => route('movcompra.index'),
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
        return redirect()->route('movcompra.verDetalles', $id);
    }

    public function verDetalles($id)
    {
        $movcompra = Movcompra::with([
            'detMovcompras.unidad',
            'detMovcompras.igv',
            'detMovcompras.producto',
            'movimiento.tipodocumento',
            'movimiento.Persona.personamaestro',
            'movimiento.Responsable.personamaestro',
            'movcaja.conceptopago',
            'movcaja.detMovcaja',
        ])->findOrFail($id);

        return view('movcompra.detalles', compact('movcompra'));
    }

    public function destroy($id)
    {
        return $this->anular($id);
    }

    private function resolverTipoPago(array $detallesMovCaja): string
    {
        foreach ($detallesMovCaja as $detalle) {
            if (strcasecmp((string) ($detalle['tipo'] ?? ''), 'Deuda') === 0) {
                return 'CREDITO';
            }
        }

        return 'CONTADO';
    }

    private function guardarDetallesCompra(Movcompra $movcompra, array $detalles, Request $request): void
    {
        $sucursalId = $request->sucursal_id;
        $igvPredeterminadoId = RelParametrosucursal::where('sucursal_id', $sucursalId)
            ->where('parametro_id', 8)
            ->value('valor');
        $igvPredeterminado = $igvPredeterminadoId ? Igv::find($igvPredeterminadoId) : null;

        foreach ($detalles as $detalle) {
            $productoId = $detalle['producto_id'] ?? null;
            $relProductoSucursal = $productoId
                ? RelProductosucursal::where('producto_id', $productoId)
                    ->where('sucursal_id', $sucursalId)
                    ->first()
                : null;

            $igv_id = $relProductoSucursal?->igv_id ?? $igvPredeterminado?->id;
            $igvClase = $igv_id ? Igv::find($igv_id) : $igvPredeterminado;
            $unidad = null;

            if (!empty($detalle['unidad_id'])) {
                $unidad = Unidad::find($detalle['unidad_id']);
            } elseif (!empty($detalle['unidad'])) {
                $unidad = Unidad::where('descripcion', $detalle['unidad'])->first();
            }

            $cantidad = (float) ($detalle['cantidad'] ?? 0);
            $precioUnitario = (float) ($detalle['precio_unitario'] ?? 0);
            $importe = $cantidad * $precioUnitario;

            DetMovcompra::create([
                'movcompra_id' => $movcompra->id,
                'tipo_detalle' => $request->tipo_detalle,
                'codigo' => $detalle['codigo'] ?? '',
                'descripcion' => $detalle['descripcion'] ?? '',
                'producto_id' => $productoId,
                'json_producto' => json_encode($detalle, JSON_UNESCAPED_UNICODE),
                'unidad_id' => $unidad?->id ?? 1,
                'igv_id' => $igv_id,
                'cantidad' => $cantidad,
                'monto' => $importe,
                'comentario' => $detalle['notas'] ?? '',
                'situacion' => 'E',
                'sucursal_id' => $sucursalId,
            ]);
        }
    }

    private function actualizarStockYKardex(
        Movcompra $movcompra,
        array $detalles,
        Movimiento $movimiento,
        $usuario,
        Request $request
    ): void {
        foreach ($detalles as $detalle) {
            $productoId = $detalle['producto_id'] ?? null;
            if (!$productoId) {
                continue;
            }

            $unidad = null;
            if (!empty($detalle['unidad_id'])) {
                $unidad = Unidad::find($detalle['unidad_id']);
            } elseif (!empty($detalle['unidad'])) {
                $unidad = Unidad::where('descripcion', $detalle['unidad'])->first();
            }
            $unidadId = $unidad?->id ?? 1;
            $cantidad = (float) ($detalle['cantidad'] ?? 0);
            $precioUnitario = (float) ($detalle['precio_unitario'] ?? 0);

            $stockProducto = Stockproducto::firstOrNew([
                'producto_id' => $productoId,
                'unidadbase_id' => $unidadId,
                'sucursal_id' => $request->sucursal_id,
            ]);

            $stockAnterior = $stockProducto->stockbase ?? 0;
            $stockProducto->stockbase = $stockAnterior + $cantidad;
            $stockProducto->save();

            $producto = Producto::find($productoId);

            Kardex::create([
                'producto_id' => $productoId,
                'json_producto' => json_encode($producto),
                'unidad_id' => $unidadId,
                'json_listaunidad' => json_encode(['unidad_nombre' => $unidad?->descripcion ?? '']),
                'cantidad' => $cantidad,
                'unidadbase_id' => $unidadId,
                'cantidadbase' => $cantidad,
                'preciounitario' => $precioUnitario,
                'moneda' => $request->moneda,
                'tipocambio' => $request->tipocambio,
                'total' => $cantidad * $precioUnitario,
                'fecha' => $request->fecha_compra,
                'situacion' => 'A',
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'movimiento_id' => $movimiento->id,
                'tipomovimiento_id' => 1,
                'tipodocumento_id' => $request->tipodocumento_id,
                'sucursal_id' => $request->sucursal_id,
                'stockanterior' => $stockAnterior,
                'stockactual' => $stockProducto->stockbase,
            ]);
        }
    }

    private function registrarMovimientoCaja(
        Movcompra $movcompra,
        Movimiento $movimientoPadre,
        Request $request,
        $usuario,
        Persona $responsable,
        ?Persona $persona,
        array $detallesMovCaja
    ): void {
        $caja = Caja::findOrFail($request->caja_id);
        $turnoCaja = RelTurnocaja::conCajaActiva($request->caja_id)->first();
        $turnoId = $turnoCaja?->movcajainicio?->turno_id ?? Turno::where('sucursal_id', $request->sucursal_id)->value('id');
        $turno = $turnoId ? Turno::find($turnoId) : null;

        $movimientoCaja = Movimiento::create([
            'numero' => $this->generateNumeroMovcaja($request->sucursal_id, $request->caja_id),
            'fecha' => $request->fecha_movcaja ?? $request->fecha_compra,
            'usuario_id' => $usuario->id,
            'usuario' => $usuario->name,
            'responsable_id' => $responsable->id,
            'responsable' => trim($responsable->nombres . ' ' . $responsable->apellidos),
            'persona_id' => $request->persona_id,
            'persona' => $persona ? trim($persona->nombres . ' ' . $persona->apellidos) : 'CLIENTES VARIOS',
            'comentario' => $request->comentario ?? '',
            'situacion' => 'A',
            'tipomovimiento_id' => 4,
            'tipodocumento_id' => 8,
            'sucursal_id' => $request->sucursal_id,
            'movimiento_id' => $movimientoPadre->id,
        ]);

        $concepto = Conceptopago::find(5) ?? Conceptopago::find(33) ?? Conceptopago::where('tipo', 'E')->first();

        $movcaja = Movcaja::create([
            'conceptopago_id' => $concepto->id,
            'moneda' => $request->moneda,
            'tipocambio' => $request->tipocambio,
            'total' => $request->total,
            'caja_id' => $request->caja_id,
            'caja' => $caja->numero,
            'turno_id' => $turnoId ?? 0,
            'json_turno' => $turno ? json_encode($turno->toArray(), JSON_UNESCAPED_UNICODE) : json_encode([]),
            'movimiento_id' => $movimientoCaja->id,
            'sucursal_id' => $request->sucursal_id,
        ]);

        foreach ($detallesMovCaja as $detalle) {
            $detalleMovcaja = DetMovcaja::create([
                'movcaja_id' => $movcaja->id,
                'tipo' => $detalle['tipo'] ?? 'Pagado',
                'fechavencimiento' => $detalle['fechavencimiento'] ?? $detalle['fecha_vencimiento'] ?? null,
                'fechapago' => now(),
                'mediopago_id' => $detalle['mediopago_id'] ?? null,
                'mediopago' => !empty($detalle['mediopago_id'])
                    ? (Mediopago::find($detalle['mediopago_id'])->descripcion ?? ($detalle['medio_pago'] ?? ''))
                    : ($detalle['medio_pago'] ?? ''),
                'numero' => $detalle['numero'] ?? '',
                'tarjeta_id' => null,
                'tarjeta' => '',
                'banco_id' => null,
                'banco' => '',
                'billeteradigital_id' => null,
                'billeteradigital' => '',
                'pos_id' => null,
                'pos' => '',
                'monto' => $detalle['monto'] ?? 0,
                'comentario' => $detalle['comentario'] ?? ($detalle['notas'] ?? ''),
                'situacion' => 'A',
                'sucursal_id' => $request->sucursal_id,
            ]);

            $medioPago = strtolower((string) $detalleMovcaja->mediopago);
            if ($medioPago === 'efectivo') {
                continue;
            }

            foreach ($detalle['detalles'] ?? [] as $item) {
                if (!empty($item['banco'])) {
                    $detalleMovcaja->update([
                        'banco_id' => Banco::where('descripcion', trim($item['banco']))->value('id'),
                        'banco' => trim($item['banco']),
                    ]);
                }
                if (!empty($item['tarjeta'])) {
                    $partes = explode(' - ', $item['tarjeta']);
                    if (count($partes) >= 2) {
                        $nombreTarjeta = trim($partes[0]);
                        $tipoTarjeta = strtolower(trim($partes[1]));
                        $tipo = str_contains($tipoTarjeta, 'débito') ? 'D' : 'C';
                        $detalleMovcaja->update([
                            'tarjeta_id' => Tarjetum::where('descripcion', $nombreTarjeta)->where('tipo', $tipo)->value('id'),
                            'tarjeta' => $item['tarjeta'],
                        ]);
                    }
                }
                if (!empty($item['billetera'])) {
                    $detalleMovcaja->update([
                        'billeteradigital_id' => Billeteradigital::where('descripcion', trim($item['billetera']))->value('id'),
                        'billeteradigital' => trim($item['billetera']),
                    ]);
                }
                if (!empty($item['pos'])) {
                    $detalleMovcaja->update([
                        'pos_id' => Pasarelapago::where('descripcion', trim($item['pos']))->value('id'),
                        'pos' => trim($item['pos']),
                    ]);
                }
            }
        }

        CuentaCobrarpagarService::sincronizarDesdeMovcajaId($movcaja->id);
    }

    private function generateNumeroMovcaja(int $sucursalId, int $cajaId): string
    {
        $ultimo = Movimiento::where('sucursal_id', $sucursalId)
            ->where('tipomovimiento_id', 4)
            ->whereHas('movcaja', fn($q) => $q->where('caja_id', $cajaId))
            ->orderByDesc('numero')
            ->value('numero');

        return str_pad((string) (((int) $ultimo) + 1), 8, '0', STR_PAD_LEFT);
    }
}
