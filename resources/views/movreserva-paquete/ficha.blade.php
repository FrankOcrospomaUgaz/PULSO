<!doctype html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8">
    <title>Ficha de Reserva de Paquete</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .container {
            width: 100%;
            margin: auto;
        }

        .header {
            text-align: right;
            font-size: 12px;
        }

        .header h2 {
            text-align: left;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            padding: 8px;
            background: #f2f2f2;
            border: 1px solid #ddd;
            margin-top: 15px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .details-table td {
            padding: 5px;
        }

        .details-table .label {
            font-weight: bold;
            width: 30%;
        }

        .total-table {
            width: 100%;
            border: 1px solid black;
            margin-top: 10px;
        }

        .total-table td {
            padding: 5px;
            border-bottom: 1px solid black;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
        }

        .package-image {
            max-width: 150px;
            margin-top: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Encabezado del Hotel -->
        <div class="header">
            <h2>{{ $sucursal->razonsocial }}</h2>
            <p>
                {{ $sucursal->direccion }}<br>
                {{ $sucursal->ciudad }}, {{ $sucursal->pais }}<br>
                Phone: {{ $sucursal->telefono }}<br>
                Email: {{ $sucursal->email }}<br>
                Website: {{ $sucursal->web }}
            </p>
        </div>

        <!-- Registro de Reserva de Paquete -->
        <div class="section-title">Ficha de Reserva de Paquete</div>
        <table class="details-table">
            <tr>
                <td class="label bold">Reserva ID #:</td>
                <td class="bold">{{ str_pad($reservaData['id'], 5, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td class="label bold">Nombre del Paquete:</td>
                <td class="bold">{{ $reservaData['paquete']['nombre'] }}</td>
            </tr>
            <tr>
                <td class="label bold">Precio del Paquete:</td>
                <td class="bold">{{ $reservaData['moneda'] }} {{ number_format($reservaData['precio_paquete'], 2) }}</td>
            </tr>
            @if($reservaData['paquete']['imagen'])
                <tr>
                    <td class="label bold">Imagen del Paquete:</td>
                    <td>
                        <img class="package-image" src="{{ public_path('storage/' . $reservaData['paquete']['imagen']) }}" alt="Imagen Paquete">
                    </td>
                </tr>
            @endif
            <tr>
                <td class="label bold">Descripción del Paquete:</td>
                <td>{!! $reservaData['paquete']['contenido'] !!}</td>
            </tr>
        </table>

        <!-- Datos del Cliente -->
        <div class="section-title">Datos del Cliente</div>
        <table class="details-table">
            <tr>
                <td class="label">Nombre:</td>
                <td class="bold">{{ $reservaData['nombre_cliente'] }}</td>
            </tr>
            <tr>
                <td class="label">Teléfono Fijo:</td>
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
            <tr>
                <td class="label">Dirección:</td>
                <td>{{ $reservaData['direccion'] }}</td>
            </tr>
        </table>

        <!-- Detalles de la Reserva -->
        <div class="section-title">Detalles de la Reserva</div>
        <table class="details-table">
            <tr>
                <td class="label">Fecha de Reserva:</td>
                <td>{{ $reservaData['fecha'] }}</td>
            </tr>
            <tr>
                <td class="label">Número de Personas:</td>
                <td>{{ $reservaData['nropersonas'] }}</td>
            </tr>
            <tr>
                <td class="label">Estado:</td>
                <td>{{ $reservaData['estado'] }}</td>
            </tr>
            <tr>
                <td class="label">Observación:</td>
                <td>{{ $reservaData['observacion'] }}</td>
            </tr>
        </table>

        <!-- Cargos de Reservación -->
        <div class="section-title">Cargos de Reservación</div>
        <table class="total-table">
            <tr>
                <td class="label">Total:</td>
                <td class="right bold">{{ $reservaData['moneda'] }} {{ number_format($reservaData['total'], 2) }}</td>
            </tr>
        </table>

        <p class="right">
            <strong>Fecha: {{ date('d M, Y') }}</strong>
        </p>

        <p class="right">
            <strong>Firma: ________________________</strong>
        </p>

        <!-- Footer -->
        <div class="footer">
            <a href="https://www.hotelhub.com.pe" style="text-decoration: none; color: blue;">Desarrollado por HotelHUB</a>
        </div>
    </div>

</body>

</html>
