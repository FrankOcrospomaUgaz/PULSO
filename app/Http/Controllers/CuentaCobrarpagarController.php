<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Conceptopago;
use App\Models\CuentaCobrarpagar;
use App\Models\DetCuentaCobrarpagar;
use App\Models\DetMovcaja;
use App\Models\Mediopago;
use App\Models\Movcaja;
use App\Models\Movimiento;
use App\Models\Persona;
use App\Models\RelTurnocaja;
use App\Models\Sucursal;
use App\Models\Tipodocumento;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use App\Services\CuentaCobrarpagarService;

class CuentaCobrarpagarController extends Controller
{
    public function cobrar(Request $request)
    {
        return $this->index($request, 'COBRAR');
    }

    public function pagar(Request $request)
    {
        return $this->index($request, 'PAGAR');
    }

    public function pago(CuentaCobrarpagar $cuenta)
    {
        $this->autorizarCuenta($cuenta);
        $cuenta->loadMissing('movcaja.movimiento.Persona.personamaestro');

        $tipoConcepto = $cuenta->tipo === 'COBRAR' ? 'I' : 'E';
        $conceptos = Conceptopago::where('tipo', $tipoConcepto)->orderBy('descripcion')->get();
        $medioPagos = Mediopago::where('situacion', true)->orderBy('descripcion')->get();
        $cajas = Caja::where('sucursal_id', $cuenta->sucursal_id)->orderBy('numero')->get();
        $responsable = Auth::user()->persona;
        $responsable?->loadMissing('personamaestro');

        return view('cuenta-cobrarpagar.pago', compact('cuenta', 'conceptos', 'medioPagos', 'cajas', 'responsable'));
    }

    public function guardarPago(Request $request, CuentaCobrarpagar $cuenta)
    {
        $this->autorizarCuenta($cuenta);

        $request->validate([
            'caja_id' => 'required|exists:caja,id',
            'conceptopago_id' => 'nullable|exists:conceptopago,id',
            'mediopago_id' => 'required|exists:mediopago,id',
            'monto' => 'required|numeric|min:0.01',
            'numero' => 'nullable|string|max:255',
            'comentario' => 'nullable|string',
        ]);

        if ((float) $request->monto > $cuenta->restante) {
            return response()->json([
                'success' => false,
                'errors' => 'El monto no puede ser mayor al restante de la cuenta.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $usuario = Auth::user();
            $responsable = $usuario->persona;
            $persona = $cuenta->movcaja?->movimiento?->Persona;
            $caja = Caja::findOrFail($request->caja_id);
            $concepto = $cuenta->tipo === 'COBRAR'
                ? $this->conceptoPagoCuentaPorCobrar()
                : Conceptopago::findOrFail($request->conceptopago_id);
            $medioPago = Mediopago::findOrFail($request->mediopago_id);
            $tipoDocumentoId = $cuenta->tipo === 'COBRAR' ? 7 : 8;
            $turnoCaja = RelTurnocaja::conCajaActiva($caja->id)->first();
            $turnoId = $turnoCaja?->movcajainicio?->turno_id ?? Turno::where('sucursal_id', $cuenta->sucursal_id)->value('id');
            $turno = $turnoId ? Turno::find($turnoId) : null;

            $movimiento = Movimiento::create([
                'numero' => $this->generarNumero($cuenta->sucursal_id, $caja->id),
                'fecha' => now(),
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'persona_id' => $persona?->id,
                'persona' => $persona ? trim($persona->nombres . ' ' . $persona->apellidos) : 'N/A',
                'responsable_id' => $responsable?->id,
                'responsable' => $responsable ? trim($responsable->nombres . ' ' . $responsable->apellidos) : 'N/A',
                'comentario' => $request->comentario ?? '',
                'situacion' => 'A',
                'tipomovimiento_id' => 4,
                'tipodocumento_id' => $tipoDocumentoId,
                'sucursal_id' => $cuenta->sucursal_id,
            ]);

            $movcaja = Movcaja::create([
                'conceptopago_id' => $concepto->id,
                'moneda' => $cuenta->moneda,
                'tipocambio' => $cuenta->tipocambio,
                'total' => $request->monto,
                'caja_id' => $caja->id,
                'caja' => $caja->numero,
                'turno_id' => $turnoId ?? 0,
                'json_turno' => $turno ? json_encode($turno->toArray(), JSON_UNESCAPED_UNICODE) : json_encode([]),
                'movimiento_id' => $movimiento->id,
                'sucursal_id' => $cuenta->sucursal_id,
            ]);

            DetMovcaja::create([
                'movcaja_id' => $movcaja->id,
                'tipo' => 'Pagado',
                'fechavencimiento' => null,
                'fechapago' => now(),
                'mediopago_id' => $medioPago->id,
                'mediopago' => $medioPago->descripcion,
                'numero' => $request->numero ?? '',
                'tarjeta_id' => null,
                'tarjeta' => '',
                'banco_id' => null,
                'banco' => '',
                'billeteradigital_id' => null,
                'billeteradigital' => '',
                'pos_id' => null,
                'pos' => '',
                'monto' => $request->monto,
                'comentario' => $request->comentario ?? '',
                'situacion' => 'A',
                'sucursal_id' => $cuenta->sucursal_id,
            ]);

            DetCuentaCobrarpagar::create([
                'cuenta_cobrarpagar_id' => $cuenta->id,
                'movcaja_id' => $movcaja->id,
                'situacion' => 'A',
                'sucursal_id' => $cuenta->sucursal_id,
            ]);

            $this->actualizarSaldo($cuenta->fresh());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado correctamente.',
                'redirect_url' => $cuenta->tipo === 'COBRAR' ? route('cuentas.cobrar.index') : route('cuentas.pagar.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => 'Error al registrar el pago: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function conceptoPagoCuentaPorCobrar(): Conceptopago
    {
        return Conceptopago::firstOrCreate(
            [
                'descripcion' => 'Pago de cuenta por cobrar',
                'tipo' => 'I',
            ],
            [
                'restringido' => false,
            ]
        );
    }

    public function pdf(Request $request, string $tipo)
    {
        [$cuentas, $resumen] = $this->buscarCuentas($request, strtoupper($tipo), false);

        return PDF::loadView('cuenta-cobrarpagar.pdf', [
            'cuentas' => $cuentas,
            'resumen' => $resumen,
            'tipo' => strtoupper($tipo),
        ])->stream('cuentas-' . strtolower($tipo) . '.pdf');
    }

    public function excel(Request $request, string $tipo)
    {
        [$cuentas] = $this->buscarCuentas($request, strtoupper($tipo), false);
        $filename = 'cuentas-' . strtolower($tipo) . '.csv';

        return response()->streamDownload(function () use ($cuentas) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Numero', 'Estado', 'Fecha', 'Vencimiento', 'Origen', 'Concepto', 'Moneda', 'Total', 'Pagado', 'Restante', 'Usuario', 'Persona', 'Situacion']);
            foreach ($cuentas as $cuenta) {
                fputcsv($out, [
                    $cuenta->numero,
                    $cuenta->estado,
                    optional($cuenta->fecha)->format('Y-m-d H:i:s'),
                    optional($cuenta->fechavencimiento)->format('Y-m-d H:i:s'),
                    $this->origenCuenta($cuenta),
                    $cuenta->movcaja?->conceptopago?->descripcion,
                    $cuenta->moneda,
                    number_format($cuenta->total, 2, '.', ''),
                    number_format((float) $cuenta->totalpagado, 2, '.', ''),
                    number_format($cuenta->restante, 2, '.', ''),
                    $cuenta->movcaja?->movimiento?->usuario,
                    $this->personaCuenta($cuenta),
                    $cuenta->situacion_texto,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function index(Request $request, string $tipo)
    {
        [$cuentas, $resumen, $filtros] = $this->buscarCuentas($request, $tipo, true);

        return view('cuenta-cobrarpagar.index', compact('cuentas', 'resumen', 'filtros', 'tipo'));
    }

    private function buscarCuentas(Request $request, string $tipo, bool $paginate): array
    {
        $persona = Persona::with('sucursal')->findOrFail(Auth::user()->persona_id);
        $sucursal = $persona->sucursal;
        $sucursalIds = Sucursal::where('empresa_id', $sucursal->empresa_id)->pluck('id');

        $query = CuentaCobrarpagar::query()
            ->select('cuenta_cobrarpagar.*')
            ->whereIn('cuenta_cobrarpagar.sucursal_id', $sucursalIds)
            ->where('cuenta_cobrarpagar.tipo', $tipo);

        $this->aplicarFiltros($query, $request);

        $resumen = $this->resumenDesdeQuery(clone $query);

        $listQuery = (clone $query)->with([
            'sucursal:id,nombrecomercial',
            'movcaja:id,total,caja_id,conceptopago_id,movimiento_id,caja',
            'movcaja.caja:id,numero',
            'movcaja.conceptopago:id,descripcion',
            'movcaja.movimiento:id,numero,usuario,persona,persona_id,tipodocumento_id',
            'movcaja.movimiento.Persona:id,personamaestro_id',
            'movcaja.movimiento.Persona.personamaestro:id,nrodoc',
            'movcaja.movimiento.tipodocumento:id,nombre,tipomovimiento_id',
            'movcaja.movimiento.tipodocumento.tipomovimiento:id,descripcion',
        ]);

        $cuentas = $paginate
            ? $listQuery->latest('cuenta_cobrarpagar.id')->paginate($request->input('perPage', 10))->appends($request->all())
            : $listQuery->latest('cuenta_cobrarpagar.id')->get();

        $filtros = [
            'cajas' => Caja::whereIn('sucursal_id', $sucursalIds)->orderBy('numero')->get(),
            'sucursales' => Sucursal::whereIn('id', $sucursalIds)->orderBy('nombrecomercial')->get(),
            'conceptos' => Conceptopago::orderBy('descripcion')->get(),
        ];

        return [$cuentas, $resumen, $filtros];
    }

    private function aplicarFiltros($query, Request $request): void
    {
        $query
            ->when($request->filled('caja_id'), fn($q) => $q->whereHas('movcaja', fn($m) => $m->where('caja_id', $request->caja_id)))
            ->when($request->filled('sucursal_id'), fn($q) => $q->where('cuenta_cobrarpagar.sucursal_id', $request->sucursal_id))
            ->when($request->filled('estado'), fn($q) => $q->whereIn('cuenta_cobrarpagar.estado', (array) $request->estado))
            ->when($request->filled('situacion'), fn($q) => $q->where('cuenta_cobrarpagar.situacion', $request->situacion))
            ->when($request->filled('conceptopago_id'), fn($q) => $q->whereHas('movcaja', fn($m) => $m->where('conceptopago_id', $request->conceptopago_id)))
            ->when($request->filled('fechaInicio'), fn($q) => $q->where('cuenta_cobrarpagar.fecha', '>=', Carbon::parse($request->fechaInicio)->startOfDay()))
            ->when($request->filled('fechaFin'), fn($q) => $q->where('cuenta_cobrarpagar.fecha', '<=', Carbon::parse($request->fechaFin)->endOfDay()))
            ->when($request->filled('fechaProgramada'), function ($q) use ($request) {
                $fecha = Carbon::parse($request->fechaProgramada);
                $q->whereBetween('cuenta_cobrarpagar.fechavencimiento', [$fecha->copy()->startOfDay(), $fecha->copy()->endOfDay()]);
            })
            ->when($request->filled('estado_cuotas'), function ($q) use ($request) {
                $hoy = today()->startOfDay();
                if ($request->estado_cuotas === 'VENCIDAS') {
                    $q->where('cuenta_cobrarpagar.fechavencimiento', '<', $hoy)->where('cuenta_cobrarpagar.estado', '!=', 'PAGADO');
                }
                if ($request->estado_cuotas === 'ATIEMPO') {
                    $q->where('cuenta_cobrarpagar.fechavencimiento', '>=', $hoy)->where('cuenta_cobrarpagar.estado', '!=', 'PAGADO');
                }
            })
            ->when($request->filled('persona'), function ($q) use ($request) {
                $search = $request->persona;
                $q->whereHas('movcaja.movimiento', function ($m) use ($search) {
                    $m->where('persona', 'ILIKE', "%{$search}%")
                        ->orWhereHas('Persona.personamaestro', fn($p) => $p->where('nrodoc', 'ILIKE', "%{$search}%"));
                });
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($inner) use ($search) {
                    $inner->where('cuenta_cobrarpagar.numero', 'ILIKE', "%{$search}%")
                        ->orWhere('cuenta_cobrarpagar.estado', 'ILIKE', "%{$search}%")
                        ->orWhereHas('movcaja.movimiento', fn($m) => $m->where('numero', 'ILIKE', "%{$search}%")->orWhere('usuario', 'ILIKE', "%{$search}%"))
                        ->orWhereHas('movcaja.conceptopago', fn($c) => $c->where('descripcion', 'ILIKE', "%{$search}%"));
                });
            });
    }

    private function resumenDesdeQuery($query): array
    {
        $hoy = today()->startOfDay()->toDateTimeString();

        $row = $query
            ->join('movcaja as resumen_movcaja', 'cuenta_cobrarpagar.movcaja_id', '=', 'resumen_movcaja.id')
            ->select(DB::raw("
                COALESCE(SUM(CASE
                    WHEN cuenta_cobrarpagar.estado <> 'PAGADO'
                    THEN GREATEST(resumen_movcaja.total - cuenta_cobrarpagar.totalpagado, 0)
                    ELSE 0
                END), 0) AS total,
                COUNT(CASE WHEN cuenta_cobrarpagar.estado <> 'PAGADO' THEN 1 END) AS documentos,
                COALESCE(SUM(CASE
                    WHEN cuenta_cobrarpagar.estado <> 'PAGADO'
                        AND cuenta_cobrarpagar.fechavencimiento >= ?
                    THEN GREATEST(resumen_movcaja.total - cuenta_cobrarpagar.totalpagado, 0)
                    ELSE 0
                END), 0) AS atiempo,
                COUNT(CASE
                    WHEN cuenta_cobrarpagar.estado <> 'PAGADO'
                        AND cuenta_cobrarpagar.fechavencimiento >= ?
                    THEN 1
                END) AS atiempo_documentos,
                COALESCE(SUM(CASE
                    WHEN cuenta_cobrarpagar.estado <> 'PAGADO'
                        AND cuenta_cobrarpagar.fechavencimiento < ?
                    THEN GREATEST(resumen_movcaja.total - cuenta_cobrarpagar.totalpagado, 0)
                    ELSE 0
                END), 0) AS vencidas,
                COUNT(CASE
                    WHEN cuenta_cobrarpagar.estado <> 'PAGADO'
                        AND cuenta_cobrarpagar.fechavencimiento < ?
                    THEN 1
                END) AS vencidas_documentos
            "))
            ->addBinding([$hoy, $hoy, $hoy, $hoy], 'select')
            ->first();

        $attrs = $row ? $row->getAttributes() : [];

        return [
            'total' => (float) ($attrs['total'] ?? 0),
            'documentos' => (int) ($attrs['documentos'] ?? 0),
            'atiempo' => (float) ($attrs['atiempo'] ?? 0),
            'atiempo_documentos' => (int) ($attrs['atiempo_documentos'] ?? 0),
            'vencidas' => (float) ($attrs['vencidas'] ?? 0),
            'vencidas_documentos' => (int) ($attrs['vencidas_documentos'] ?? 0),
        ];
    }

    private function sincronizarCuentas(): void
    {
        DetMovcaja::where('tipo', 'Deuda')
            ->pluck('movcaja_id')
            ->unique()
            ->each(fn($movcajaId) => CuentaCobrarpagarService::sincronizarDesdeMovcajaId((int) $movcajaId));
    }

    private function actualizarSaldo(CuentaCobrarpagar $cuenta): void
    {
        $pagadoInicial = DetMovcaja::where('movcaja_id', $cuenta->movcaja_id)
            ->where('tipo', 'Pagado')
            ->sum('monto');

        $pagadoDetalles = DetCuentaCobrarpagar::where('cuenta_cobrarpagar_id', $cuenta->id)
            ->whereHas('movcaja.movimiento', fn($q) => $q->where('situacion', 'A'))
            ->with('movcaja')
            ->get()
            ->sum(fn($detalle) => (float) ($detalle->movcaja->total ?? 0));

        $totalPagado = round((float) $pagadoInicial + (float) $pagadoDetalles, 2);
        $total = (float) ($cuenta->movcaja->total ?? Movcaja::where('id', $cuenta->movcaja_id)->value('total') ?? 0);

        $cuenta->update([
            'totalpagado' => $totalPagado,
            'estado' => $totalPagado <= 0 ? 'NUEVO' : ($totalPagado >= $total ? 'PAGADO' : 'PAGANDO'),
            'fechapago' => $totalPagado > 0 ? now() : null,
        ]);
    }

    private function autorizarCuenta(CuentaCobrarpagar $cuenta): void
    {
        $persona = Persona::with('sucursal')->findOrFail(Auth::user()->persona_id);
        abort_unless($persona->sucursal && $persona->sucursal->empresa_id === $cuenta->sucursal->empresa_id, 403);
    }

    private function generarNumero(int $sucursalId, int $cajaId): string
    {
        $ultimo = Movcaja::where('sucursal_id', $sucursalId)->where('caja_id', $cajaId)->max('id') ?? 0;
        return str_pad((string) ($ultimo + 1), 8, '0', STR_PAD_LEFT);
    }

    private function origenCuenta(CuentaCobrarpagar $cuenta): string
    {
        $movimiento = $cuenta->movcaja?->movimiento;
        $tipo = $movimiento?->tipodocumento?->tipomovimiento?->descripcion
            ?? $movimiento?->tipodocumento?->nombre
            ?? 'Caja';

        return trim($tipo . ' ' . ($movimiento?->numero ?? $cuenta->movcaja?->caja ?? ''));
    }

    private function personaCuenta(CuentaCobrarpagar $cuenta): string
    {
        $persona = $cuenta->movcaja?->movimiento?->Persona;
        $doc = $persona?->personamaestro?->nrodoc ?? '0';
        $nombre = $cuenta->movcaja?->movimiento?->persona ?? 'CLIENTES VARIOS';

        return trim($doc . ' - ' . $nombre);
    }
}
