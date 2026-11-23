<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Resumen de Alojamientos</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">
    <style>
        body {
            font-family: sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 30px;
        }

        header.left,
        header.right {
            position: absolute;
            top: 10px;
            font-size: 10px;
        }

        header.left {
            left: 30px;
        }

        header.right {
            right: 30px;
        }

        footer {
            text-align: center;
            font-size: 11px;
            margin-top: 40px;
        }

        h5 {
            text-align: center;
            margin-top: 40px;
            font-size: 18px;
        }

        .habitacion-num {
            font-size: 15px;
            font-weight: bold;
            line-height: 1.1;
        }

        .habitacion-precio {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.1;
        }

        .cabecera-columna {
            font-size: 9px;
            font-weight: bold;
            padding: 4px 0;
            line-height: 1.1;
        }

        .yellow { background-color: #e6ff82; }
        .orange { background-color: #FFA726; }
        .pink { background-color: #f8bbd0; }

        .legend {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
        }

        .legend-item {
            display: inline-block;
            margin: 0 20px;
        }

        .legend-color {
            display: inline-block;
            width: 18px;
            height: 18px;
            margin-right: 6px;
            vertical-align: middle;
            border: 1px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 30px;
        }

        .tabla-habitaciones th,
        .tabla-habitaciones td {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            padding: 4px 3px;
            height: 22px;
            line-height: 1.1;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <h5>Resumen de Alojamientos</h5>
    <p style="text-align: center; font-size: 10px; margin-top: -20px">
        Desde: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y H:i') }}  Hasta: {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y H:i') }}
    </p>

    <!-- Tabla superior de control -->
    <table width="100%">
        <tr>
            <td width="35%" valign="top">
                <table style="border-collapse: collapse; width: 100%; font-size: 12px;">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align: center; border: 1px solid #000; font-weight: bold;">
                                Cuartos que salieron
                            </th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #000;">HORA</th>
                            <th style="border: 1px solid #000;">N° HAB</th>
                            <th style="border: 1px solid #000;">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 5; $i++)
                            <tr>
                                <td style="border: 1px solid #000; height: 25px;"></td>
                                <td style="border: 1px solid #000;"></td>
                                <td style="border: 1px solid #000;"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </td>
            <td width="30%" valign="top" style="text-align: center;">
                <div style="font-weight: bold; font-size: 18px; margin-top: 25px; text-decoration: underline;">
                    CONTROL DIARIO
                </div>
            </td>
            <td width="35%" valign="top" style="font-size: 13px; padding-left: 10px;">
                <div style="margin-bottom: 20px;">
                    <strong>Firma:</strong> <span style="border-bottom: 1px solid #000; display: inline-block; width: 150px;"></span>
                </div>
                <div>
                    <strong>Fecha:</strong>
                    <span style="border-bottom: 1px solid #000; display: inline-block; width: 30px;"></span> /
                    <span style="border-bottom: 1px solid #000; display: inline-block; width: 30px;"></span> /
                    <span style="border-bottom: 1px solid #000; display: inline-block; width: 60px;"></span>
                </div>
            </td>
        </tr>
    </table>

@php
    $columnasPorPagina = 4; // 4 habitaciones por fila
    $filasPorPagina = 22;    // 8 filas por página (total 32 habitaciones)
    $habitacionesPorPagina = $columnasPorPagina * $filasPorPagina;

    $paginas = array_chunk($habitaciones->toArray(), $habitacionesPorPagina);
@endphp

@foreach ($paginas as $pagina)
    @php
        $totalHabitacionesPagina = count($pagina);
        $filasReales = (int) ceil($totalHabitacionesPagina / $columnasPorPagina);
        $grid = [];

        // Llenado vertical: arriba hacia abajo por columna
        for ($fila = 0; $fila < $filasReales; $fila++) {
            $filaActual = [];
            for ($columna = 0; $columna < $columnasPorPagina; $columna++) {
                $indice = ($columna * $filasReales) + $fila;
                $filaActual[] = $pagina[$indice] ?? null;
            }
            $grid[] = $filaActual;
        }
    @endphp

    <table class="tabla-habitaciones">
        <thead>
            <tr>
                @for ($i = 0; $i < $columnasPorPagina; $i++)
                    <th></th>
                    <th class="cabecera-columna">N° HAB</th>
                    <th class="cabecera-columna">COSTO</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($grid as $fila)
                <tr>
                    @foreach ($fila as $hab)
                        @if ($hab)
                            <td></td>
                            <td class="{{ $hab['color'] }}">
                                <div class="habitacion-num">{{ $hab['numero'] }}</div>
                            </td>
                            <td class="{{ $hab['color'] }}">
                                <div class="habitacion-precio">{{ number_format($hab['precio'], 2) }}</div>
                            </td>
                        @else
                            <td></td><td></td><td></td>
                        @endif
                    @endforeach
</tr>
            @endforeach
        </tbody>
    </table>
    <div class="legend">
        <div class="legend-item"><span class="legend-color yellow"></span> Turno Día</div>
        <div class="legend-item"><span class="legend-color orange"></span> Turno Noche</div>
        <div class="legend-item"><span class="legend-color pink"></span> No alquilado</div>
    </div>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach


</body>
</html>


