<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Consumo</title>
    <style>
        @page { margin: 12px 16px 18px 16px; }
        body {
            font-family: "Times New Roman", Times, serif;
            color: #000;
            font-size: 12px;
            line-height: 1.15;
        }
        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .logo { width: 88px; max-height: 88px; object-fit: contain; }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            padding-top: 54px;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .upper { text-transform: uppercase; }
        .thick-top { border-top: 4px solid #000; }
        .thick-bottom { border-bottom: 4px solid #000; }
        .thin-bottom { border-bottom: 2px solid #000; }
        .meta td { padding: 1px 2px; }
        .guests td { padding: 1px 2px; }
        .details th {
            border-top: 4px solid #000;
            border-bottom: 4px solid #000;
            padding: 2px 3px;
            font-size: 12px;
            text-align: left;
        }
        .details td {
            padding: 1px 3px;
            font-size: 12px;
        }
        .details .num { text-align: right; white-space: nowrap; }
        .details .date { text-align: center; white-space: nowrap; }
        .totals td {
            padding-top: 36px;
            font-weight: bold;
            white-space: nowrap;
        }
    </style>
</head>
<body>
@php
    $logoUri = 'file:///' . str_replace('\\', '/', $logoPath);
    $fechaDoc = now();
    $dias = ['DOMINGO', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
    $meses = [1 => 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SETIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
    $fechaTexto = $dias[$fechaDoc->dayOfWeek] . ' ' . $fechaDoc->day . ' DE ' . $meses[(int) $fechaDoc->month] . ' ' . $fechaDoc->year;
    $distrito = $sucursal->ubigeo;
    $provincia = $distrito?->ubigeo;
    $departamento = $provincia?->ubigeo;
    $direccion = collect([$sucursal->direccion, $distrito?->descripcion, $provincia?->descripcion, $departamento?->descripcion])
        ->filter()
        ->join(' - ');
    $habitaciones = $movhabitacion->movhabitacion_habitacion
        ->pluck('habitacion.numero')
        ->filter()
        ->join(', ');
    $reservaNumero = $movhabitacion->movreserva?->movimiento?->numero ?? $movhabitacion->movimiento?->numero ?? $movhabitacion->id;
    $principalNombre = trim(($principal->nombres ?? '') . ' ' . ($principal->apellidos ?? ''));
    $principalDoc = $principal?->personamaestro?->nrodoc ?? '';
@endphp

<table class="header">
    <tr>
        <td width="22%"><img src="{{ $logoUri }}" class="logo" alt="Logo"></td>
        <td width="56%" class="title">DETALLE DE CONSUMO</td>
        <td width="22%" class="right" style="padding-top: 96px;">
            <span>{{ $fechaTexto }}</span><br>
            <span>{{ $fechaDoc->format('H:i:s') }}</span>
        </td>
    </tr>
</table>

<table class="meta">
    <tr>
        <td colspan="4" class="upper">{{ $sucursal->razonsocial ?? $sucursal->nombrecomercial }}</td>
    </tr>
    <tr>
        <td colspan="4">RUC : {{ $sucursal->ruc }}</td>
    </tr>
    <tr>
        <td colspan="4" class="upper">{{ $direccion }}</td>
    </tr>
    <tr class="thick-top">
        <td width="14%" class="bold"># RESERVA:</td>
        <td width="36%">{{ $reservaNumero }}</td>
        <td width="12%" class="bold">HAB:</td>
        <td width="38%">{{ $habitaciones ?: '-' }}</td>
    </tr>
    <tr>
        <td class="bold">A NOMBRE:</td>
        <td class="upper">{{ $principalNombre ?: 'CLIENTES VARIOS' }}</td>
        <td class="right bold">CHECK IN:</td>
        <td class="right">{{ $movhabitacion->fechaingreso ? \Carbon\Carbon::parse($movhabitacion->fechaingreso)->format('d.m.Y') : '-' }}</td>
    </tr>
    <tr>
        <td class="bold">RAZON SOCIAL:</td>
        <td class="upper">{{ $movhabitacion->movimiento?->persona ?? $principalNombre ?: 'CLIENTES VARIOS' }}</td>
        <td class="right bold">CHECK OUT:</td>
        <td class="right">{{ ($movhabitacion->fecha_salida_real ?: $movhabitacion->fechasalida) ? \Carbon\Carbon::parse($movhabitacion->fecha_salida_real ?: $movhabitacion->fechasalida)->format('d.m.Y') : '-' }}</td>
    </tr>
    <tr>
        <td class="bold">RUC:</td>
        <td>{{ $principalDoc ?: '-' }}</td>
        <td></td>
        <td></td>
    </tr>
    <tr class="thick-bottom">
        <td class="bold">DIRECCION</td>
        <td colspan="3" class="upper">{{ $principal->direccion ?? '' }}</td>
    </tr>
</table>

<table class="guests thin-bottom">
    @forelse ($movhabitacion->huespeds as $huesped)
        <tr>
            <td width="52%" class="upper">{{ trim(($huesped->persona?->nombres ?? '') . ' ' . ($huesped->persona?->apellidos ?? '')) }}</td>
            <td width="16%" class="center">PERUANA</td>
            <td width="10%" class="center">DNI</td>
            <td width="22%">{{ $huesped->persona?->personamaestro?->nrodoc }}</td>
        </tr>
    @empty
        <tr>
            <td width="52%">CLIENTES VARIOS</td>
            <td width="16%" class="center">PERUANA</td>
            <td width="10%" class="center">DNI</td>
            <td width="22%">0</td>
        </tr>
    @endforelse
</table>

<table class="details">
    <thead>
        <tr>
            <th width="31%">PRODUCTO DE VENTA</th>
            <th width="12%"></th>
            <th width="9%">PRECIO</th>
            <th width="8%" class="center">CANT</th>
            <th width="14%">SUB TOTAL</th>
            <th width="12%">IGV</th>
            <th width="14%">TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($detalles as $detalle)
            <tr>
                <td class="upper">{{ $detalle['descripcion'] }}</td>
                <td class="date">{{ $detalle['fecha'] ? $detalle['fecha']->format('j/m/Y') : '' }}</td>
                <td class="num">S/ {{ number_format($detalle['precio'], 2) }}</td>
                <td class="center">{{ rtrim(rtrim(number_format($detalle['cantidad'], 2), '0'), '.') }}</td>
                <td class="num">S/ {{ number_format($detalle['subtotal'], 2) }}</td>
                <td class="num">S/ {{ number_format($detalle['igv'], 3) }}</td>
                <td class="num">S/ {{ number_format($detalle['total'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td width="55%" class="right">TOTALES</td>
        <td width="15%" class="num">S/ {{ number_format($totales['subtotal'], 2) }}</td>
        <td width="15%" class="num">S/ {{ number_format($totales['igv'], 2) }}</td>
        <td width="15%" class="num">S/ {{ number_format($totales['total'], 2) }}</td>
    </tr>
</table>
</body>
</html>
