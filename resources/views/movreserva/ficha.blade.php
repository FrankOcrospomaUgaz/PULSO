<!doctype html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8">
    <title>Ficha de Reserva</title>

    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 25px;
        }

        .container {
            width: 100%;
            margin: auto;
        }
.header {
    display: flex;
    justify-content: space-between;
    align-items: center; /* centra verticalmente ambos elementos */
    border-bottom: 2px solid #06beb8;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.hotel-info h2 {
    margin: 0;
    line-height: 1; /* evita espacio extra vertical */
    display: flex;
    align-items: center; /* centra si hay varias líneas */
}

.hotel-logo img {
    max-width: 120px;
    max-height: 80px;
    object-fit: contain;
    display: block;
}


.hotel-logo {
    flex-shrink: 0;
    text-align: right;
}



        .hotel-info {
            font-size: 12px;
            line-height: 1.4;
        }

        .hotel-info h2 {
            font-family: 'Times New Roman', serif;
            font-size: 22px;
            margin: 0;
            color: #06beb8;
                flex: 1;
    text-align: left;
        }

        /* Títulos de sección */
        .section-title {
            font-size: 15px;
            font-weight: bold;
            padding: 8px 10px;
            background: #ceffff;
            border-left: 4px solid #06beb8;
            margin-top: 20px;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        td, th {
            padding: 6px 8px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 35%;
            color: #555;
        }

        .details-table td {
            border-bottom: 1px solid #eee;
        }

        .habitaciones-table th {
            background-color: #f0f8f8;
            font-weight: bold;
            border: 1px solid #ddd;
            text-align: left;
        }

        .habitaciones-table td {
            border: 1px solid #ddd;
        }

        .total-table {
            margin-top: 10px;
            border: 1px solid #ccc;
        }

        .total-table td {
            border-bottom: 1px solid #eee;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        /* Firma y fecha */
        .signature-section {
            margin-top: 30px;
            text-align: right;
            font-size: 13px;
        }

        .signature-line {
            margin-top: 40px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #888;
        }

        .footer a {
            color: #06beb8;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Encabezado del Hotel -->
<div class="header">
    <div class="hotel-info">
        <h2 style="margin: 0;">{{ $sucursal->razonsocial }}</h2>
    </div>
    <div class="hotel-logo">
        @if ($logoDataUri)
            <img src="{{ $logoDataUri }}" alt="Logo de {{ $sucursal->razonsocial }}" class="img-fluid">
        @endif
    </div>
</div>


    <!-- Registro de Reserva -->
    <div class="section-title">Tarjeta de Registro</div>
    <table class="details-table">
        <tr>
            <td class="label">Código de Reserva:</td>
            <td class="bold">{{ str_pad($reservaData['id'], 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
    </table>

    <!-- Habitaciones -->
    <div class="section-title">Habitaciones</div>
    @if($reservaData['modo'] === 'G')
        <table class="habitaciones-table">
            <thead>
            <tr>
                <th>N° Habitación</th>
                <th>Tipo</th>
                <th>Tarifa</th>
                <th>Noches</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($reservaData['habitaciones'] as $h)
                <tr>
                    <td>{{ $h['habitacion'] }}</td>
                    <td>{{ $h['tipo'] }}</td>
                    <td>{{ $reservaData['moneda'] }} {{ number_format($h['costo'], 2) }}</td>
                    <td>{{ $h['dias'] }}</td>
                    <td>{{ $reservaData['moneda'] }} {{ number_format($h['total'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <table class="details-table">
            <tr>
                <td class="label">Tipo de habitación:</td>
                <td class="bold">{{ $reservaData['habitaciones'][0]['tipo'] }}</td>
            </tr>
            <tr>
                <td class="label">Habitación:</td>
                <td class="bold">{{ $reservaData['habitaciones'][0]['habitacion'] }}</td>
            </tr>
        </table>
    @endif

    <!-- Datos del Huésped -->
    <div class="section-title">Datos del Huésped</div>
    <table class="details-table">
        <tr>
            <td class="label">Nombre:</td>
            <td class="bold">{{ $reservaData['nombre_cliente'] }}</td>
        </tr>
        <tr>
            <td class="label">Teléfono:</td>
            <td>{{ $reservaData['telefono'] }}</td>
        </tr>
        <tr>
            <td class="label">Móvil:</td>
            <td class="bold">{{ $reservaData['movil'] }}</td>
        </tr>
        <tr>
            <td class="label">Correo Electrónico:</td>
            <td class="bold">{{ $reservaData['email'] }}</td>
        </tr>
    </table>

    <!-- Detalles de la Estadía -->
    <div class="section-title">Detalles de la Estadía</div>
    <table class="details-table">
        <tr>
            <td class="label">Fecha(s):</td>
            <td>{{ $reservaData['fecha'] }}</td>
        </tr>
        <tr>
            <td class="label">Noches:</td>
            <td>{{ $reservaData['nronoches'] }}</td>
        </tr>
        <tr>
            <td class="label">Personas:</td>
            <td>{{ $reservaData['nropersonas'] }} Adultos</td>
        </tr>
    </table>

    <!-- Cargos -->
    <div class="section-title">Cargos de Reservación</div>
    <table class="total-table">
        <tr>
            <td class="label">Cargos de reserva (Tarifa):</td>
            <td class="right">{{ $reservaData['moneda'] }} {{ number_format(array_sum(array_column($reservaData['habitaciones'], 'costo')), 2) }}</td>
        </tr>
        <tr>
            <td class="label">Impuesto Total:</td>
            <td class="right">{{ $reservaData['moneda'] }} {{ number_format(0, 2) }}</td>
        </tr>
        <tr>
            <td class="label bold">Monto Total:</td>
            <td class="right bold">{{ $reservaData['moneda'] }} {{ number_format($reservaData['total'], 2) }}</td>
        </tr>
        <tr>
            <td class="label">Monto Pagado:</td>
            <td class="right">{{ $reservaData['moneda'] }} {{ number_format($reservaData['adelanto'], 2) }}</td>
        </tr>
        <tr>
            <td class="label">Saldo Pendiente:</td>
            <td class="right">{{ $reservaData['moneda'] }} {{ number_format($reservaData['saldo'], 2) }}</td>
        </tr>
    </table>

    <!-- Firma y fecha -->
    <div class="signature-section">
@php
    setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'es_ES');
@endphp

<p><strong>Fecha:</strong> {{ strftime('%d %B, %Y') }}</p>
        <div class="signature-line">
            _______________________<br>
            Firma del Cliente
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <a href="https://www.hotelhub.com.pe">Desarrollado por HotelHUB</a>
    </div>
</div>

</body>
</html>
