<?php

namespace App\Http\Controllers;

use App\Exports\GenericReportExport;
use App\Models\Caja;
use App\Models\Categorium;
use App\Models\DetMovcaja;
use App\Models\DetMovventum;
use App\Models\Habitacion;
use App\Models\Mediopago;
use App\Models\Movcaja;
use App\Models\Movhabitacion;
use App\Models\MovhabitacionHabitacion;
use App\Models\MovreservaHabitacion;
use App\Models\Movventum;
use App\Models\Movimiento;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\RelTurnocaja;
use App\Models\Sucursal;
use App\Models\Tipohabitacion;
use App\Models\Tipodocumento;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function movventa()
    {
        $sucursal_id = Auth::user()->persona->sucursal_id;
        $categorias = Categorium::whereNull('categoria_id')
            ->whereHas('relCategoriasucursal', function ($query) use ($sucursal_id) {
                $query->where('sucursal_id', $sucursal_id)->where('tipocarta', '!=', 'NINGUNA');
            })
            ->with('categorias')
            ->get();
        $cajas = Caja::where('sucursal_id', $sucursal_id)->orderBy('situacion', 'ASC')->get();
        $relTurnocajas = RelTurnocaja::where('sucursal_id', $sucursal_id)
            ->orderBy('fechainicio', 'desc')
            ->get();
        $tipodocumentos = Tipodocumento::where('tipomovimiento_id', 2)->get();
        $mediopagos = Mediopago::where('situacion', true)->orderBy('descripcion')->get();
        $habitaciones = Habitacion::where('sucursal_id', $sucursal_id)->orderBy('numero')->get();
        $recepcionistasmodel = DB::table('persona as p')
            ->select('p.id')
            ->distinct()
            ->leftJoin('users as u', 'u.persona_id', '=', 'p.id')
            ->leftJoin('rolpersona as r', 'r.persona_id', '=', 'p.id')
            ->where('p.sucursal_id', $sucursal_id)
            ->whereNull('p.deleted_at')
            ->where(function ($query) {
                $query->whereIn('u.perfil_id', [12, 10, 11, 19])
                    ->whereIn('r.rol_id', [1, 2]);
            })
            ->pluck('p.id'); // 🔹 obtenemos solo los IDs

        // Ahora obtenemos los modelos Persona completos
        $responsables = Persona::whereIn('id', $recepcionistasmodel)->get();

        return view('report.movventa', compact(
            'categorias',
            'cajas',
            'relTurnocajas',
            'tipodocumentos',
            'mediopagos',
            'habitaciones',
            'responsables'
        ));
    }

    public function movventaGenerar(Request $request)
    {
        $reporte = (string) $request->input('reporte', '');
        if ($reporte === '') {
            abort(404);
        }

        $sucursalId = Auth::user()->persona->sucursal_id;
        $sucursal = Sucursal::with('empresa')->findOrFail($sucursalId);

        [$inicio, $fin] = $this->resolveDateRange($request);
        $subtitle = $this->buildSubtitle($inicio, $fin);

        $data = $this->buildMovventaReport($reporte, $request, $sucursalId, $inicio, $fin);
        $data['subtitle'] = $subtitle;
        $data['sucursal'] = $sucursal;

        $tipoDocumento = strtolower((string) $request->input('tipo_documento', 'pdf'));
        $filename = $data['filename'] ?? 'reporte';

        if ($tipoDocumento === 'excel') {
            return Excel::download(
                new GenericReportExport($data['title'], $subtitle, $data['columns'], $data['rows']),
                $filename . '.xlsx'
            );
        }

$pdf = Pdf::loadView('report.movventa_pdf', $data)
    ->setPaper('a4', 'landscape')

    // Reserva espacio para el header (solo para que quepa)
    ->setOption('margin-top', '10mm')   // ajusta según el alto real de tu header

    // ✅ Esto es lo que quieres: separa header del contenido en TODAS las páginas
    ->setOption('header-spacing', 9)   // ✅ CORRECTO


    ->setOption('margin-bottom', '5mm')
    ->setOption('margin-left', '10mm')
    ->setOption('margin-right', '10mm')

    ->setOption('header-html', view('report.pdf_header', $data)->render())
    ->setOption('footer-html', view('report.pdf_footer')->render())
    ->setOption('footer-right', 'Pg. [page] de [topage]')
    ->setOption('footer-font-size', 6)
    ->setOption('footer-spacing', 0);



        return $pdf->stream($filename . '.pdf');
    }

    private function resolveDateRange(Request $request): array
    {
        $fechaInicio = $request->input('fecha_inicio');
        $horaInicio = $request->input('hora_inicio', '00:00:00');
        $fechaFin = $request->input('fecha_fin');
        $horaFin = $request->input('hora_fin', '23:59:59');

        $inicio = $fechaInicio ? Carbon::parse(trim($fechaInicio . ' ' . $horaInicio)) : null;
        $fin = $fechaFin ? Carbon::parse(trim($fechaFin . ' ' . $horaFin)) : null;

        return [$inicio, $fin];
    }

    private function buildSubtitle(?Carbon $inicio, ?Carbon $fin): string
    {
        if ($inicio && $fin) {
            return 'Desde ' . $inicio->format('Y-m-d H:i') . ' hasta ' . $fin->format('Y-m-d H:i');
        }
        if ($inicio) {
            return 'Desde ' . $inicio->format('Y-m-d H:i');
        }
        if ($fin) {
            return 'Hasta ' . $fin->format('Y-m-d H:i');
        }

        return 'Sin rango de fechas';
    }

    private function baseMovventaQuery(int $sucursalId, Request $request, ?Carbon $inicio, ?Carbon $fin)
    {
        $query = Movventum::query()
            ->where('sucursal_id', $sucursalId)
            ->whereNull('deleted_at');

        $moneda = $request->input('moneda');
        if ($moneda) {
            $query->where('moneda', $moneda);
        }

        $origen = $request->input('origen_venta');
        if ($origen === 'ALOJAMIENTO') {
            $query->whereNotNull('movhabitacion_id');
        } elseif ($origen === 'RESERVA') {
            $query->whereNotNull('movreserva_id');
        } elseif ($origen === 'DIRECTA') {
            $query->whereNull('movhabitacion_id')->whereNull('movreserva_id');
        }

        $habitacionId = $request->input('habitacion_id');
        if (!empty($habitacionId)) {
            $query->whereHas('movhabitacion.habitaciones', function ($q) use ($habitacionId) {
                $q->where('habitacion.id', $habitacionId);
            });
        }

        $query->whereHas('movimiento', function ($q) use ($inicio, $fin, $request) {
            if ($inicio && $fin) {
                $q->whereBetween('fecha', [$inicio, $fin]);
            } elseif ($inicio) {
                $q->where('fecha', '>=', $inicio);
            } elseif ($fin) {
                $q->where('fecha', '<=', $fin);
            }

            if ($request->has('situacion')) {
                $situacion = $request->input('situacion');
                if ($situacion === 'A' || $situacion === 'D') {
                    $q->where('situacion', $situacion);
                }
            } else {
                $q->where('situacion', 'A');
            }
        });

        return $query;
    }

    private function resolveColumns(Request $request, array $available, array $defaultKeys): array
    {
        $selected = $request->input('columnas', $defaultKeys);
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        $selected = array_values(array_filter($selected, fn($key) => array_key_exists($key, $available)));
        if (empty($selected)) {
            $selected = $defaultKeys;
        }

        $columns = [];
        foreach ($selected as $key) {
            $columns[$key] = $available[$key];
        }

        return [$selected, $columns];
    }

    private function buildRow(array $selected, array $data): array
    {
        $row = [];
        foreach ($selected as $key) {
            $row[] = $data[$key] ?? '';
        }
        return $row;
    }

    private function jsonValue(?string $json, string $key): ?string
    {
        if (!$json) {
            return null;
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return null;
        }
        return $decoded[$key] ?? null;
    }

    private function formatMoney($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function formatTipoCambio($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, 3, '.', '');
    }

    private function formatMetodoPagoDetalle($detalle): string
    {
        $medio = $detalle->Mediopago->descripcion ?? $detalle->mediopago ?? 'Otro';

        $extras = [];
        if (!empty($detalle->pos)) {
            $extras[] = $detalle->pos;
        }
        if (!empty($detalle->tarjeta)) {
            $extras[] = $detalle->tarjeta;
        }
        if (!empty($detalle->banco)) {
            $extras[] = $detalle->banco;
        }
        if (!empty($detalle->billeteradigital)) {
            $extras[] = $detalle->billeteradigital;
        }

        if (!empty($extras)) {
            $medio .= ' (' . implode(' | ', $extras) . ')';
        }

        return $medio . ': ' . $this->formatMoney($detalle->monto);
    }

    private function formatMetodoPagoExtras($detalle): string
    {
        $extras = [];
        if (!empty($detalle->pos)) {
            $extras[] = $detalle->pos;
        }
        if (!empty($detalle->tarjeta)) {
            $extras[] = $detalle->tarjeta;
        }
        if (!empty($detalle->banco)) {
            $extras[] = $detalle->banco;
        }
        if (!empty($detalle->billeteradigital)) {
            $extras[] = $detalle->billeteradigital;
        }

        if (empty($extras)) {
            return '';
        }

        return implode(' | ', $extras) . ': ' . $this->formatMoney($detalle->monto);
    }

    private function normalizeMetodoPagoLabel(?string $metodo): string
    {
        $value = mb_strtolower(trim((string) $metodo), 'UTF-8');
        $value = strtr($value, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ñ' => 'n',
        ]);

        if (str_contains($value, 'efectivo')) {
            return 'Efectivo';
        }
        if (str_contains($value, 'tarjeta')) {
            return 'Tarjeta';
        }
        if (str_contains($value, 'transfer')) {
            return 'Transferencia';
        }
        if (str_contains($value, 'depos')) {
            return 'Depósito';
        }
        if (str_contains($value, 'billetera')) {
            return 'Billetera digital';
        }
        if (str_contains($value, 'aplicacion') || str_contains($value, 'movil')) {
            return 'Aplicación móvil';
        }

        return '';
    }

    private function normalizeMetodoPagoKey(?string $metodo): string
    {
        $value = mb_strtolower(trim((string) $metodo), 'UTF-8');
        $converted = function_exists('iconv')
            ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value)
            : false;
        if ($converted !== false) {
            $value = $converted;
        }

        if (str_contains($value, 'efectivo')) {
            return 'Efectivo';
        }
        if (str_contains($value, 'tarjeta')) {
            return 'Tarjeta';
        }
        if (str_contains($value, 'transfer')) {
            return 'Transferencia';
        }
        if (str_contains($value, 'depos')) {
            return 'Deposito';
        }
        if (str_contains($value, 'billetera')) {
            return 'Billetera digital';
        }
        if (str_contains($value, 'aplic') || str_contains($value, 'movil')) {
            return 'Aplicacion movil';
        }

        return '';
    }

    private function isDetallePagado($detalle): bool
    {
        $tipo = mb_strtolower(trim((string) $detalle->tipo), 'UTF-8');
        if ($tipo === '') {
            return true;
        }

        return in_array($tipo, ['pagado', 'cobrado', 'cancelado'], true);
    }

    private function buildMetodoPagoConsolidadoLabel($detalle): string
    {
        $medio = $detalle->Mediopago->descripcion ?? $detalle->mediopago ?? 'Otro';
        $extras = [];
        if (!empty($detalle->tarjeta)) {
            $extras[] = $detalle->tarjeta;
        }
        if (!empty($detalle->banco)) {
            $extras[] = $detalle->banco;
        }
        if (!empty($detalle->billeteradigital)) {
            $extras[] = $detalle->billeteradigital;
        }
        if (!empty($detalle->pos)) {
            $extras[] = $detalle->pos;
        }

        if (empty($extras)) {
            return $medio;
        }

        return $medio . ' | ' . implode(' | ', $extras);
    }

    private function formatPersonaLabel($movimiento): string
    {
        if (!$movimiento) {
            return 'N/A';
        }

        $persona = $movimiento->Persona ?? null;
        $doc = $persona && $persona->personamaestro ? $persona->personamaestro->nrodoc : '';
        $nombre = $movimiento->persona ?? '';
        $label = trim($doc . ' - ' . $nombre);

        if ($label !== '' && $label !== '-') {
            return $label;
        }

        return $nombre !== '' ? $nombre : 'N/A';
    }

    private function formatCajaLabel($movcaja): string
    {
        if (!$movcaja) {
            return '';
        }

        $cajaAttribute = $movcaja->getAttribute('caja');
        if ($cajaAttribute !== null && $cajaAttribute !== '') {
            return (string) $cajaAttribute;
        }

        if ($movcaja->caja) {
            return $movcaja->caja->numero ?? '';
        }

        return '';
    }

    private function normalizeTipoDocumento(?string $nombre): string
    {
        $nombre = strtolower(trim((string) $nombre));
        if ($nombre === '') {
            return 'ticket';
        }
        if (str_contains($nombre, 'boleta')) {
            return 'boleta';
        }
        if (str_contains($nombre, 'factura')) {
            return 'factura';
        }

        return 'ticket';
    }

    private function formatTipoPago(?string $tipo): string
    {
        $tipo = strtoupper(trim((string) $tipo));
        if ($tipo === '') {
            return 'N/A';
        }
        if ($tipo === 'C' || $tipo === 'CONTADO') {
            return 'Al contado';
        }
        if ($tipo === 'CREDITO' || $tipo === 'CR') {
            return 'Credito';
        }

        return ucfirst(strtolower($tipo));
    }

    private function resolveOrigen(Movventum $movventa): string
    {
        if (!empty($movventa->movhabitacion_id)) {
            return 'Alojamiento - ' .  $movventa->movhabitacion->movimiento->numero;
        }
        if (!empty($movventa->movreserva_id)) {
            return 'Reserva';
        }

        return 'Directa';
    }

    private function formatFecha(?string $fecha): string
    {
        if (!$fecha) {
            return '';
        }

        return Carbon::parse($fecha)->format('Y-m-d H:i:s');
    }

    private function buildMovventaReport(string $reporte, Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        switch ($reporte) {
            case 'ventas_producto':
                return $this->reportVentasProducto($request, $sucursalId, $inicio, $fin);
            case 'ingresos_parcial':
                return $this->reportIngresosParcial($request, $sucursalId, $inicio, $fin);
            case 'ticket_promedio':
                return $this->reportTicketPromedio($request, $sucursalId, $inicio, $fin);
            case 'ticket_promedio_persona':
                return $this->reportTicketPromedioPersona($request, $sucursalId, $inicio, $fin);
            case 'ventas_documento':
                return $this->reportVentasDocumento($request, $sucursalId, $inicio, $fin);
            case 'ventas_mediopago':
                return $this->reportVentasMediopago($request, $sucursalId, $inicio, $fin);
            case 'ventas_mediopago_detallado':
                return $this->reportVentasMediopagoDetallado($request, $sucursalId, $inicio, $fin);
            case 'ventas_mediopago_consolidado':
                return $this->reportVentasMediopagoConsolidado($request, $sucursalId, $inicio, $fin);
            case 'ventas_responsable':
                return $this->reportVentasResponsable($request, $sucursalId, $inicio, $fin);
            case 'descuento_persona':
                return $this->reportDescuentoPersona($request, $sucursalId, $inicio, $fin);
            case 'cortesia_persona':
                return $this->reportCortesiaPersona($request, $sucursalId, $inicio, $fin);
            case 'descuentos':
                return $this->reportDescuentos($request, $sucursalId, $inicio, $fin);
            case 'cortesias':
                return $this->reportCortesias($request, $sucursalId, $inicio, $fin);
            case 'compromisos_responsable':
                return $this->reportCompromisosResponsable($request, $sucursalId, $inicio, $fin);
            case 'ventas_cliente':
                return $this->reportVentasCliente($request, $sucursalId, $inicio, $fin);
            case 'productos_cliente':
                return $this->reportProductosCliente($request, $sucursalId, $inicio, $fin);
            case 'ingresos_producto':
                return $this->reportIngresosProducto($request, $sucursalId, $inicio, $fin);
        }

        abort(404);
    }

    private function reportVentasProducto(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $movventas = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with(['detMovventas.producto.categorium', 'detMovventas.unidad'])
            ->get();

        $categoriaId = $request->input('categoria_id');
        $detalles = collect();

        foreach ($movventas as $movventa) {
            foreach ($movventa->detMovventas as $detalle) {
                if ($categoriaId && $detalle->producto && (string) $detalle->producto->categoria_id !== (string) $categoriaId) {
                    continue;
                }
                $detalles->push([
                    'detalle' => $detalle,
                    'moneda' => $movventa->moneda,
                ]);
            }
        }

        $agrupado = $detalles->groupBy(function ($item) {
            $detalle = $item['detalle'];
            $productoId = $detalle->producto_id ?? 0;
            return $productoId . '|' . $detalle->descripcion . '|' . $item['moneda'];
        });

        $available = [
            'categoria' => 'Categoria',
            'codigo' => 'Codigo',
            'descripcion' => 'Producto',
            'moneda' => 'Moneda',
            'precio_original' => 'Precio original',
            'cantidad' => 'Cantidad',
            'unidad' => 'Unidad',
            'precio_vendido' => 'P. vendido (PEN)',
            'total' => 'Total (PEN)',
        ];
        [$selected, $columns] = $this->resolveColumns($request, $available, ['categoria', 'descripcion', 'cantidad', 'precio_vendido', 'total']);

        $rows = [];
        $categoriasAgrupadas = [];
        foreach ($agrupado as $items) {
            $first = $items->first();
            $detalle = $first['detalle'];
            $moneda = $first['moneda'];

            $cantidad = $items->sum(fn($item) => (float) $item['detalle']->cantidad);
            $total = $items->sum(fn($item) => (float) $item['detalle']->monto);
            $totalOriginal = $items->sum(fn($item) => (float) ($item['detalle']->monto_original ?? $item['detalle']->monto));
            $precioOriginal = $cantidad > 0 ? $totalOriginal / $cantidad : 0;
            $precioVendido = $cantidad > 0 ? $total / $cantidad : 0;

            $categoria = trim((string) ($detalle->producto?->categorium?->descripcion ?? ''));
            if ($categoria === '') {
                continue;
            }
            $unidad = $detalle->unidad->descripcion
                ?? $this->jsonValue($detalle->json_unidad, 'descripcion')
                ?? '';

            $rowData = [
                'categoria' => $categoria,
                'codigo' => $detalle->producto?->codigo ?? '-',
                'descripcion' => $detalle->descripcion,
                'moneda' => $moneda,
                'precio_original' => $this->formatMoney($precioOriginal),
                'unidad' => $unidad,
                'cantidad' => $this->formatMoney($cantidad),
                'precio_vendido' => $this->formatMoney($precioVendido),
                'total' => $this->formatMoney($total),
            ];

            $row = $this->buildRow($selected, $rowData);
            $rows[] = $row;

            $categoriaKey = $categoria !== '' ? $categoria : '';
            if (!isset($categoriasAgrupadas[$categoriaKey])) {
                $categoriasAgrupadas[$categoriaKey] = [
                    'nombre' => $categoriaKey,
                    'moneda' => $moneda,
                    'total' => 0,
                    'rows' => [],
                ];
            }
            $categoriasAgrupadas[$categoriaKey]['rows'][] = $row;
            $categoriasAgrupadas[$categoriaKey]['total'] += (float) $total;
            if ($categoriasAgrupadas[$categoriaKey]['moneda'] === '' && $moneda !== '') {
                $categoriasAgrupadas[$categoriaKey]['moneda'] = $moneda;
            }
        }
        if (!empty($categoriasAgrupadas)) {
            ksort($categoriasAgrupadas, SORT_NATURAL | SORT_FLAG_CASE);
        }

        return [
            'title' => 'Ventas por producto',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'ventas_producto',
            'report_key' => 'ventas_producto',
            'selected_keys' => $selected,
            'categorias' => array_values($categoriasAgrupadas),
        ];
    }

    private function reportIngresosParcial(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $movventas = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with('movimiento')
            ->get();

        $agrupado = $movventas->groupBy(function ($movventa) {
            $fecha = $movventa->movimiento ? Carbon::parse($movventa->movimiento->fecha)->toDateString() : 'Sin fecha';
            return $fecha . '|' . $movventa->moneda;
        });

        $available = [
            'fecha' => 'Fecha',
            'moneda' => 'Moneda',
            'subtotal' => 'Subtotal',
            'igv' => 'IGV',
            'total' => 'Total',
            'diassemana' => 'Dias de semana',
            'finsemana' => 'Fin de semana',
        ];
        [$selected, $columns] = $this->resolveColumns($request, $available, ['fecha', 'moneda', 'total']);

        $rows = [];
        foreach ($agrupado as $key => $items) {
            [$fecha, $moneda] = array_pad(explode('|', $key, 2), 2, '');
            $moneda = $moneda !== '' ? $moneda : ($items->first()->moneda ?? '');

            $subtotal = $items->sum('subtotal');
            $igv = $items->sum('igv');
            $total = $items->sum('total');

            $esFinSemana = $fecha !== 'Sin fecha' ? Carbon::parse($fecha)->isWeekend() : false;
            $rowData = [
                'fecha' => $fecha,
                'moneda' => $moneda,
                'subtotal' => $this->formatMoney($subtotal),
                'igv' => $this->formatMoney($igv),
                'total' => $this->formatMoney($total),
                'diassemana' => $this->formatMoney($esFinSemana ? 0 : $total),
                'finsemana' => $this->formatMoney($esFinSemana ? $total : 0),
            ];

            $rows[] = $this->buildRow($selected, $rowData);
        }

        return [
            'title' => 'Ingresos parciales',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'ingresos_parciales',
        ];
    }

    private function reportTicketPromedio(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $movventas = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with('movimiento')
            ->get();

        $agrupado = $movventas->groupBy('moneda');
        $sections = [];

        foreach ($agrupado as $moneda => $items) {
            $monedaLabel = $moneda !== '' ? $moneda : (string) $request->input('moneda', 'PEN');

            $porDia = $items->groupBy(function ($movventa) {
                $fecha = $movventa->movimiento ? $movventa->movimiento->fecha : null;
                return $fecha ? Carbon::parse($fecha)->toDateString() : 'Sin fecha';
            });

            $mapaDias = [];
            foreach ($porDia as $dia => $diaItems) {
                $mapaDias[$dia] = [
                    'total' => $diaItems->sum('total'),
                    'personas' => $diaItems->count(),
                ];
            }

            $dias = [];
            if ($inicio && $fin) {
                $start = $inicio->copy()->startOfDay();
                $end = $fin->copy()->startOfDay();
                if ($start->gt($end)) {
                    [$start, $end] = [$end, $start];
                }
                foreach (CarbonPeriod::create($start, $end) as $date) {
                    $key = $date->toDateString();
                    $totalDia = $mapaDias[$key]['total'] ?? 0;
                    $personasDia = $mapaDias[$key]['personas'] ?? 0;
                    $promedioDia = $personasDia > 0 ? $totalDia / $personasDia : 0;
                    $dias[] = [
                        'dia' => $key,
                        'promedio' => $this->formatMoney($promedioDia),
                        'total' => $this->formatMoney($totalDia),
                        'personas' => (string) $personasDia,
                    ];
                }
            } else {
                $keys = array_keys($mapaDias);
                sort($keys);
                foreach ($keys as $key) {
                    $totalDia = $mapaDias[$key]['total'] ?? 0;
                    $personasDia = $mapaDias[$key]['personas'] ?? 0;
                    $promedioDia = $personasDia > 0 ? $totalDia / $personasDia : 0;
                    $dias[] = [
                        'dia' => $key,
                        'promedio' => $this->formatMoney($promedioDia),
                        'total' => $this->formatMoney($totalDia),
                        'personas' => (string) $personasDia,
                    ];
                }
            }

            $totalGeneral = $items->sum('total');
            $personasGeneral = $items->count();
            $promedioGeneral = $personasGeneral > 0 ? $totalGeneral / $personasGeneral : 0;

            $sections[] = [
                'moneda' => $monedaLabel,
                'resumen' => [
                    'promedio' => $this->formatMoney($promedioGeneral),
                    'total' => $this->formatMoney($totalGeneral),
                    'personas' => (string) $personasGeneral,
                ],
                'dias' => $dias,
            ];
        }

        $hasMultiple = count($sections) > 1;
        $rows = [];
        if ($hasMultiple) {
            $columns = [
                'Moneda',
                'Dia',
                'Ticket promedio',
                'Total de ventas',
                'Numero de personas',
            ];
            foreach ($sections as $section) {
                foreach ($section['dias'] as $diaRow) {
                    $rows[] = [
                        $section['moneda'],
                        $diaRow['dia'],
                        $diaRow['promedio'],
                        $diaRow['total'],
                        $diaRow['personas'],
                    ];
                }
            }
        } else {
            $monedaLabel = (string) $request->input('moneda', 'PEN');
            if (!empty($sections)) {
                $monedaLabel = $sections[0]['moneda'] ?? $monedaLabel;
            }
            $columns = [
                'Dia',
                'Ticket promedio (' . $monedaLabel . ')',
                'Total de ventas (' . $monedaLabel . ')',
                'Numero de personas',
            ];
            foreach ($sections as $section) {
                foreach ($section['dias'] as $diaRow) {
                    $rows[] = [
                        $diaRow['dia'],
                        $diaRow['promedio'],
                        $diaRow['total'],
                        $diaRow['personas'],
                    ];
                }
            }
        }

        return [
            'title' => 'Ticket promedio',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'ticket_promedio',
            'report_key' => 'ticket_promedio',
            'ticket_sections' => $sections,
            'ticket_has_multiple' => $hasMultiple,
        ];
    }

    private function reportTicketPromedioPersona(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $personaId = $request->input('persona_id');
        $query = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with(['movimiento.Persona.personamaestro', 'movimiento.Persona.ubigeo']);

        if (!empty($personaId)) {
            $query->whereHas('movimiento', function ($q) use ($personaId) {
                $q->where('persona_id', $personaId);
            });
        }

        $movventas = $query->get();

        $agrupado = $movventas->groupBy(function ($movventa) {
            $persona = $movventa->movimiento->Persona ?? null;
            $personaKey = $persona ? (string) $persona->id : '0';
            $moneda = $movventa->moneda ?? '';
            return $personaKey . '|' . $moneda;
        });

        $sections = [];
        foreach ($agrupado as $key => $items) {
            [$personaKey, $moneda] = array_pad(explode('|', $key, 2), 2, '');
            $monedaLabel = $moneda !== '' ? $moneda : (string) $request->input('moneda', 'PEN');

            $cantidad = $items->count();
            $total = $items->sum('total');
            $promedio = $cantidad > 0 ? $total / $cantidad : 0;

            $firstMovventa = $items->first();
            $movimiento = $firstMovventa->movimiento ?? null;
            $persona = $movimiento ? ($movimiento->Persona ?? null) : null;
            $personamaestro = $persona ? $persona->personamaestro : null;

            $nombreBase = trim(
                ($personamaestro->nombres ?? $persona->nombres ?? '') . ' ' . ($personamaestro->apellidos ?? $persona->apellidos ?? '')
            );
            $nombreCompleto = $nombreBase !== '' ? $nombreBase : ($movimiento->persona ?? 'N/A');

            $row = [
                'tipo' => $personamaestro->tipopersona ?? '',
                'documento' => $personamaestro->nrodoc ?? '',
                'nombre' => $nombreCompleto,
                'fechanac' => $personamaestro && $personamaestro->fechanac ? Carbon::parse($personamaestro->fechanac)->format('Y-m-d') : '',
                'genero' => $personamaestro->sexo ?? '',
                'direccion' => $persona->direccion ?? '',
                'email' => $persona->email ?? '',
                'telefono_fijo' => $persona->telefonofijo ?? '',
                'telefono_movil' => $persona->telefonomovil ?? '',
                'distrito' => $persona && $persona->ubigeo ? $persona->ubigeo->descripcion : '',
                'promedio' => $this->formatMoney($promedio),
                'total' => $this->formatMoney($total),
                'transacciones' => (string) $cantidad,
            ];

            if (!isset($sections[$monedaLabel])) {
                $sections[$monedaLabel] = [
                    'moneda' => $monedaLabel,
                    'total' => 0,
                    'transacciones' => 0,
                    'rows' => [],
                ];
            }

            $sections[$monedaLabel]['rows'][] = $row;
            $sections[$monedaLabel]['total'] += (float) $total;
            $sections[$monedaLabel]['transacciones'] += (int) $cantidad;
        }

        if (!empty($sections)) {
            foreach ($sections as $monedaKey => $section) {
                $transacciones = $section['transacciones'];
                $totalSeccion = $section['total'];
                $promedioSeccion = $transacciones > 0 ? $totalSeccion / $transacciones : 0;

                $sections[$monedaKey]['resumen'] = [
                    'promedio' => $this->formatMoney($promedioSeccion),
                    'total' => $this->formatMoney($totalSeccion),
                    'transacciones' => (string) $transacciones,
                ];
            }
        }

        $sections = array_values($sections);
        $hasMultiple = count($sections) > 1;

        $columns = [
            'Tipo',
            'Documento',
            'Nombres completos',
            'F. nacimiento',
            'Genero',
            'Direccion',
            'E-mail',
            'T. fijo',
            'T. movil',
            'Distrito',
            'Ticket promedio',
            'Total de ventas',
            'Numero de transacciones',
        ];

        $rows = [];
        foreach ($sections as $section) {
            foreach ($section['rows'] as $row) {
                $rows[] = [
                    $row['tipo'],
                    $row['documento'],
                    $row['nombre'],
                    $row['fechanac'],
                    $row['genero'],
                    $row['direccion'],
                    $row['email'],
                    $row['telefono_fijo'],
                    $row['telefono_movil'],
                    $row['distrito'],
                    $row['promedio'],
                    $row['total'],
                    $row['transacciones'],
                ];
            }
        }

        return [
            'title' => 'Ticket promedio por persona',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'ticket_promedio_persona',
            'report_key' => 'ticket_promedio_persona',
            'ticket_persona_sections' => $sections,
            'ticket_persona_has_multiple' => $hasMultiple,
        ];
    }

    private function reportVentasDocumento(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $query = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with(['movimiento.tipodocumento', 'movimiento.Persona.personamaestro']);

        $movventas = $query->get()
            ->sortBy(fn($movventa) => $movventa->movimiento?->fecha ?? '');

        $agrupado = $movventas->groupBy(function ($movventa) {
            $documento = $movventa->movimiento->tipodocumento->nombre ?? 'Sin documento';
            return $documento . '|' . $movventa->moneda;
        });

        $columns = [
            'Documento',
            'Moneda',
            'Cantidad de ventas',
            'Total',
        ];

        $rows = [];
        foreach ($agrupado as $key => $items) {
            [$documento, $moneda] = array_pad(explode('|', $key, 2), 2, '');
            $rows[] = [
                $documento,
                $moneda,
                (string) $items->count(),
                $this->formatMoney($items->sum('total')),
            ];
        }

        $totalesDocumento = [
            'ticket' => ['subtotal' => 0.0, 'igv' => 0.0, 'rc' => 0.0, 'total' => 0.0],
            'boleta' => ['subtotal' => 0.0, 'igv' => 0.0, 'rc' => 0.0, 'total' => 0.0],
            'factura' => ['subtotal' => 0.0, 'igv' => 0.0, 'rc' => 0.0, 'total' => 0.0],
        ];

        $detalleColumns = [
            'Numero',
            'Tipo',
            'Moneda',
            'Subtotal',
            'IGV',
   
            'Total',
            'Fecha',
            'Cliente',
            'Tipo de pago',
            'Comentario',
            'Situacion',
            'Origen',
        ];

        $detalleRows = [];
        foreach ($movventas as $movventa) {
            $movimiento = $movventa->movimiento;
            $tipoNombre = $movimiento && $movimiento->tipodocumento
                ? $movimiento->tipodocumento->nombre
                : 'Sin documento';
            $tipoKey = $this->normalizeTipoDocumento($tipoNombre);

            $totalesDocumento[$tipoKey]['subtotal'] += (float) $movventa->subtotal;
            $totalesDocumento[$tipoKey]['igv'] += (float) $movventa->igv;
            $totalesDocumento[$tipoKey]['total'] += (float) $movventa->total;

            $numeroPartes = array_filter([
                $movventa->serie ?? '',
                $movimiento->numero ?? '',
                $movventa->anio ?? '',
            ], fn($value) => $value !== '');
            $numero = $numeroPartes ? implode('-', $numeroPartes) : 'N/A';

            $persona = $movimiento->Persona ?? null;
            $clienteDoc = $persona && $persona->personamaestro ? $persona->personamaestro->nrodoc : '';
            $clienteNombre = $movimiento->persona ?? '';
            $cliente = trim($clienteDoc . ' - ' . $clienteNombre);
            if ($cliente === '' || $cliente === '-') {
                $cliente = 'N/A';
            }

            $detalleRows[] = [
                $numero,
                $tipoNombre,
                $movventa->moneda ?? '',
                $this->formatMoney($movventa->subtotal),
                $this->formatMoney($movventa->igv),
           
                $this->formatMoney($movventa->total),
                $this->formatFecha($movimiento->fecha ?? null),
                $cliente,
                $this->formatTipoPago($movventa->tipo_pago ?? ''),
                $movimiento->comentario ?? '',
                $movimiento->situacion ?? '',
                $this->resolveOrigen($movventa),
            ];
        }

        return [
            'title' => 'Ventas por tipo de documento',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'ventas_documento',
            'report_key' => 'ventas_documento',
            'totales_documento' => $totalesDocumento,
            'detalle_columns' => $detalleColumns,
            'detalle_rows' => $detalleRows,
        ];
    }

    private function buildPagoDetalles(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $movventas = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with([
                'movimiento.Persona.personamaestro',
                'movcaja.detMovcaja.Mediopago',
                'movcaja.turno',
                'movcaja.caja',
            ])
            ->get();

        $detalles = collect();
        foreach ($movventas as $movventa) {
            $movcaja = $movventa->movcaja;
            if (!$movcaja) {
                continue;
            }
            foreach ($movcaja->detMovcaja as $detalle) {
                $detalles->push([
                    'movventa' => $movventa,
                    'detalle' => $detalle,
                ]);
            }
        }

        $mediopagoId = $request->input('mediopago_id');
        if (!empty($mediopagoId)) {
            $detalles = $detalles->filter(fn($item) => (string) $item['detalle']->mediopago_id === (string) $mediopagoId);
        }

        return [$movventas, $detalles->values()];
    }

    private function reportVentasMediopago(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        [, $detalles] = $this->buildPagoDetalles($request, $sucursalId, $inicio, $fin);
        $detalles = $detalles->sortBy(function ($item) {
            return $item['movventa']->movimiento?->fecha ?? '';
        })->values();

        $columns = [
            'Origen',
            'Moneda',
            'T. cambio',
            'Total',
            'Metodos de pago',
            'Fecha',
            'Usuario',
            'Caja',
            'Turno',
            'Persona',
            'Responsable',
            'Comentario',
            'Situacion',
        ];

        $rows = [];
        foreach ($detalles as $item) {
            $movventa = $item['movventa'];
            $detalle = $item['detalle'];
            $movimiento = $movventa->movimiento;
            $movcaja = $movventa->movcaja;

            $serie = $movventa->serie ?? '';
            $correlativo = $movimiento ? str_pad((string) ($movimiento->numero ?? ''), 8, '0', STR_PAD_LEFT) : '';
            $anio = $movventa->anio ?? ($movimiento && $movimiento->fecha ? Carbon::parse($movimiento->fecha)->format('Y') : '');
            $origenPartes = array_filter([$serie, $correlativo, $anio], fn($value) => $value !== '');
            $origen = $origenPartes ? 'Venta - ' . implode('-', $origenPartes) : 'Venta';

            $turno = '';
            if ($movcaja) {
                $turno = $movcaja->turno->nombre
                    ?? $this->jsonValue($movcaja->json_turno, 'descripcion')
                    ?? '';
            }

            $rows[] = [
                $origen,
                $movventa->moneda ?? '',
                $this->formatTipoCambio($movventa->tipocambio ?? null),
                $this->formatMoney($detalle->monto),
                $this->formatMetodoPagoDetalle($detalle),
                $this->formatFecha($movimiento?->fecha ?? null),
                $movimiento->usuario ?? '',
                $this->formatCajaLabel($movcaja),
                $turno,
                $this->formatPersonaLabel($movimiento),
                $movimiento->responsable ?? '',
                $movimiento->comentario ?? '',
                $movimiento->situacion ?? '',
            ];
        }

        return [
            'title' => 'Ventas por metodo de pago',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'ventas_medio_pago',
            'report_key' => 'ventas_mediopago',
        ];
    }

    private function reportVentasMediopagoDetallado(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        [, $detalles] = $this->buildPagoDetalles($request, $sucursalId, $inicio, $fin);

        $agrupado = $detalles->groupBy(function ($item) {
            $detalle = $item['detalle'];
            return $detalle->Mediopago->descripcion ?? $detalle->mediopago ?? 'Otro';
        });

        if (!empty($agrupado)) {
            $agrupado = $agrupado->sortKeys();
        }

        $totalesPorMetodo = [
            'Efectivo' => 0.0,
            'Tarjeta' => 0.0,
            'Transferencia' => 0.0,
            'Depósito' => 0.0,
            'Billetera digital' => 0.0,
            'Aplicación móvil' => 0.0,
        ];

        $columns = [
            'Origen',
            'Total',
            'Total del metodo',
            'Detalles',
            'Fecha',
            'Usuario',
            'Caja',
            'Turno',
            'Persona',
            'Responsable',
            'Comentario',
            'Situacion',
        ];

        $rows = [];
        $sections = [];

        foreach ($agrupado as $metodo => $items) {
            $items = $items->sortBy(function ($item) {
                return $item['movventa']->movimiento?->fecha ?? '';
            })->values();

            $sectionRows = [];
            $totalMetodo = 0;
            $totalVentas = 0;

            foreach ($items as $item) {
                $movventa = $item['movventa'];
                $detalle = $item['detalle'];
                $movimiento = $movventa->movimiento;
                $movcaja = $movventa->movcaja;

                $serie = $movventa->serie ?? '';
                $correlativo = $movimiento ? str_pad((string) ($movimiento->numero ?? ''), 8, '0', STR_PAD_LEFT) : '';
                $anio = $movventa->anio ?? ($movimiento && $movimiento->fecha ? Carbon::parse($movimiento->fecha)->format('Y') : '');
                $origenPartes = array_filter([$serie, $correlativo, $anio], fn($value) => $value !== '');
                $origen = $origenPartes ? 'Venta - ' . implode('-', $origenPartes) : 'Venta';

                $turno = '';
                if ($movcaja) {
                    $turno = $movcaja->turno->descripcion
                        ?? $this->jsonValue($movcaja->json_turno, 'descripcion')
                        ?? '';
                }

                $row = [
                    'origen' => $origen,
                    'total' => $this->formatMoney($movventa->total ?? 0),
                    'total_metodo' => $this->formatMoney($detalle->monto ?? 0),
                    'detalles' => $this->formatMetodoPagoExtras($detalle),
                    'fecha' => $this->formatFecha($movimiento?->fecha ?? null),
                    'usuario' => $movimiento->usuario ?? '',
                    'caja' => $this->formatCajaLabel($movcaja),
                    'turno' => $turno,
                    'persona' => $this->formatPersonaLabel($movimiento),
                    'responsable' => $movimiento->responsable ?? '',
                    'comentario' => $movimiento->comentario ?? '',
                    'situacion' => $movimiento->situacion ?? '',
                ];

                $sectionRows[] = $row;
                $rows[] = array_values($row);

                $totalMetodo += (float) ($detalle->monto ?? 0);
                $totalVentas += (float) ($movventa->total ?? 0);

                $normalized = $this->normalizeMetodoPagoLabel($metodo);
                if ($normalized !== '' && array_key_exists($normalized, $totalesPorMetodo)) {
                    $totalesPorMetodo[$normalized] += (float) ($detalle->monto ?? 0);
                }
            }

            $sections[] = [
                'metodo' => $metodo,
                'rows' => $sectionRows,
                'total_metodo' => $this->formatMoney($totalMetodo),
                'total_ventas' => $this->formatMoney($totalVentas),
            ];
        }

        return [
            'title' => 'Detallado de ventas por metodo de pago',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'ventas_medio_pago_detallado',
            'report_key' => 'ventas_mediopago_detallado',
            'mediopago_detallado_sections' => $sections,
            'mediopago_detallado_totales' => array_map(fn($value) => $this->formatMoney($value), $totalesPorMetodo),
        ];
    }

    private function reportVentasMediopagoConsolidado(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        [, $detalles] = $this->buildPagoDetalles($request, $sucursalId, $inicio, $fin);

        $methodOrder = [
            'Efectivo',
            'Tarjeta',
            'Transferencia',
            'Deposito',
            'Billetera digital',
            'Aplicacion movil',
        ];

        $summaryPaid = [];
        $summaryOwed = [];
        $detailPaid = [];
        foreach ($methodOrder as $method) {
            $summaryPaid[$method] = ['pen' => 0.0, 'usd' => 0.0];
            $summaryOwed[$method] = ['pen' => 0.0, 'usd' => 0.0];
            $detailPaid[$method] = [];
        }

        foreach ($detalles as $item) {
            $movventa = $item['movventa'];
            $detalle = $item['detalle'];

            $moneda = $movventa->moneda ?? '';
            if ($moneda !== 'PEN' && $moneda !== 'USD') {
                continue;
            }
            $currencyKey = $moneda === 'USD' ? 'usd' : 'pen';

            $methodRaw = $detalle->Mediopago->descripcion ?? $detalle->mediopago ?? '';
            $methodKey = $this->normalizeMetodoPagoKey($methodRaw);
            if ($methodKey === '' || !array_key_exists($methodKey, $summaryPaid)) {
                continue;
            }

            $amount = (float) ($detalle->monto ?? 0);

            if ($this->isDetallePagado($detalle)) {
                $summaryPaid[$methodKey][$currencyKey] += $amount;
                $label = $this->buildMetodoPagoConsolidadoLabel($detalle);
                if (!isset($detailPaid[$methodKey][$label])) {
                    $detailPaid[$methodKey][$label] = ['pen' => 0.0, 'usd' => 0.0];
                }
                $detailPaid[$methodKey][$label][$currencyKey] += $amount;
            } else {
                $summaryOwed[$methodKey][$currencyKey] += $amount;
            }
        }

        $resumenPagadas = [
            'rows' => [],
            'totales' => ['pen' => 0.0, 'usd' => 0.0],
        ];
        $resumenAdeudadas = [
            'rows' => [],
            'totales' => ['pen' => 0.0, 'usd' => 0.0],
        ];

        foreach ($methodOrder as $method) {
            $paidPen = $summaryPaid[$method]['pen'];
            $paidUsd = $summaryPaid[$method]['usd'];
            $owedPen = $summaryOwed[$method]['pen'];
            $owedUsd = $summaryOwed[$method]['usd'];

            $resumenPagadas['rows'][] = [
                'medio' => $method,
                'pen' => $this->formatMoney($paidPen),
                'usd' => $this->formatMoney($paidUsd),
            ];
            $resumenAdeudadas['rows'][] = [
                'medio' => $method,
                'pen' => $this->formatMoney($owedPen),
                'usd' => $this->formatMoney($owedUsd),
            ];

            $resumenPagadas['totales']['pen'] += $paidPen;
            $resumenPagadas['totales']['usd'] += $paidUsd;
            $resumenAdeudadas['totales']['pen'] += $owedPen;
            $resumenAdeudadas['totales']['usd'] += $owedUsd;
        }

        $resumenPagadas['totales'] = [
            'pen' => $this->formatMoney($resumenPagadas['totales']['pen']),
            'usd' => $this->formatMoney($resumenPagadas['totales']['usd']),
        ];
        $resumenAdeudadas['totales'] = [
            'pen' => $this->formatMoney($resumenAdeudadas['totales']['pen']),
            'usd' => $this->formatMoney($resumenAdeudadas['totales']['usd']),
        ];

        $detallePagadas = [];
        foreach ($methodOrder as $method) {
            $items = $detailPaid[$method];
            if (empty($items)) {
                continue;
            }

            ksort($items);
            $rows = [];
            $totalPen = 0.0;
            $totalUsd = 0.0;
            foreach ($items as $label => $values) {
                $pen = $values['pen'] ?? 0.0;
                $usd = $values['usd'] ?? 0.0;
                $rows[] = [
                    'medio' => $label,
                    'pen' => $this->formatMoney($pen),
                    'usd' => $this->formatMoney($usd),
                ];
                $totalPen += $pen;
                $totalUsd += $usd;
            }

            $detallePagadas[] = [
                'metodo' => $method,
                'rows' => $rows,
                'totales' => [
                    'pen' => $this->formatMoney($totalPen),
                    'usd' => $this->formatMoney($totalUsd),
                ],
            ];
        }

        return [
            'title' => 'Consolidado de ventas por metodo de pago',
            'filename' => 'ventas_medio_pago_consolidado',
            'report_key' => 'ventas_mediopago_consolidado',
            'mediopago_consolidado_pagadas' => $resumenPagadas,
            'mediopago_consolidado_adeudadas' => $resumenAdeudadas,
            'mediopago_consolidado_detalle_pagadas' => $detallePagadas,
        ];
    }

    private function reportVentasResponsable(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $responsableId = $request->input('responsable_id');
        $categoriaId = $request->input('categoria_id');

        $query = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with([
                'movimiento.Responsable.personamaestro',
                'detMovventas.producto',
                'detMovventas.unidad',
            ]);

        if (!empty($responsableId)) {
            $query->whereHas('movimiento', function ($q) use ($responsableId) {
                $q->where('responsable_id', $responsableId);
            });
        }

        if (!empty($categoriaId)) {
            $query->whereHas('detMovventas.producto', function ($q) use ($categoriaId) {
                $q->where('categoria_id', $categoriaId);
            });
        }

        $movventas = $query->get();

        $detalles = collect();
        foreach ($movventas as $movventa) {
            $movimiento = $movventa->movimiento;
            $responsable = $movimiento ? $movimiento->Responsable : null;
            $doc = $responsable && $responsable->personamaestro ? $responsable->personamaestro->nrodoc : '';
            $nombre = $movimiento->responsable ?? '';
            $label = trim($doc . ' - ' . $nombre);
            if ($label === '' || $label === '-') {
                $label = 'N/A';
            }

            foreach ($movventa->detMovventas as $detalle) {
                if ($categoriaId && $detalle->producto && (string) $detalle->producto->categoria_id !== (string) $categoriaId) {
                    continue;
                }

                $cantidad = (float) $detalle->cantidad;
                $total = (float) $detalle->monto;
                $totalOriginal = (float) ($detalle->monto_original ?? $detalle->monto);
                $precioOriginal = $cantidad > 0 ? $totalOriginal / $cantidad : 0;
                $precioVendido = $cantidad > 0 ? $total / $cantidad : 0;

                $unidad = $detalle->unidad->descripcion
                    ?? $this->jsonValue($detalle->json_unidad, 'descripcion')
                    ?? '';

            $responsableKey = $movimiento->responsable_id ?? 'sin';
            $detalles->push([
                'responsable_id' => $responsableKey,
                'responsable' => $label,
                'detalle' => $detalle,
                'moneda' => $movventa->moneda ?? '',
                'unidad' => $unidad,
                'precio_original' => $precioOriginal,
                'precio_vendido' => $precioVendido,
            ]);
        }
        }

        $agrupado = $detalles->groupBy(function ($item) {
            $detalle = $item['detalle'];
            $productoId = $detalle->producto_id ?? 0;
            $precioOriginal = number_format($item['precio_original'], 2, '.', '');
            $precioVendido = number_format($item['precio_vendido'], 2, '.', '');
            $unidad = $item['unidad'] ?? '';

            return $item['responsable']
                . '|' . $productoId
                . '|' . $detalle->descripcion
                . '|' . $item['moneda']
                . '|' . $precioOriginal
                . '|' . $precioVendido
                . '|' . $unidad;
        });

        $columns = [
            'Responsable',
            'Codigo',
            'Descripcion',
            'Moneda',
            'P. original',
            'Cantidad',
            'Unidad',
            'P. vendido (PEN)',
            'Total (PEN)',
        ];

        $rows = [];
        $responsables = [];
        $totalesGenerales = [];

        foreach ($agrupado as $items) {
            $first = $items->first();
            $detalle = $first['detalle'];
            $responsableLabel = $first['responsable'] ?? 'N/A';
            $responsableId = $first['responsable_id'] ?? 'sin';
            $moneda = $first['moneda'] ?? '';
            $unidad = $first['unidad'] ?? '';

            $cantidad = $items->sum(fn($item) => (float) $item['detalle']->cantidad);
            $total = $items->sum(fn($item) => (float) $item['detalle']->monto);
            $totalOriginal = $items->sum(fn($item) => (float) ($item['detalle']->monto_original ?? $item['detalle']->monto));
            $precioOriginal = $cantidad > 0 ? $totalOriginal / $cantidad : 0;
            $precioVendido = $cantidad > 0 ? $total / $cantidad : 0;

            $codigo = $detalle->producto->codigo ?? $detalle->codigo ?? '-';
            $rowData = [
                'codigo' => $codigo,
                'descripcion' => $detalle->descripcion,
                'moneda' => $moneda,
                'precio_original' => $this->formatMoney($precioOriginal),
                'cantidad' => $this->formatMoney($cantidad),
                'unidad' => $unidad,
                'precio_vendido' => $this->formatMoney($precioVendido),
                'total' => $this->formatMoney($total),
            ];

            $rows[] = [
                $responsableLabel,
                $rowData['codigo'],
                $rowData['descripcion'],
                $rowData['moneda'],
                $rowData['precio_original'],
                $rowData['cantidad'],
                $rowData['unidad'],
                $rowData['precio_vendido'],
                $rowData['total'],
            ];

            if (!isset($responsables[$responsableId])) {
                $responsables[$responsableId] = [
                    'responsable' => $responsableLabel,
                    'rows' => [],
                    'totales' => [],
                ];
            }
            $responsables[$responsableId]['rows'][] = $rowData;
            if (!isset($responsables[$responsableId]['totales'][$moneda])) {
                $responsables[$responsableId]['totales'][$moneda] = 0.0;
            }
            $responsables[$responsableId]['totales'][$moneda] += (float) $total;

            if (!isset($totalesGenerales[$moneda])) {
                $totalesGenerales[$moneda] = 0.0;
            }
            $totalesGenerales[$moneda] += (float) $total;
        }

        if (!empty($responsables)) {
            ksort($responsables, SORT_NATURAL | SORT_FLAG_CASE);
            foreach ($responsables as &$section) {
                usort($section['rows'], function ($a, $b) {
                    return strcmp($a['codigo'], $b['codigo']);
                });
                foreach ($section['totales'] as $moneda => $total) {
                    $section['totales'][$moneda] = $this->formatMoney($total);
                }
            }
            unset($section);
        }

        $totalesGeneralesFormatted = [];
        foreach ($totalesGenerales as $moneda => $total) {
            $label = $moneda !== '' ? $moneda : 'N/A';
            $totalesGeneralesFormatted[$label] = $this->formatMoney($total);
        }

        return [
            'title' => 'Ventas por responsable',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'ventas_responsable',
            'report_key' => 'ventas_responsable',
            'responsables' => array_values($responsables),
            'totales_generales' => $totalesGeneralesFormatted,
        ];
    }

    private function reportDescuentoPersona(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $personaId = $request->input('persona_id');
        $categoriaId = $request->input('categoria_id');

        $query = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with([
                'detMovventas.producto.categorium',
                'movimiento.Persona.personamaestro',
                'movhabitacion.habitacion',
                'movhabitacion.habitaciones',
                'movhabitacion.movhabitacion_habitacion.habitacion',
            ]);

        if (!empty($personaId)) {
            $query->whereHas('movimiento', function ($q) use ($personaId) {
                $q->where('persona_id', $personaId);
            });
        }

        $movventas = $query->get();
        $columns = [
            'Cliente',
            'Habitacion',
            'Estadia',
            'Precio real (PEN)',
            'P. alquilado (PEN)',
            'Subtotal (PEN)',
            'Descuento total (PEN)',
        ];

        $rows = [];
        $detalleRows = [];
        $totalSubtotal = 0.0;
        $totalDescuento = 0.0;

        foreach ($movventas as $movventa) {
            $movimiento = $movventa->movimiento;
            $persona = $movimiento ? $movimiento->Persona : null;
            $doc = $persona && $persona->personamaestro ? $persona->personamaestro->nrodoc : '';
            $nombre = $movimiento->persona ?? '';
            $clienteLabel = trim($doc . ' - ' . $nombre);
            if ($clienteLabel === '' || $clienteLabel === '-') {
                $clienteLabel = 'N/A';
            }

            $habitacionLabel = 'N/A';
            $movhabitacion = $movventa->movhabitacion;
            if ($movhabitacion) {
                $habitaciones = collect();
                $relaciones = $movhabitacion->movhabitacion_habitacion ?? collect();
                if ($relaciones->count()) {
                    $relacionesFiltradas = $relaciones->filter(function ($relacion) use ($movventa) {
                        return empty($relacion->movventa_id) || (int) $relacion->movventa_id === (int) $movventa->id;
                    });
                    $relacionesUsar = $relacionesFiltradas->count() ? $relacionesFiltradas : $relaciones;
                    $habitaciones = $relacionesUsar->map(function ($relacion) {
                        return $relacion->habitacion;
                    })->filter();
                }

                if ($habitaciones->isEmpty() && !empty($movhabitacion->habitaciones)) {
                    $habitaciones = $movhabitacion->habitaciones;
                }

                if ($habitaciones->isEmpty() && $movhabitacion->habitacion) {
                    $habitaciones = collect([$movhabitacion->habitacion]);
                }

                if ($habitaciones->isNotEmpty()) {
                    $habitacionLabel = $habitaciones->map(function ($habitacion) {
                        $numero = $habitacion->numero ?? '';
                        $descripcion = $habitacion->descripcion ?? '';
                        $label = $numero !== '' ? $numero : $descripcion;
                        return trim($label);
                    })->filter()->unique()->implode(', ');
                }
            }

            foreach ($movventa->detMovventas as $detalle) {
                if ((float) $detalle->dcto_porcentaje <= 0) {
                    continue;
                }
                if ($categoriaId && $detalle->producto && (string) $detalle->producto->categoria_id !== (string) $categoriaId) {
                    continue;
                }

                $cantidad = (float) $detalle->cantidad;
                $subtotal = (float) $detalle->monto;
                $original = (float) ($detalle->monto_original ?? $detalle->monto);
                $descuento = max($original - $subtotal, 0);
                $precioVendido = $cantidad > 0 ? $subtotal / $cantidad : 0;

                $precioReal = $movhabitacion ? ($movhabitacion->movhabitacion_habitacion->first()->tarifa ?? $movhabitacion->preciohabitacion ?? 0) : 0;
                $estadiaLabel = 'N/A';
                if ($movhabitacion) {
                    $dias = (float) ($movhabitacion->dias ?? 0);
                    $horas = (float) ($movhabitacion->horas ?? 0);
                    if ($dias > 0) {
                        $estadiaLabel = round($dias) . ' ' . (round($dias) === 1 ? 'noche' : 'noches');
                    } elseif ($horas > 0) {
                        $estadiaLabel = round($horas) . ' ' . (round($horas) === 1 ? 'hora' : 'horas');
                    }
                }

                $row = [
                    'cliente' => $clienteLabel,
                    'habitacion' => $habitacionLabel,
                    'estadia' => $estadiaLabel,
                    'precio_real' => $this->formatMoney($precioReal),
                    'precio_vendido' => $this->formatMoney($precioVendido),
                    'subtotal' => $this->formatMoney($subtotal),
                    'descuento' => $this->formatMoney($descuento),
                ];

                $detalleRows[] = $row;
                $rows[] = [
                    $row['cliente'],
                    $row['habitacion'],
                    $row['estadia'],
                    $row['precio_real'],
                    $row['precio_vendido'],
                    $row['subtotal'],
                    $row['descuento'],
                ];

                $totalSubtotal += $subtotal;
                $totalDescuento += $descuento;
            }
        }

        return [
            'title' => 'Descuentos por persona',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'descuentos_persona',
            'report_key' => 'descuento_persona',
            'descuento_persona_rows' => $detalleRows,
            'descuento_persona_totales' => [
                'subtotal' => $this->formatMoney($totalSubtotal),
                'descuento' => $this->formatMoney($totalDescuento),
            ],
        ];
    }

    private function reportCortesiaPersona(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $personaId = $request->input('persona_id');
        $categoriaId = $request->input('categoria_id');

        $query = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with(['detMovventas.producto.categorium', 'movimiento.Persona.personamaestro'])
            ->where('total', 0);

        if (!empty($personaId)) {
            $query->whereHas('movimiento', function ($q) use ($personaId) {
                $q->where('persona_id', $personaId);
            });
        }

        $movventas = $query->get();
        $detalles = collect();

        foreach ($movventas as $movventa) {
            foreach ($movventa->detMovventas as $detalle) {
                if ($categoriaId && $detalle->producto && (string) $detalle->producto->categoria_id !== (string) $categoriaId) {
                    continue;
                }
                $detalles->push([
                    'detalle' => $detalle,
                    'movventa' => $movventa,
                ]);
            }
        }

        $agrupado = $detalles->groupBy(function ($item) {
            $movventa = $item['movventa'];
            $persona = $movventa->movimiento->Persona ?? null;
            $doc = $persona && $persona->personamaestro ? $persona->personamaestro->nrodoc : '';
            $nombre = $movventa->movimiento->persona ?? '';
            $label = trim($doc . ' - ' . $nombre);
            $label = $label !== '' ? $label : 'N/A';
            return $label . '|' . $movventa->moneda;
        });

        $available = [
            'persona' => 'Persona',
            'moneda' => 'Moneda',
            'cantidad' => 'Cantidad de items',
            'total' => 'Total cortesia',
        ];
        [$selected, $columns] = $this->resolveColumns($request, $available, ['persona', 'cantidad', 'total']);

        $rows = [];
        foreach ($agrupado as $key => $items) {
            [$personaLabel, $moneda] = array_pad(explode('|', $key, 2), 2, '');

            $total = $items->sum(function ($item) {
                $detalle = $item['detalle'];
                return (float) ($detalle->monto_original ?? $detalle->monto);
            });

            $rowData = [
                'persona' => $personaLabel,
                'moneda' => $moneda,
                'cantidad' => (string) $items->count(),
                'total' => $this->formatMoney($total),
            ];

            $rows[] = $this->buildRow($selected, $rowData);
        }

        return [
            'title' => 'Cortesias por persona',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'cortesias_persona',
        ];
    }

    private function reportDescuentos(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $producto = trim((string) $request->input('producto'));
        $responsableId = $request->input('responsable_id');

        $query = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with(['detMovventas.producto', 'movimiento.Responsable']);

        if (!empty($responsableId)) {
            $query->whereHas('movimiento', function ($q) use ($responsableId) {
                $q->where('responsable_id', $responsableId);
            });
        }

        $movventas = $query->get();
        $detalles = collect();

        foreach ($movventas as $movventa) {
            foreach ($movventa->detMovventas as $detalle) {
                if ((float) $detalle->dcto_porcentaje <= 0) {
                    continue;
                }
                if ($producto !== '' && stripos($detalle->descripcion, $producto) === false) {
                    continue;
                }
                $detalles->push([
                    'detalle' => $detalle,
                    'movventa' => $movventa,
                ]);
            }
        }

        $agrupado = $detalles->groupBy(function ($item) {
            $detalle = $item['detalle'];
            return ($detalle->producto_id ?? 0) . '|' . $detalle->descripcion;
        });

        $columns = [
            'Producto',
            'Cantidad',
            'Subtotal',
            'Descuento',
            'Total',
        ];

        $rows = [];
        foreach ($agrupado as $key => $items) {
            $descripcion = explode('|', $key, 2)[1] ?? 'Sin descripcion';
            $cantidad = $items->sum(fn($item) => (float) $item['detalle']->cantidad);
            $subtotal = $items->sum(fn($item) => (float) ($item['detalle']->monto_original ?? $item['detalle']->monto));
            $descuento = $items->sum(function ($item) {
                $detalle = $item['detalle'];
                $original = (float) ($detalle->monto_original ?? $detalle->monto);
                return $original * ((float) $detalle->dcto_porcentaje / 100);
            });
            $total = $subtotal - $descuento;

            $rows[] = [
                $descripcion,
                $this->formatMoney($cantidad),
                $this->formatMoney($subtotal),
                $this->formatMoney($descuento),
                $this->formatMoney($total),
            ];
        }

        return [
            'title' => 'Descuentos',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'descuentos',
        ];
    }

    private function reportCortesias(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $producto = trim((string) $request->input('producto'));
        $responsableId = $request->input('responsable_id');

        $query = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with(['detMovventas.producto', 'movimiento.Responsable']);

        if (!empty($responsableId)) {
            $query->whereHas('movimiento', function ($q) use ($responsableId) {
                $q->where('responsable_id', $responsableId);
            });
        }

        $movventas = $query->get();
        $detalles = collect();

        foreach ($movventas as $movventa) {
            foreach ($movventa->detMovventas as $detalle) {
                if ((float) $detalle->monto > 0) {
                    continue;
                }
                if ($producto !== '' && stripos($detalle->descripcion, $producto) === false) {
                    continue;
                }
                $detalles->push([
                    'detalle' => $detalle,
                    'movventa' => $movventa,
                ]);
            }
        }

        $agrupado = $detalles->groupBy(function ($item) {
            $detalle = $item['detalle'];
            return ($detalle->producto_id ?? 0) . '|' . $detalle->descripcion;
        });

        $columns = [
            'Producto',
            'Cantidad',
            'Total cortesia',
        ];

        $rows = [];
        foreach ($agrupado as $key => $items) {
            $descripcion = explode('|', $key, 2)[1] ?? 'Sin descripcion';
            $cantidad = $items->sum(fn($item) => (float) $item['detalle']->cantidad);
            $total = $items->sum(fn($item) => (float) ($item['detalle']->monto_original ?? $item['detalle']->monto));

            $rows[] = [
                $descripcion,
                $this->formatMoney($cantidad),
                $this->formatMoney($total),
            ];
        }

        return [
            'title' => 'Cortesias',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'cortesias',
        ];
    }

    private function reportCompromisosResponsable(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $responsableId = $request->input('responsable_id');
        $estado = $request->input('estado');

        $query = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with('movimiento.Responsable.personamaestro')
            ->whereNotNull('compromiso_pago_id')
            ->whereHas('compromisoPago', function ($q) use ($estado) {
                if ($estado === 'PENDIENTE') {
                    $q->whereNull('fecha_pago')->where('estado', '!=', 'pagado');
                } elseif ($estado === 'PAGADO') {
                    $q->where('estado', 'pagado');
                }
            });

        if (!empty($responsableId)) {
            $query->whereHas('movimiento', function ($q) use ($responsableId) {
                $q->where('responsable_id', $responsableId);
            });
        }

        $movventas = $query->get();
        $agrupado = $movventas->groupBy(function ($movventa) {
            $responsable = $movventa->movimiento->Responsable ?? null;
            $doc = $responsable && $responsable->personamaestro ? $responsable->personamaestro->nrodoc : '';
            $nombre = $movventa->movimiento->responsable ?? '';
            $label = trim($doc . ' - ' . $nombre);
            $label = $label !== '' ? $label : 'N/A';
            return $label . '|' . $movventa->moneda;
        });

        $columns = [
            'Responsable',
            'Moneda',
            'Cantidad',
            'Total',
        ];

        $rows = [];
        foreach ($agrupado as $key => $items) {
            [$responsable, $moneda] = array_pad(explode('|', $key, 2), 2, '');
            $rows[] = [
                $responsable,
                $moneda,
                (string) $items->count(),
                $this->formatMoney($items->sum('total')),
            ];
        }

        return [
            'title' => 'Compromisos por responsable',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'compromisos_responsable',
        ];
    }

    private function reportVentasCliente(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $personaId = $request->input('persona_id');
        $query = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with('movimiento.Persona.personamaestro');

        if (!empty($personaId)) {
            $query->whereHas('movimiento', function ($q) use ($personaId) {
                $q->where('persona_id', $personaId);
            });
        }

        $movventas = $query->get();

        $agrupado = $movventas->groupBy(function ($movventa) {
            $persona = $movventa->movimiento->Persona ?? null;
            $doc = $persona && $persona->personamaestro ? $persona->personamaestro->nrodoc : '';
            $nombre = $movventa->movimiento->persona ?? '';
            $label = trim($doc . ' - ' . $nombre);
            $label = $label !== '' ? $label : 'N/A';
            return $label . '|' . $movventa->moneda;
        });

        $columns = [
            'Persona',
            'Moneda',
            'Cantidad de ventas',
            'Total',
        ];

        $rows = [];
        foreach ($agrupado as $key => $items) {
            [$persona, $moneda] = array_pad(explode('|', $key, 2), 2, '');
            $rows[] = [
                $persona,
                $moneda,
                (string) $items->count(),
                $this->formatMoney($items->sum('total')),
            ];
        }

        return [
            'title' => 'Ventas por persona',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'ventas_persona',
        ];
    }

    private function reportProductosCliente(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $personaId = $request->input('persona_id');
        $query = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with(['detMovventas.producto', 'movimiento.Persona.personamaestro']);

        if (!empty($personaId)) {
            $query->whereHas('movimiento', function ($q) use ($personaId) {
                $q->where('persona_id', $personaId);
            });
        }

        $movventas = $query->get();
        $detalles = collect();

        foreach ($movventas as $movventa) {
            foreach ($movventa->detMovventas as $detalle) {
                $detalles->push([
                    'detalle' => $detalle,
                    'movventa' => $movventa,
                ]);
            }
        }

        $agrupado = $detalles->groupBy(function ($item) {
            $detalle = $item['detalle'];
            return ($detalle->producto_id ?? 0) . '|' . $detalle->descripcion . '|' . $item['movventa']->moneda;
        });

        $columns = [
            'Producto',
            'Moneda',
            'Cantidad',
            'Total',
        ];

        $rows = [];
        foreach ($agrupado as $key => $items) {
            [$id, $descripcion, $moneda] = array_pad(explode('|', $key, 3), 3, '');
            $cantidad = $items->sum(fn($item) => (float) $item['detalle']->cantidad);
            $total = $items->sum(fn($item) => (float) $item['detalle']->monto);
            $rows[] = [
                $descripcion,
                $moneda,
                $this->formatMoney($cantidad),
                $this->formatMoney($total),
            ];
        }

        return [
            'title' => 'Productos por persona',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'productos_persona',
        ];
    }

    private function reportIngresosProducto(Request $request, int $sucursalId, ?Carbon $inicio, ?Carbon $fin): array
    {
        $movventas = $this->baseMovventaQuery($sucursalId, $request, $inicio, $fin)
            ->with(['detMovventas.producto.categorium'])
            ->get();

        $categoriaId = $request->input('categoria_id');
        $detalles = collect();

        foreach ($movventas as $movventa) {
            foreach ($movventa->detMovventas as $detalle) {
                if ($categoriaId && $detalle->producto && (string) $detalle->producto->categoria_id !== (string) $categoriaId) {
                    continue;
                }
                $detalles->push([
                    'detalle' => $detalle,
                    'moneda' => $movventa->moneda,
                ]);
            }
        }

        $agrupado = $detalles->groupBy(function ($item) {
            $detalle = $item['detalle'];
            $categoria = $detalle->producto->categorium->descripcion ?? 'Sin categoria';
            return $categoria . '|' . $item['moneda'];
        });

        $columns = [
            'Categoria',
            'Moneda',
            'Cantidad',
            'Total',
        ];

        $rows = [];
        foreach ($agrupado as $key => $items) {
            [$categoria, $moneda] = array_pad(explode('|', $key, 2), 2, '');
            $cantidad = $items->sum(fn($item) => (float) $item['detalle']->cantidad);
            $total = $items->sum(fn($item) => (float) $item['detalle']->monto);
            $rows[] = [
                $categoria,
                $moneda,
                $this->formatMoney($cantidad),
                $this->formatMoney($total),
            ];
        }

        return [
            'title' => 'Ingresos por categoria',
            'columns' => $columns,
            'rows' => $rows,
            'filename' => 'ingresos_producto',
        ];
    }
    public function dashmovventa(Request $request)
    {
        $sucursal_id = Auth::user()->persona->sucursal_id;

        // Fechas del mes actual
        $fechaInicio = $request->filled('fecha_inicio')
            ? Carbon::parse($request->fecha_inicio)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();


        $fechaFin = $request->filled('fecha_fin')
            ? Carbon::parse($request->fecha_fin)->endOfDay()
            : Carbon::now()->endOfDay();



        $ventasPorTipoDocumento = Movventum::with('movimiento.tipodocumento')
            ->whereHas('movimiento', function ($query) use ($fechaInicio, $fechaFin, $sucursal_id) {
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at');
            })
            ->whereNull('deleted_at')
            ->get()
            ->groupBy(function ($venta) {
                return $venta->movimiento->tipodocumento->id ?? 0;
            })
            ->map(function ($items, $tipoId) {
                return [
                    'id' => $tipoId,
                    'nombre' => $items->first()->movimiento->tipodocumento->nombre ?? 'Desconocido',
                    'totales' => $items->groupBy('moneda')->map(function ($group) {
                        return $group->sum('total');
                    }),
                ];
            });

        $ventasAgrupadas = Movventum::with('movimiento.tipodocumento')
            ->whereHas('movimiento', function ($q) use ($fechaInicio, $fechaFin, $sucursal_id) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at');
            })
            ->whereNull('deleted_at')
            ->get()
            ->groupBy(fn($venta) => \Carbon\Carbon::parse($venta->movimiento->fecha)->format('Y-m-d'))
            ->map(function ($ventasDelDia) {
                return $ventasDelDia->groupBy(fn($v) => $v->movimiento->tipodocumento->id ?? 0)
                    ->map(
                        fn($ventasTipo) =>
                        $ventasTipo->groupBy('moneda')->map(fn($g) => $g->sum('total'))
                    );
            })
            ->sortKeys(); // <-- Ordena las fechas cronológicamente
        $evolucionVentas = Movventum::with('movimiento')
            ->whereHas('movimiento', function ($q) use ($fechaInicio, $fechaFin, $sucursal_id) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at');
            })
            ->whereNull('deleted_at')
            ->get()
            ->groupBy(fn($v) => \Carbon\Carbon::parse($v->movimiento->fecha)->format('Y-m-d'))
            ->map(function ($ventasDelDia) {
                return [
                    'total' => $ventasDelDia->where('moneda', 'PEN')->sum('total'),
                    'credito' => $ventasDelDia->where('tipo_pago', 'C')->sum('total'),
                    'anuladas' => $ventasDelDia->filter(function ($venta) {

                        return $venta->movimiento && $venta->movimiento->situacion === 'Desactivado';
                    })->sum('total'),
                ];
            })
            ->sortKeys();

        $descuentosPorPersona = Movventum::with(['detMovventas', 'movimiento.persona'])
            ->whereHas('movimiento', function ($q) use ($fechaInicio, $fechaFin, $sucursal_id) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at');
            })
            ->whereHas('detMovventas', function ($q) {
                $q->where('dcto_porcentaje', '>', 0);
            })
            ->get()
            ->flatMap(function ($movventa) {
                $persona = $movventa->movimiento->Persona->personamaestro->nrodoc . ' - ' . $movventa->movimiento->Persona->nombres . ' ' . $movventa->movimiento->Persona->apellidos ?? 'Sin Nombre';

                return $movventa->detMovventas
                    ->filter(fn($detalle) => $detalle->dcto_porcentaje > 0)
                    ->map(function ($detalle) use ($persona) {
                        $totalOriginal = $detalle->monto ?? 0;
                        $descuento = $totalOriginal * ($detalle->dcto_porcentaje / 100);

                        return [
                            'persona'   => $persona,
                            'subtotal'  => $totalOriginal,
                            'descuento' => $descuento,
                        ];
                    });
            })
            ->groupBy('persona')
            ->map(function ($items) {
                return [
                    'subtotal'  => $items->sum('subtotal'),
                    'descuento' => $items->sum('descuento'),
                ];
            });
        $descuentosPorUsuario = Movventum::with(['detMovventas', 'movimiento.Usuario'])
            ->whereHas('movimiento', function ($q) use ($fechaInicio, $fechaFin, $sucursal_id) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at');
            })
            ->whereHas('detMovventas', function ($q) {
                $q->where('dcto_porcentaje', '>', 0);
            })
            ->get()
            ->flatMap(function ($movventa) {
                $usuario =  $movventa->movimiento->responsable ?? 'Sin usuario';

                return $movventa->detMovventas
                    ->filter(fn($detalle) => $detalle->dcto_porcentaje > 0)
                    ->map(function ($detalle) use ($usuario) {
                        $totalOriginal = $detalle->monto ?? 0;
                        $descuento = $totalOriginal * ($detalle->dcto_porcentaje / 100);

                        return [
                            'usuario'   => $usuario,
                            'subtotal'  => $totalOriginal,
                            'descuento' => $descuento,
                        ];
                    });
            })
            ->groupBy('usuario')
            ->map(function ($items) {
                return [
                    'subtotal'  => $items->sum('subtotal'),
                    'descuento' => $items->sum('descuento'),
                ];
            })
            ->map(function ($valores, $usuario) {
                return [
                    'name' => $usuario,
                    'value' => round($valores['descuento'], 2),
                    'subtotal' => round($valores['subtotal'], 2)
                ];
            })
            ->values();

        $topHabitacionesDescuento = Movhabitacion::with('habitacion')
            ->whereHas('habitacion', function ($q) use ($sucursal_id) {
                $q->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at');
            })
            ->where('descuento', '>', 0)
            ->get()
            ->groupBy('habitacion_id')
            ->map(function ($items, $habitacion_id) {
                $habitacion = $items->first()->habitacion;
                return [
                    'id' => $habitacion_id,
                    'nombre' => $habitacion ? $habitacion->numero : 'Sin número',
                    'descuento' => $items->sum('descuento')
                ];
            })
            ->sortByDesc('descuento')
            ->take(10)
            ->values();
        $cortesiasPorUsuario = Movventum::with(['detMovventas', 'movimiento.usuario'])
            ->where('total', 0) // Solo ventas con total 0
            ->whereHas('movimiento', function ($q) use ($fechaInicio, $fechaFin, $sucursal_id) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at');
            })

            ->get()
            ->flatMap(function ($movventa) {
                $usuario = $movventa->movimiento->Usuario->name ?? 'Sin usuario';

                return $movventa->detMovventas

                    ->map(function ($detalle) use ($usuario) {
                        $cantidad = $detalle->monto_original ?? 0;

                        return [
                            'usuario'   => $usuario,
                            'cantidad'  => $cantidad,
                        ];
                    });
            })
            ->groupBy('usuario')
            ->map(function ($items) {
                return [
                    'cantidad'  => $items->sum('cantidad'),

                ];
            })
            ->map(function ($valores, $usuario) {
                return [
                    'name' => $usuario,
                    'value' => round($valores['cantidad'], 2),

                ];
            })
            ->values();

        $cortesiasPorPersona = Movventum::with(['detMovventas', 'movimiento.persona'])
            ->where('total', 0) // Solo ventas con total 0 (cortesía)
            ->whereHas('movimiento', function ($q) use ($fechaInicio, $fechaFin, $sucursal_id) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at');
            })
            ->get()
            ->flatMap(function ($movventa) {
                $persona = $movventa->movimiento->Persona->personamaestro->nrodoc . ' - ' .
                    $movventa->movimiento->Persona->nombres . ' ' .
                    $movventa->movimiento->Persona->apellidos ?? 'Sin Nombre';

                return $movventa->detMovventas
                    ->map(function ($detalle) use ($persona) {
                        $cantidad = $detalle->monto_original ?? 0;
                        return [
                            'persona' => $persona,
                            'cantidad' => $cantidad,
                        ];
                    });
            })
            ->groupBy('persona')
            ->map(function ($items) {
                return [
                    'cantidad' => $items->sum('cantidad'),
                ];
            })
            ->map(function ($valores, $persona) {
                return [
                    'name' => $persona,
                    'value' => round($valores['cantidad'], 2),
                ];
            })
            ->values();

        $topHabitacionesCortesia = Movhabitacion::with(['habitacion', 'movventas.detMovventas'])
            ->whereHas('habitacion', function ($q) use ($sucursal_id) {
                $q->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at');
            })
            ->whereHas('movventas', function ($q) {
                $q->where('total', 0); // Solo ventas con total 0 (cortesía)
            })
            ->get()
            ->groupBy('habitacion_id')
            ->map(function ($items, $habitacion_id) {
                $habitacion = $items->first()->habitacion;

                // Sumar monto_original de todos los detalles de movventas con total 0
                $totalCortesia = $items->flatMap(function ($movhabitacion) {
                    return $movhabitacion->movventas
                        ->filter(fn($mv) => $mv->total == 0)
                        ->flatMap(fn($mv) => $mv->detMovventas)
                        ->pluck('monto_original');
                })->sum();

                return [
                    'id' => $habitacion_id,
                    'nombre' => $habitacion ? $habitacion->numero : 'Sin número',
                    'cortesia' => round($totalCortesia, 2)
                ];
            })
            ->sortByDesc('cortesia')
            ->take(10)
            ->values();
        // dd($topHabitacionesCortesia);
        return view('report.dashboardventas', compact(
            'topHabitacionesCortesia',
            'descuentosPorUsuario',
            'ventasPorTipoDocumento',
            'ventasAgrupadas',
            'evolucionVentas',
            'descuentosPorPersona',
            'topHabitacionesDescuento',
            'cortesiasPorUsuario',
            'cortesiasPorPersona',
            'fechaInicio',
            'fechaFin'
        ));
    }

    public function dashmovcaja(Request $request)
    {
        $sucursal_id = Auth::user()->persona->sucursal_id;

        // Si existen en request usar esas fechas, si no usar valores por defecto
        $fechaInicio = $request->filled('fecha_inicio')
            ? Carbon::parse($request->fecha_inicio)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();


        $fechaFin = $request->filled('fecha_fin')
            ? Carbon::parse($request->fecha_fin)->endOfDay()
            : Carbon::now()->endOfDay();


        $ingresosVsEgresos = collect([
            'Ingreso' => 0,
            'Egreso' => 0,
        ]);

        $cajaItems = \App\Models\Movcaja::with('movimiento')
            ->whereHas('movimiento', function ($q) use ($fechaInicio, $fechaFin, $sucursal_id) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at');
            })
            ->whereNotIn('conceptopago_id', [2, 3])
            ->whereNull('deleted_at')
            ->get();

        // Clasificar por tipo (Ingreso: 7, 4, 9, 10 / Egreso: 8)
        foreach ($cajaItems as $item) {
            $tipo = $item->movimiento->tipodocumento_id;

            if (in_array($tipo, [7, 4, 9, 10])) {
                $ingresosVsEgresos['Ingreso'] += $item->moneda === 'PEN' ? $item->total : 0;
            } elseif ($tipo === 8) {
                $ingresosVsEgresos['Egreso'] += $item->moneda === 'PEN' ? $item->total : 0;
            }
        }

        $ingresosPorMetodo = DetMovcaja::with(['mediopago', 'movcaja.movimiento'])
            ->whereHas('movcaja', function ($q) use ($fechaInicio, $fechaFin, $sucursal_id) {
                $q->whereBetween('created_at', [$fechaInicio, $fechaFin])
                    ->where('sucursal_id', $sucursal_id)
                    ->whereNotIn('conceptopago_id', [2, 3])
                    ->whereHas('movimiento', fn($m) => $m->where('tipodocumento_id', 7)
                        ->orWhereIn('tipodocumento_id', [4, 9, 10]))
                    ->whereNull('deleted_at');
            })
            ->whereNull('deleted_at')
            ->get()
            ->groupBy(fn($i) => $i->Mediopago->descripcion ?? 'Otro')
            ->map(fn($grupo) => round($grupo->sum('monto'), 2));

        // Egresos por método de pago
        $egresosPorMetodo = DetMovcaja::with(['mediopago', 'movcaja.movimiento'])
            ->whereHas('movcaja', function ($q) use ($fechaInicio, $fechaFin, $sucursal_id) {
                $q->whereBetween('created_at', [$fechaInicio, $fechaFin])
                    ->where('sucursal_id', $sucursal_id)
                    ->whereNotIn('conceptopago_id', [2, 3])
                    ->whereHas('movimiento', fn($m) => $m->where('tipodocumento_id', 8)) // Solo egresos
                    ->whereNull('deleted_at');
            })
            ->whereNull('deleted_at')
            ->get()
            ->groupBy(fn($i) => $i->Mediopago->descripcion ?? 'Otro')
            ->map(fn($grupo) => round($grupo->sum('monto'), 2));

        $ingresosPorConcepto = Movcaja::with(['conceptopago', 'movimiento.tipodocumento'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->get();

        $conceptos = $ingresosPorConcepto
            ->filter(fn($m) => $m->movimiento && $m->movimiento->tipodocumento && $m->movimiento->tipodocumento->nombre === 'Ingreso' && $m->conceptopago->descripcion !== 'Apertura de caja')
            ->groupBy(fn($m) => $m->conceptopago->descripcion ?? 'Otro')
            ->map(fn($grupo) => round($grupo->sum('total'), 2));

        // Grupo "Paga cliente"
        $pagaCliente = $ingresosPorConcepto
            ->filter(fn($m) => $m->movimiento && in_array($m->movimiento->tipodocumento_id, [4, 10, 9]))
            ->sum('total');

        $conceptos['Pago de cliente'] = round($pagaCliente, 2);
        $ingresosPorConcepto = $conceptos;

        $egresosPorConceptoRaw = Movcaja::with(['conceptopago', 'movimiento.tipodocumento'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->get();

        $egresosPorConcepto = $egresosPorConceptoRaw
            ->filter(fn($m) => $m->movimiento && $m->movimiento->tipodocumento && ($m->movimiento->tipodocumento->nombre === 'Egreso') && $m->conceptopago->descripcion !== 'Cierre de caja')
            ->groupBy(fn($m) => $m->conceptopago->descripcion ?? 'Otro')
            ->map(fn($grupo) => round($grupo->sum('total'), 2));
        // Ejemplo de consulta para evolución de ingresos y egresos por fecha
        $ingresos = Movcaja::with(['movimiento.tipodocumento'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->get()
            ->filter(fn($m) => $m->movimiento && $m->movimiento->tipodocumento && ($m->movimiento->tipodocumento->nombre === 'Ingreso' || in_array($m->movimiento->tipodocumento_id, [4, 9, 10])) && $m->conceptopago->descripcion !== 'Apertura de caja')
            ->groupBy(fn($m) => \Carbon\Carbon::parse($m->created_at)->format('Y-m-d'))
            ->map(function ($items, $fecha) {
                return (object)[
                    'fecha' => $fecha,
                    'total' => $items->sum('total')
                ];
            })
            ->values();
        $egresos = Movcaja::with(['movimiento.tipodocumento'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->get()
            ->filter(fn($m) => $m->movimiento &&  ($m->movimiento->tipodocumento->nombre === 'Egreso' || in_array($m->movimiento->tipodocumento_id, [8])) && $m->conceptopago->descripcion !== 'Cierre de caja')
            ->groupBy(fn($m) => \Carbon\Carbon::parse($m->created_at)->format('Y-m-d'))
            ->map(function ($items, $fecha) {
                return (object)[
                    'fecha' => $fecha,
                    'total' => $items->sum('total')
                ];
            })
            ->values();
        $fechas = collect($ingresos)->pluck('fecha')
            ->merge(collect($egresos)->pluck('fecha'))
            ->unique()->sort()->values();

        $evolucionTiposDocumento = [];
        foreach ($fechas as $fecha) {
            $evolucionTiposDocumento[] = [
                'fecha' => $fecha,
                'ingresos' => (float) ($ingresos->firstWhere('fecha', $fecha)->total ?? 0),
                'egresos' => (float) ($egresos->firstWhere('fecha', $fecha)->total ?? 0),
            ];
        }

        return view('report.dashboardcaja', compact(
            'ingresosPorConcepto',
            'egresosPorConcepto',
            'ingresosVsEgresos',
            'ingresosPorMetodo',
            'egresosPorMetodo',
            'evolucionTiposDocumento',
            'fechaInicio',
            'fechaFin'
        ));
    }


    public function dashmovhabitacion(Request $request)
    {
        $sucursal_id = Auth::user()->persona->sucursal_id;

        // Si existen en request usar esas fechas, si no usar valores por defecto
        $fechaInicio = $request->filled('fecha_inicio')
            ? Carbon::parse($request->fecha_inicio)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();


        $fechaFin = $request->filled('fecha_fin')
            ? Carbon::parse($request->fecha_fin)->endOfDay()
            : Carbon::now()->endOfDay();

        $topHabitacionesLimit = (int) $request->input('limit_top_habitaciones', $request->input('limit', 10));
        if (!in_array($topHabitacionesLimit, [10, 25, 50], true)) {
            $topHabitacionesLimit = 10;
        }

        $topHabitacionesOrden = strtolower((string) $request->input('orden_top_habitaciones', 'desc'));
        $topHabitacionesOrden = $topHabitacionesOrden === 'asc' ? 'asc' : 'desc';

        $categoriasHabitaciones = Tipohabitacion::where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $categoriasTopHabitacionesSeleccionadas = collect($request->input('categorias_top_habitaciones', []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values();

        if ($categoriasTopHabitacionesSeleccionadas->isEmpty()) {
            $categoriasTopHabitacionesSeleccionadas = $categoriasHabitaciones->pluck('id')
                ->map(fn($id) => (int) $id)
                ->values();
        }

        $topCategoriasLimit = (int) $request->input('limit_top_categorias', 10);
        if (!in_array($topCategoriasLimit, [10, 25, 50], true)) {
            $topCategoriasLimit = 10;
        }

        $topCategoriasOrden = strtolower((string) $request->input('orden_top_categorias', 'desc'));
        $topCategoriasOrden = $topCategoriasOrden === 'asc' ? 'asc' : 'desc';

        //  dd($fechaInicio, $fechaFin);
        $evolucionAlojamientos = [];

        $anulados = \App\Models\Movhabitacion::where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->whereHas('movimiento', function ($q) {
                $q->where('situacion', 'D'); // Asegúrate de que el tipodocumento_id para anulados sea 8
            })
            ->whereBetween('fechaingreso', [$fechaInicio, $fechaFin])
            ->selectRaw('DATE(fechaingreso) as fecha, COUNT(*) as cantidad')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $finalizados = \App\Models\Movhabitacion::where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->where('situacion', 'C')
            ->whereBetween('fechaingreso', [$fechaInicio, $fechaFin])
            ->selectRaw('DATE(fechaingreso) as fecha, COUNT(*) as cantidad')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $conProductos = \App\Models\Movhabitacion::where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->whereBetween('fechaingreso', [$fechaInicio, $fechaFin])
            ->whereHas('detmovhabitacion')
            ->selectRaw('DATE(fechaingreso) as fecha, COUNT(*) as cantidad')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Unificar fechas
        $fechas = collect($anulados)->pluck('fecha')
            ->merge(collect($finalizados)->pluck('fecha'))
            ->merge(collect($conProductos)->pluck('fecha'))
            ->unique()->sort()->values();

        foreach ($fechas as $fecha) {
            $evolucionAlojamientos[] = [
                'fecha' => $fecha,
                'anulados' => (int) ($anulados->firstWhere('fecha', $fecha)->cantidad ?? 0),
                'finalizados' => (int) ($finalizados->firstWhere('fecha', $fecha)->cantidad ?? 0),
                'con_productos' => (int) ($conProductos->firstWhere('fecha', $fecha)->cantidad ?? 0),
            ];
        }
        // Fechas y sucursal
        $hoy = Carbon::now()->format('Y-m-d');

        // Habitaciones ocupadas actualmente
        $ocupadas = Habitacion::where('sucursal_id', $sucursal_id)
            ->where('situacion', 'Ocupada') // L = Limpieza (ajusta según codificación)
            ->count();

        // Reservas activas para hoy
        $reservasHoy = MovreservaHabitacion::whereHas('movreserva', function ($q) use ($sucursal_id) {
            $q->where('sucursal_id', $sucursal_id)->whereNull('deleted_at');
        })
            ->whereDate('fechacheckin', $hoy)
            ->whereNull('deleted_at')
            ->count();

        // Habitaciones en limpieza
        $enLimpieza = Habitacion::where('sucursal_id', $sucursal_id)
            ->where('situacion', 'Limpieza') // L = Limpieza (ajusta según codificación)
            ->count();

        // Habitaciones en mantenimiento
        $enMantenimiento = Habitacion::where('sucursal_id', $sucursal_id)
            ->where('situacion', 'Mantenimiento') // M = Mantenimiento (ajusta según codificación)
            ->count();
        $topHabitaciones = MovhabitacionHabitacion::with(['habitacion', 'habitacion.tipohabitacion'])
            ->whereHas('habitacion', function ($q) use ($sucursal_id, $categoriasTopHabitacionesSeleccionadas) {
                $q->where('sucursal_id', $sucursal_id)
                    ->whereNull('deleted_at');
                if ($categoriasTopHabitacionesSeleccionadas->isNotEmpty()) {
                    $q->whereIn('tipohabitacion_id', $categoriasTopHabitacionesSeleccionadas->all());
                }
            })
            ->whereNull('deleted_at')
            ->whereBetween('fechacheckin', [$fechaInicio, $fechaFin]) // 👈 filtro por rango de fechas
            ->selectRaw('habitacion_id, COUNT(*) as total')
            ->groupBy('habitacion_id')
            ->orderBy('total', $topHabitacionesOrden)
            ->take($topHabitacionesLimit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->habitacion->id,
                    'name' => $item->habitacion->numero,
                    'value' => $item->total,
                    'pictureSettings' => [
                        'src' => '/img/habitaciones/' . $item->habitacion->numero . '.svg'
                    ],
                    'columnFill' => '#4CAF50'
                ];
            });
        $topCategorias = DB::table('movhabitacion_habitacion as mhh')
            ->join('habitacion as h', 'h.id', '=', 'mhh.habitacion_id')
            ->leftJoin('tipohabitacion as th', 'th.id', '=', 'h.tipohabitacion_id')
            ->whereNull('mhh.deleted_at')
            ->whereNull('h.deleted_at')
            ->where('h.sucursal_id', $sucursal_id)
            ->whereBetween('mhh.fechacheckin', [$fechaInicio, $fechaFin])
            ->selectRaw("COALESCE(th.nombre, 'Sin categoria') as category, COUNT(*) as value")
            ->groupByRaw("COALESCE(th.nombre, 'Sin categoria')")
            ->orderBy('value', $topCategoriasOrden)
            ->limit($topCategoriasLimit)
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'value' => (int) $item->value
                ];
            })->values();
        //    dd($topCategorias );
        $alojamientosPorPiso = DB::table('movhabitacion_habitacion as mhh')
            ->join('habitacion as h', 'h.id', '=', 'mhh.habitacion_id')
            ->leftJoin('piso as p', 'p.id', '=', 'h.piso_id')
            ->leftJoin('tipohabitacion as th', 'th.id', '=', 'h.tipohabitacion_id')
            ->whereNull('mhh.deleted_at')
            ->whereNull('h.deleted_at')
            ->where('h.sucursal_id', $sucursal_id)
            ->whereBetween('mhh.fechacheckin', [$fechaInicio, $fechaFin])
            ->selectRaw("COALESCE(p.nombre, 'Sin piso') as piso_nombre, COALESCE(th.nombre, 'Sin tipo') as tipo_nombre, COUNT(*) as cantidad")
            ->groupByRaw("COALESCE(p.nombre, 'Sin piso'), COALESCE(th.nombre, 'Sin tipo')")
            ->orderBy('piso_nombre')
            ->get()
            ->groupBy('piso_nombre')
            ->map(function ($items, $pisoNombre) {
                $formatted = [];
                $total = 0;
                foreach ($items as $item) {
                    $tipo = $item->tipo_nombre;
                    $count = (int) $item->cantidad;
                    $formatted[$tipo] = $count;
                    $total += $count;
                }

                $formatted['quantity'] = $total;

                return [
                    'provider' => $pisoNombre,
                    'data' => $formatted
                ];
            })->values();
        // Alojamientos por responsable
        $alojamientosPorResponsable = DB::table('movhabitacion as mh')
            ->leftJoin('movimiento as m', 'm.id', '=', 'mh.movimiento_id')
            ->leftJoin('persona as p', 'p.id', '=', 'm.responsable_id')
            ->where('mh.sucursal_id', $sucursal_id)
            ->whereNull('mh.deleted_at')
            ->whereBetween('mh.fechaingreso', [$fechaInicio, $fechaFin])
            ->selectRaw("COALESCE(p.nombres, 'Sin responsable') as responsable, COUNT(*) as cantidad")
            ->groupByRaw("COALESCE(p.nombres, 'Sin responsable')")
            ->orderByDesc('cantidad')
            ->get()
            ->map(function ($item) {
                return [
                    'responsable' => $item->responsable,
                    'cantidad' => (int) $item->cantidad,
                ];
            })->values();

        return view('report.dashboardalojamientos', compact(
            'ocupadas',
            'reservasHoy',
            'enLimpieza',
            'enMantenimiento',
            'categoriasHabitaciones',
            'categoriasTopHabitacionesSeleccionadas',
            'topHabitaciones',
            'topHabitacionesLimit',
            'topHabitacionesOrden',
            'topCategorias',
            'topCategoriasLimit',
            'topCategoriasOrden',
            'alojamientosPorPiso',
            'alojamientosPorResponsable',
            'evolucionAlojamientos',
            'fechaInicio',
            'fechaFin'
        ));
    }

    // DashboardController.php
    public function evolucionHabitacion($id)
    {
        $datos = MovhabitacionHabitacion::where('habitacion_id', $id)
            ->whereNull('deleted_at')
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $fechas = $datos->pluck('fecha');
        $valores = $datos->pluck('total');

        return response()->json([
            'fechas' => $fechas,
            'valores' => $valores
        ]);
    }

    public function alojamientosHabitacion(Request $request, $id)
    {
        $sucursal_id = Auth::user()->persona->sucursal_id;

        $fechaInicio = $request->filled('fecha_inicio')
            ? Carbon::parse($request->fecha_inicio)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $fechaFin = $request->filled('fecha_fin')
            ? Carbon::parse($request->fecha_fin)->endOfDay()
            : Carbon::now()->endOfDay();

        $habitacion = Habitacion::where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $movimientoHabitacion = Movhabitacion::with([
            'habitacion',
            'huespeds.persona.personamaestro',
            'movhabitacion_habitacion' => function ($query) use ($habitacion) {
                $query->where('habitacion_id', $habitacion->id)
                    ->whereNull('deleted_at');
            },
        ])
            ->where('sucursal_id', $sucursal_id)
            ->whereBetween('fechaingreso', [$fechaInicio, $fechaFin])
            ->whereHas('movhabitacion_habitacion', function ($query) use ($habitacion) {
                $query->where('habitacion_id', $habitacion->id)
                    ->whereNull('deleted_at');
            })
            ->orderBy('fechaingreso', 'desc')
            ->get()
            ->map(function ($movimiento) use ($habitacion) {
                $detalleHabitacion = $movimiento->movhabitacion_habitacion->first();

                if ($detalleHabitacion) {
                    $movimiento->preciohabitacion = $detalleHabitacion->tarifa ?? $movimiento->preciohabitacion;
                    $movimiento->early_checkin = $detalleHabitacion->early_checkin ?? $movimiento->early_checkin;
                    $movimiento->late_checkout = $detalleHabitacion->late_checkout ?? $movimiento->late_checkout;
                    $movimiento->total = $detalleHabitacion->total ?? $movimiento->total;
                    $movimiento->horas = $detalleHabitacion->horas ?? $movimiento->horas;
                    $movimiento->dias = $detalleHabitacion->dias ?? $movimiento->dias;
                    $movimiento->situacion = $detalleHabitacion->situacion ?? $movimiento->situacion;
                    $movimiento->fechasalida = $detalleHabitacion->fechacheckout ?? $movimiento->fechasalida;
                }

                $movimiento->setRelation('habitacion', $habitacion);

                return $movimiento;
            });

        $tituloHistorial = 'Historial de Alojamientos - Habitacion ' . $habitacion->numero;
        $subtituloHistorial = 'Rango: ' . $fechaInicio->format('Y-m-d') . ' al ' . $fechaFin->format('Y-m-d');

        return view('huesped.historial-alojamientos', compact(
            'movimientoHabitacion',
            'tituloHistorial',
            'subtituloHistorial'
        ));
    }
}






