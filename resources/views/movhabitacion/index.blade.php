@extends('layouts.app')

@section('content')
    <style>
        .card-habitacion {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            /* Sombra más suave */
            transition: transform 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;

            color: #000000;
            /* Texto en blanco */

        }

        /* Asegura que todas las tarjetas de las habitaciones tengan la misma altura */
        .habitacion-column {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .habitacion-item {
            display: flex;
            flex-direction: column;
            flex: 1 1 100%;
            /* Por defecto en móviles: 1 tarjeta por fila */
            max-width: 100%;
            min-height: 350px;
        }

        @media (min-width: 600px) {
            .habitacion-item {
                flex: 1 1 48%;
                /* 2 tarjetas por fila */
                max-width: 48%;
            }
        }

        @media (min-width: 992px) {
            .habitacion-item {
                flex: 1 1 31%;
                /* 3 tarjetas por fila */
                max-width: 31%;
            }
        }

        @media (min-width: 1200px) {
            .habitacion-item {
                flex: 1 1 24%;
                /* 4 tarjetas por fila solo si hay mucho espacio */
                max-width: 24%;
            }
        }


        .custom-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            /* Hace que la tarjeta ocupe toda la altura disponible */
            justify-content: space-between;
            /* Asegura que el contenido se distribuya bien */
        }



        .card-item-body small {
            display: block;
            font-size: 0.9em;
            color: #000000;
            padding-top: 8px;

            /* Ajustar el color del texto secundario */
        }

        .card-habitacion:hover {
            transform: scale(1.05);
            /* Aumentar el efecto de hover */
        }

        .card-item-title {
            font-weight: bold;
            font-size: 1.1em;
            color: #333;
            background-color: white;
            border-radius: 20px;
            padding: 5px;
            justify-content: center;
            text-align: center;
        }

        .numerohabitacion {
            font-weight: bold;
            color: #333;
            background-color: white;
            border-radius: 20px;
            padding: 5px;
            display: inline-block;
            /* Ajusta el ancho al contenido */
        }


        .card-item-icon {
            font-size: 1.5em;
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .card-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            background-color: white;
            border-radius: 20px;
            padding: 5px;
        }

        .card-item-footer small {
            font-size: 1em;
            color: #000000;
            /* Color verde para el precio */
            font-weight: bold;
        }




        .card-item-footer .bed-icon.maintenance {
            color: orange;

        }


        .text-warning {
            color: #fdc16a !important;
        }

        .text-green {
            color: #7FFFCC !important;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .d-flex {
            display: flex;
            justify-content: space-between;
            /* Alinea los elementos a los extremos */
            align-items: center;
            /* Alinea los elementos verticalmente al centro */
        }

        .habitaciones-details {
            display: flex;
            flex-direction: column;
            /* Los detalles se muestran de arriba a abajo */
            gap: 5px;
            /* Espaciado entre los detalles */
            font-size: 100%;
            color: #555;
        }

        .habitaciones-details small {
            display: inline-block;
            /* Asegura que los detalles estén en línea, uno debajo del otro */
            margin-left: 10px;
            /* Espacio entre el nombre del piso y los detalles */
        }

        .item.active .custom-card {
            background-color: #e0f7fa;
            /* Celeste claro */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            /* Sombra más suave */
            transition: background-color 0.3s ease;
            /* Efecto suave al cambiar el fondo */
        }

        .card-item-body {
            display: flex;
            flex-direction: column;
            /* Distribuir en columnas */
            gap: 10px;
            /* Espaciado entre elementos */
        }

        .habitacion-info {
            flex: 1;
            min-height: 60px;
            margin-top: 15px;

            background-color: white;
            border-radius: 20px;
            justify-content: center;
            text-align: center;
            /* Esto hace que la información de la habitación ocupe el espacio restante */
        }

        .tr-habitacion-container {
            border-radius: 20px;
            padding: 3px;
        }

        .tr-habitacion {
            font-weight: bold;
            font-size: 1.5em;
            color: #ffffff;
            /* Color del cronómetro, puedes cambiarlo si lo deseas */
        }

        .palpitar {
            animation: palpitar 1s infinite;
        }

        @keyframes palpitar {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .habitacion-botones-cronometro {
            display: flex;
            flex-direction: column;
        }

        .tr-habitacion-container {

            text-align: center;
        }

        .operaciones-container {
            display: flex;
            gap: 2px;
            /* Espaciado horizontal entre botones */
            flex-wrap: wrap;
            /* Permite que los botones pasen a una nueva línea si es necesario */
            justify-content: flex-start;
            /* Alinea los botones al inicio */
        }

        .btn-floating {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .btn-floating:hover {
            transform: scale(1.1);
            /* Efecto al pasar el mouse */
        }

        .estado-circle {
            display: inline-block;
            width: 12px;
            /* Tamaño del círculo */
            height: 12px;
            border-radius: 50%;
            margin-right: 10px;
            /* Espaciado con el texto */
        }
        /* Modal opciones de habitacion ocupada */
        #modalOcupada.modal-ocupada {
            --mh-ink: #1f2937;
            --mh-muted: #6b7280;
            --mh-accent: #0f766e;
            --mh-card-border: #e4e8ee;
            --mh-danger: #c62828;
            border-radius: 18px;
            max-width: 980px;
            width: 94%;
        }

        #modalOcupada.modal-ocupada .modal-content {
            padding: 26px 28px 18px;
            background: linear-gradient(180deg, #f6f8fb 0%, #ffffff 42%);
        }

        #modalOcupada.modal-ocupada .modal-ocupada-title {
            margin: 0 0 12px;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: var(--mh-ink);
            text-align: left;
        }

        #modalOcupada.modal-ocupada .comentario-panel {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 12px 14px;
            border-radius: 12px;
            background: #eef7f5;
            border: 1px solid rgba(15, 118, 110, 0.2);
        }

        #modalOcupada.modal-ocupada .comentario-icon {
            font-size: 1.4rem;
            color: var(--mh-accent);
            margin-top: 2px;
        }

        #modalOcupada.modal-ocupada .comentario-label {
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--mh-muted);
        }

        #modalOcupada.modal-ocupada .comentario-habitacion {
            font-weight: 600;
            color: var(--mh-accent);
            margin-top: 2px;
            line-height: 1.4;
            word-break: break-word;
        }

        #modalOcupada.modal-ocupada .comentario-habitacion:empty::before {
            content: "Sin comentario";
            color: var(--mh-muted);
            font-weight: 500;
        }

        #modalOcupada.modal-ocupada .modal-ocupada-actions {
            margin-top: 18px;
        }

        #modalOcupada.modal-ocupada .action-card {
            border-radius: 14px;
            border: 1px solid var(--mh-card-border);
            background: #ffffff;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            cursor: pointer;
        }

        #modalOcupada.modal-ocupada .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
            border-color: rgba(15, 118, 110, 0.45);
        }

        #modalOcupada.modal-ocupada .action-card .card-content {
            padding: 18px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        #modalOcupada.modal-ocupada .action-card h6 {
            margin: 0;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--mh-ink);
        }

        #modalOcupada.modal-ocupada .action-card i.large {
            font-size: 2.4rem;
            line-height: 1;
        }

        #modalOcupada.modal-ocupada .action-card.action-danger {
            border-color: rgba(198, 40, 40, 0.35);
            background: linear-gradient(180deg, rgba(198, 40, 40, 0.08), #ffffff 55%);
        }

        #modalOcupada.modal-ocupada .action-card.action-danger:hover {
            border-color: var(--mh-danger);
        }

        #modalOcupada.modal-ocupada .action-form {
            width: 100%;
            margin: 0;
        }

        #modalOcupada.modal-ocupada .action-form button.action-card {
            width: 100%;
            padding: 0;
        }

        #modalOcupada.modal-ocupada .modal-footer {
            background: #f3f6fa;
            border-top: 1px solid #e5e7eb;
            padding: 12px 24px;
        }

        #modalOcupada.modal-ocupada .modal-ocupada-close {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            color: #334155;
            font-weight: 600;
        }

        #modalOcupada.modal-ocupada .modal-ocupada-close:hover {
            background: #ffffff;
        }

        /* Modales de estado (disponible / limpieza / mantenimiento) */
        .modal-estado {
            --mh-ink: #1f2937;
            --mh-muted: #6b7280;
            --mh-accent: #0f766e;
            --mh-soft: rgba(15, 118, 110, 0.12);
            --mh-card-border: #e4e8ee;
            border-radius: 18px;
            max-width: 860px;
            width: 94%;
        }

        .modal-estado.modal-disponible {
            --mh-accent: #16a34a;
            --mh-soft: rgba(22, 163, 74, 0.12);
        }

        .modal-estado.modal-limpieza {
            --mh-accent: #d97706;
            --mh-soft: rgba(217, 119, 6, 0.12);
        }

        .modal-estado.modal-mantenimiento {
            --mh-accent: #6b4e16;
            --mh-soft: rgba(107, 78, 22, 0.12);
        }

        .modal-estado .modal-content {
            padding: 24px 26px 16px;
            background: linear-gradient(180deg, #f7f8fb 0%, #ffffff 50%);
        }

        .modal-estado-title {
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: var(--mh-ink);
            text-align: left;
        }

        .modal-estado-subtitle {
            margin: 6px 0 12px;
            font-size: 0.85rem;
            color: var(--mh-muted);
        }

        .modal-estado-actions {
            margin-top: 6px;
        }

        .modal-estado .action-card {
            border-radius: 14px;
            border: 1px solid var(--mh-card-border);
            background: #ffffff;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            cursor: pointer;
        }

        .modal-estado .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
            border-color: var(--mh-accent);
        }

        .modal-estado .action-card .card-content {
            padding: 18px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .modal-estado .action-card h6 {
            margin: 0;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--mh-ink);
        }

        .modal-estado .action-card i.large {
            font-size: 2.4rem;
            line-height: 1;
        }

        .modal-estado .modal-footer {
            background: #f3f6fa;
            border-top: 1px solid #e5e7eb;
            padding: 12px 24px;
        }

        .modal-estado-close {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            color: #334155;
            font-weight: 600;
        }

        .modal-estado-close:hover {
            background: #ffffff;
        }
    </style>
            @php
        $user = Auth::user();
        $persona = App\Models\Persona::find($user->persona_id);
        $sucursal = App\Models\Sucursal::findOrFail($persona->sucursal_id);
        $contraseñaanulacion = App\Models\RelParametrosucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Contraseña para anulación de alojamiento');
            })
            ->value('valor');


        $telefonoadmin = App\Models\RelParametrosucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Celular de administración');
            })
            ->value('valor');
    @endphp
        <input type="hidden" id="contraseñaanulacion" name="contraseñaanulacion" value={{ $contraseñaanulacion }}>
    <input type="hidden" name="telefonoadmin" id="telefonoadmin" value='@json($telefonoadmin)'>

    <div class="main-body">
        <div class="row">
            <div class="col s12">
                <div class="card main-content">
                    <div class="row">
                        <!-- Habitaciones -->
                        <div class="col s12 m10">
                            <div class="row habitacion-column">
                                <!-- Habitaciones -->
                                @php
                                    $tarifaPorHabitacion = collect($movimientosDetalle)
                                        ->pluck('tarifa', 'habitacion_id')
                                        ->map(fn($tarifa) => $tarifa ?: ' ');
                                @endphp
                                @foreach ($Habitaciones as $habitacion)
                                    @php
                                        $reservada = $movreservahoy->flatMap->movreserva_habitacion
                                            ->filter(function ($mrh) {
                                                return ($mrh->situacion ?? '') === 'PENDIENTE';
                                            })
                                            ->pluck('habitacion_id')
                                            ->contains($habitacion->id);
                                        $movhabitacion = $movimientosDetalle->firstWhere(
                                            'habitacion_id',
                                            $habitacion->id,
                                        );

                                        $claseTarjetaSituacion = strtolower($habitacion->situacion);
                                        if (
                                            $reservada
                                            && $habitacion->situacion !== 'Ocupada'
                                            && $habitacion->situacion !== 'Limpieza'
                                            && $habitacion->situacion !== 'Mantenimiento'
                                        ) {
                                            $claseTarjetaSituacion = 'disponible';
                                        }

                                    @endphp

                                    <div class="col s12 m4 l3 habitacion-item">
                                        <div class="card custom-card card-habitacion {{ $claseTarjetaSituacion }} 
                                            {{ $reservada ? 'habitacion-reservada' : '' }}"
                                            data-habitacion-id="{{ $habitacion->id }}"
                                            data-caja-abierta="{{ $cajaAbierta ? '1' : '0' }}"
                                            onclick="{{ !($reservada && $habitacion->situacion !== 'Ocupada') ? "abrirModal('$habitacion->situacion', $habitacion->id)" : '' }}">

                                            <div class="card-body">
                                                <div class="card-item-title">
                                                    <span>{{ $habitacion->tipohabitacion->nombre }}</span>
                                                </div>

                                                <div class="card-item-body">
                                                    <div class="habitacion-info">
                                                        <small>{{ $habitacion->descripcion }}</small>
                                                    </div>

                                                    <div class="habitacion-detalles d-flex flex-column">
                                                        <h4 class="font-weight-bold text-dark text-light numerohabitacion">
                                                            {{ $habitacion->numero }}
                                                        </h4>

                                                        <!-- Mostrar un aviso si está reservada -->
                                                        @if ($reservada)
                                                            <div class="ribbon-wrapper reservada">
                                                                <div class="ribbon reservada">
                                                                    <i class="fas fa-lock"></i> Reservada
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <!-- Mostrar un aviso si está comentada -->
                                                        @if ($movhabitacion && $movhabitacion['comentario'])
                                                            <div class="ribbon-wrapper comentario"
                                                                data-comentario="{{ $movhabitacion['comentario'] }}">
                                                                <div class="ribbon comentario">
                                                                    <i class="fas fa-comment"></i> Comentado
                                                                </div>
                                                            </div>
                                                        @endif




                                                        <div class="habitacion-botones-cronometro">
                                                            @if ($habitacion->situacion == 'Ocupada')
                                                                <div
                                                                    class="tiempo-restante-container tr-habitacion-container">
                                                                    @foreach ($horasPorHabitacion as $horas)
                                                                        @if ($horas['habitacion_id'] == $habitacion->id)
                                                                            <div id="tiempo-restante-{{ $habitacion->id }}"
                                                                                class="tiempo-restante tr-habitacion"
                                                                                data-habitacion-id="{{ $habitacion->id }}"
                                                                                data-hora-fin="{{ \Carbon\Carbon::parse($horas['hora_fin'])->format('Y-m-d H:i:s') }}">
                                                                                <span
                                                                                    id="timer-{{ $habitacion->id }}"></span>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endif

                                                            <div class="operaciones-container">
                                                                @foreach ($operaciones as $operacion)
                                                                    @php
                                                                        // Verificar si es una operación que NO debe ser deshabilitada aunque esté reservada
                                                                        $esOperacionLimpieza = in_array(
                                                                            $operacion->accion,
                                                                            [
                                                                                'habitacion.limpiar',
                                                                                'habitacion.dejarlimpiar',
                                                                            ],
                                                                        );

                                                                        // Solo deshabilitar si está reservada y NO es de limpieza
                                                                        $deshabilitado =
                                                                            $reservada &&
                                                                            !$esOperacionLimpieza &&
                                                                            $habitacion->situacion != 'Ocupada'
                                                                                ? 'disabled opacity-50'
                                                                                : '';
                                                                    @endphp

                                                                    @if (
                                                                        ($operacion->accion == 'movhabitacion.checkout' ||
                                                                            $operacion->accion == 'movhabitacion.productos' ||
                                                                            $operacion->accion == 'movhabitacion.servicios') &&
                                                                            $operacion->tipo === 'Registro' &&
                                                                            $habitacion->situacion == 'Ocupada')
                                                                        <a href="{{ route($operacion->accion, $habitacion->id) }}"
                                                                            class="btn-floating {{ $operacion->color }} darken-1 tooltipped waves-light btn-cargar-crud {{ $deshabilitado }}"
                                                                            data-icono="{{ $operacion->icon_class }}"
                                                                            data-titulo="{{ $operacion->descripcion }}"
                                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                                            {!! $operacion->imagen !!}
                                                                        </a>
                                                                    @elseif(
                                                                        ($operacion->accion === 'movhabitacion.checkin' ||
                                                                            $operacion->accion === 'habitacion.limpiar' ||
                                                                            $operacion->accion === 'habitacion.mantenimiento') &&
                                                                            in_array($habitacion->situacion, ['Disponible', 'Reservada'], true))
                                                                        <a href="{{ route($operacion->accion, $habitacion->id) }}"
                                                                            class="btn-floating {{ $operacion->color }} darken-1 tooltipped waves-light btn-cargar-crud {{ $deshabilitado }}"
                                                                            data-icono="{{ $operacion->icon_class }}"
                                                                            data-titulo="{{ $operacion->descripcion }}"
                                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                                            {!! $operacion->imagen !!}
                                                                        </a>
                                                                    @elseif(
                                                                        ($operacion->accion === 'habitacion.mantenimiento' || $operacion->accion === 'habitacion.dejarlimpiar') &&
                                                                            ($habitacion->situacion == 'Limpieza' or $habitacion->situacion == 'Mantenimiento'))
                                                                        <a href="{{ route($operacion->accion, $habitacion->id) }}"
                                                                            class="btn-floating {{ $operacion->color }} darken-1 tooltipped waves-light btn-cargar-crud {{ $deshabilitado }}"
                                                                            data-icono="{{ $operacion->imagen }}"
                                                                            data-titulo="{{ $operacion->descripcion }}"
                                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                                            {!! $operacion->imagen !!}
                                                                        </a>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-item-footer">
                                                    @if ($habitacion->situacion == 'Ocupada')
                                                        <small>S/ {{ $tarifaPorHabitacion->get($habitacion->id, ' ') }}</small>
                                                    @else
                                                        <small>
                                                            S/
                                                            @if (isset($modalidadtarifario) &&
                                                                    $modalidadtarifario === 'Horas' &&
                                                                    $habitacion->tipohabitacion &&
                                                                    $habitacion->tipohabitacion->json_precio_horas)
                                                                @php
                                                                    $preciosHoras = json_decode(
                                                                        $habitacion->tipohabitacion->json_precio_horas,
                                                                        true,
                                                                    );
                                                                    $primerPrecio =
                                                                        $preciosHoras[0]['precio'] ??
                                                                        $habitacion->precio;
                                                                @endphp
                                                                {{ number_format($primerPrecio, 2) }}
                                                            @else
                                                                {{ number_format($habitacion->precio, 2) }}
                                                            @endif
                                                        </small>
                                                    @endif
                                                    @php
                                                        // Buscar si la habitación tiene un movimiento de ocupación
                                                        $movimiento = $movimientosDetalle->firstWhere(
                                                            'habitacion_id',
                                                            $habitacion->id,
                                                        );
                                                    @endphp

                                                    @if ($movimiento && $movimiento['situacion'] == 'P')
                                                        <i class="fas fa-check-circle text-success tooltipped"
                                                            data-tooltip="Pagado"></i>
                                                    @elseif ($movimiento && $movimiento['situacion'] == 'A')
                                                        <i class="fas fa-exclamation-circle text-warning tooltipped"
                                                            data-tooltip="Pendiente de pago"></i>
                                                    @endif

                                                    </small>
                                                    @if ($habitacion->situacion == 'Ocupada' && isset($clientesPorHabitacion[$habitacion->id]))
                                                        <small class="cliente-principal"
                                                            title="{{ $clientesPorHabitacion[$habitacion->id] }}">
                                                            <i class="fas fa-user"></i>
                                                            <span class="nombre-abreviado">
                                                                {{ Str::limit($clientesPorHabitacion[$habitacion->id], 7, '...') }}
                                                            </span>
                                                        </small>
                                                    @endif

                                                    <div class="card-item-icon bed-icon"
                                                        style="margin-top: -3px; margin-right: 5px">
                                                        @if ($habitacion->situacion == 'Limpieza')
                                                            <i class="fas fa-broom maintenance" style="color: rgb(0, 0, 0);"
                                                                title="En Limpieza"></i>
                                                        @elseif ($habitacion->situacion == 'Mantenimiento')
                                                            <i class="fas fa-tools maintenance"
                                                                title="En Mantenimiento"></i>
                                                        @elseif($habitacion->situacion == 'Ocupada')
                                                            <i class="fa fa-bed" style="color: rgb(0, 0, 0);"
                                                                title="Ocupada"></i>
                                                        @else
                                                            <i class="fa fa-bed text-success" title="Disponible"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <style>
                            /* Contenedor de la cinta "Comentado" */
                            .ribbon-wrapper.comentario {
                                height: 80px;
                                overflow: hidden;
                                position: absolute;
                                left: -1px;
                                /* Posición izquierda */
                                top: -1px;
                                width: 80px;
                                z-index: 10;
                            }

                            /* Estilo de la cinta "Comentado" */
                            .ribbon.comentario {
                                background-color: blue;
                                /* Azul para Comentado */
                                color: white;
                                font-weight: bold;
                                box-shadow: 0 0 3px rgba(0, 0, 0, .3);
                                font-size: 0.6rem;
                                line-height: 100%;
                                padding: 5px 0;
                                position: relative;
                                left: -25px;
                                /* Posición izquierda */
                                text-align: center;
                                text-shadow: 0 -1px 0 rgba(0, 0, 0, .4);
                                text-transform: uppercase;
                                top: 20px;
                                transform: rotate(-45deg);
                                width: 110px;
                                /* Más ancho para mostrar todo el texto */
                            }

                            /* Contenedor de la cinta "Reservada" */
                            .ribbon-wrapper.reservada {
                                height: 80px;
                                overflow: hidden;
                                position: absolute;
                                right: -1px;
                                /* Posición derecha */
                                top: -1px;
                                width: 80px;
                                z-index: 10;
                            }

                            /* Estilo de la cinta "Reservada" */
                            .ribbon.reservada {
                                background-color: red;
                                /* Rojo para Reservada */
                                color: white;
                                font-weight: bold;
                                box-shadow: 0 0 3px rgba(0, 0, 0, .3);
                                font-size: 0.6rem;
                                line-height: 100%;
                                padding: 5px 0;
                                position: relative;
                                right: -1px;
                                /* Posición derecha */
                                text-align: center;
                                text-shadow: 0 -1px 0 rgba(0, 0, 0, .4);
                                text-transform: uppercase;
                                top: 20px;
                                transform: rotate(45deg);
                                width: 110px;
                                /* Más ancho para mostrar todo el texto */
                            }

                            /* Colores tipo bola con degradado */
                            .ocupada {
                                background: radial-gradient(circle at 30% 28%, #ff8383, #ff8383 70%);
                            }

                            .disponible {
                                background: radial-gradient(circle at 30% 28%, #69f0ae, #69f0ae 70%);
                            }

                            .limpieza {
                                background: radial-gradient(circle at 30% 28%, #FFA726, #FFA726 70%);
                            }

                            .mantenimiento {
                                background: radial-gradient(circle at 30% 28%, #805008, #805008 70%);
                            }
                        </style>



                        <!-- Sección de Pisos (Derecha) -->
                        <div class="col s12 m2">
                            <div class="card hoverable z-depth-3 sticky-card" style="border-radius: 15px;">
                                <div class="card-content">
                                    <h5 class="center-align teal-text">Pisos</h5>
                                    <div class="row"
                                        style="display: flex; flex-wrap: wrap; gap: 8px; max-width: 170px; margin: 0 auto;">
                                        @foreach ($Pisos as $piso)
                                            <a href="{{ route('movhabitacion.filtrarPiso', ['pisoId' => $piso->id]) }}"
                                                class="btn btn-floating {{ ($pisoId ?? 'todos') == $piso->id ? 'bg-primary' : 'bg-secondary' }} white-text tooltipped piso-boton btn-cargar-crud"
                                                data-tooltip="{{ $piso->nombre }}"
                                                style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                                {{ 'P' . preg_replace('/[^0-9]/', '', $piso->nombre) }}
                                            </a>
                                        @endforeach
                                    </div>

                                </div>
                            </div>


                            <!-- Situaciones -->
                            <div class="card hoverable z-depth-3 sticky-card" style="border-radius: 15px;">
                                <div class="card-content">
                                    <h5 class="center-align teal-text">Situaciones</h5>
                                    <ul class="collection">
                                        <a href="{{ route('movhabitacion.index') }}"
                                            class="collection-item btn-cargar-crud {{ ($estado ?? 'todos') == 'todos' ? 'active teal' : '' }}">
                                            Todos
                                        </a>
                                        <a href="{{ route('movhabitacion.filtrarEstado', ['estado' => 'disponible']) }}"
                                            class="collection-item btn-cargar-crud {{ ($estado ?? 'todos') == 'disponible' ? 'active teal' : '' }}">
                                            <div class="estado-circle" style="background-color: #7FFFCC;"></div> Disponible
                                        </a>
                                        <a href="{{ route('movhabitacion.filtrarEstado', ['estado' => 'ocupada']) }}"
                                            class="collection-item btn-cargar-crud {{ ($estado ?? 'todos') == 'ocupada' ? 'active teal' : '' }}">
                                            <div class="estado-circle" style="background-color: #ff7f7f;"></div> Ocupada
                                        </a>
                                        <a href="{{ route('movhabitacion.filtrarEstado', ['estado' => 'limpieza']) }}"
                                            class="collection-item btn-cargar-crud {{ ($estado ?? 'todos') == 'limpieza' ? 'active teal' : '' }}">
                                            <div class="estado-circle" style="background-color: #FFA726;"></div> Limpieza
                                        </a>
                                        <a href="{{ route('movhabitacion.filtrarEstado', ['estado' => 'mantenimiento']) }}"
                                            class="collection-item btn-cargar-crud {{ ($estado ?? 'todos') == 'mantenimiento' ? 'active teal' : '' }}">
                                            <div class="estado-circle" style="background-color: #805008;"></div> Mant.
                                        </a>
                                    </ul>
                                </div>
                            </div>

                            <!-- Categorías -->
                            <div class="card hoverable z-depth-3 sticky-card" style="border-radius: 15px;">
                                <div class="card-content">
                                    <h5 class="center-align teal-text">Categorías</h5>
                                    <ul class="collection">
                                        <a href="{{ route('movhabitacion.index') }}"
                                            class="collection-item btn-cargar-crud {{ ($categoriaId ?? 'todas') == 'todas' ? 'active teal' : '' }}">
                                            Todas
                                        </a>
                                        @foreach ($Categorias as $categoria)
                                            <a href="{{ route('movhabitacion.filtrar', ['categoriaId' => $categoria->id]) }}"
                                                class="collection-item btn-cargar-crud {{ ($categoriaId ?? 'todas') == $categoria->id ? 'active teal' : '' }}">
                                                {{ $categoria->nombre }}
                                            </a>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>


        <!-- Modal para vista general -->
        <div id="modalVistaGeneral" class="modal">
            <div class="modal-content">
                <h5 class="center-align">Vista General de Habitaciones</h5>
                <table class="striped centered">
                    <thead class="bg-primary">
                        <tr>
                            <th>Habitación</th>
                            <th>Tipo</th>
                            <th>Piso</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaHabitaciones">
                        <!-- Aquí se cargarán las habitaciones dinámicamente -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <a href="#" class="modal-close waves-effect waves-green btn-flat">Cerrar<i
                        class="material-icons right">clear</i></a>
            </div>
        </div>

        <!-- Modal para habitación en Limpieza -->
        <div id="modalLimpieza" class="modal z-depth-4 modal-estado modal-limpieza">
            <div class="modal-content">
                <h5 class="modal-estado-title">Opciones de la Habitación - En Limpieza</h5>
                <p class="modal-estado-subtitle">Selecciona una acción para la habitación.</p>
                <div class="row modal-estado-actions">
                    <div class="col s12 m4">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Dejar de Limpiar"
                            data-url-base="{{ route('habitacion.dejarlimpiar', '') }}">
                            <div class="card-content">
                                <i class="fas fa-broom large text-green"></i>
                                <h6>Habilitar Habitación</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m4">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Mantenimiento"
                            data-url-base="{{ route('habitacion.mantenimiento', '') }}">
                            <div class="card-content">
                                <i class="fas fa-wrench large maintenance"></i>
                                <h6>Mantenimiento</h6>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col s12 m4">
                        <div class="card hoverable center-align" style="border-radius: 10px;">
                            <div class="card-content">
                                <i class="material-icons large blue-text">date_range</i>
                                <h6>Reservas</h6>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat modal-estado-close">Cerrar</a>
            </div>
        </div>

        <!-- Modal para habitación en Mantenimiento -->
        <div id="modalMantenimiento" class="modal z-depth-4 modal-estado modal-mantenimiento">
            <div class="modal-content">
                <h5 class="modal-estado-title">Opciones de la Habitación - En Mantenimiento</h5>
                <p class="modal-estado-subtitle">Selecciona una acción para la habitación.</p>
                <div class="row modal-estado-actions">
                    <div class="col s12 m4">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Dejar de Limpiar"
                            data-url-base="{{ route('habitacion.dejarlimpiar', '') }}">
                            <div class="card-content">
                                <i class="fas fa-broom large text-green"></i>
                                <h6>Habilitar Habitación</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m4">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Limpiar"
                            data-url-base="{{ route('habitacion.limpiar', '') }}">
                            <div class="card-content">
                                <i class="fas fa-broom large text-warning"></i>
                                <h6>Limpieza</h6>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col s12 m4">
                        <div class="card hoverable center-align" style="border-radius: 10px;">
                            <div class="card-content">
                                <i class="material-icons large blue-text">date_range</i>
                                <h6>Reservas</h6>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat modal-estado-close">Cerrar</a>
            </div>
        </div>

        <!-- Modal para habitación Disponible -->
        <div id="modalDisponible" class="modal z-depth-4 modal-estado modal-disponible">
            <div class="modal-content">
                <h5 class="modal-estado-title">Opciones de la Habitación - Disponible</h5>
                <p class="modal-estado-subtitle">Selecciona una acción para la habitación.</p>
                <div class="row modal-estado-actions">
                    <div class="col s12 m3">
                        <div href="#"class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-check-circle" data-titulo="Check-In de Habitación"
                            data-url-base="{{ route('movhabitacion.checkin', '') }}">
                            <div class="card-content">
                                <i class="fas fa-check-circle large green-text"></i>
                                <h6>Check-In</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m3">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Limpiar"
                            data-url-base="{{ route('habitacion.limpiar', '') }}">
                            <div class="card-content">
                                <i class="fas fa-broom large text-warning"></i>
                                <h6>Limpieza</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m3">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Mantenimiento"
                            data-url-base="{{ route('habitacion.mantenimiento', '') }}">
                            <div class="card-content">
                                <i class="fas fa-wrench large maintenance"></i>
                                <h6>Mantenimiento</h6>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col s12 m3">
                        <div class="card hoverable center-align" style="border-radius: 10px;">
                            <div class="card-content">
                                <i class="material-icons large blue-text">date_range</i>
                                <h6>Reservas</h6>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat modal-estado-close">Cerrar</a>
            </div>
        </div>
             <div id="modalOcupada" class="modal z-depth-4 modal-ocupada">
            <div class="modal-content">
                <h5 class="modal-header-titulo modal-ocupada-title">
                    <!-- Este es el lugar donde se mostrará el número y tipo de habitación -->
                    Opciones de la Habitación - Ocupada
                </h5> <!-- Espacio para mostrar el comentario -->
                <div class="row">
                    <div class="col s12">
                        <div class="comentario-panel">
                            <i class="material-icons comentario-icon">chat_bubble_outline</i>
                            <div class="comentario-body">
                                <div class="comentario-label">Comentario</div>
                                <div id="comentario-habitacion" class="comentario-habitacion"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row modal-ocupada-actions">
                    <div class="col s12 m4">
                        <a href="#" class="card hoverable center-align btn-cargar-crud action-card action-primary"
                            data-icono="fas fa-check-circle" data-titulo="Check-Out de Habitación"
                            data-url-base="{{ route('movhabitacion.checkout', '') }}">
                            <div class="card-content">
                                <i class="fas fa-check-circle large red-text"></i>
                                <h6>Check-Out</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col s12 m4">
                        <a href="" class="card hoverable center-align btn-cargar-crud action-card" data-icono="fas fa-gifts"
                            data-titulo="Productos para habitación"
                            data-url-base="{{ route('movhabitacion.productos', '') }}">
                            <div class="card-content">
                                <i class="fas fa-gifts large blue-text"></i>
                                <h6>Productos</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col s12 m4">
                        <a href="" class="card hoverable center-align btn-cargar-crud action-card" data-icono="fas fa-gifts"
                            data-titulo="Servicios para habitación"
                            data-url-base="{{ route('movhabitacion.servicios', '') }}">
                            <div class="card-content">
                                <i class="material-icons large teal-text">room_service</i>
                                <h6>Servicios</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col s12 m4">
                        <a href="" class="card hoverable center-align btn-cargar-crud action-card" data-icono="fas fa-eye"
                            data-titulo="Detalles de Alojamiento" data-url-base="{{ route('movhabitacion.ver', '') }}">
                            <div class="card-content">
                                <i class="material-icons large purple-text">visibility</i>
                                <h6>Ver Detalles</h6>
                            </div>

                        </a>
                    </div>
                    <div class="col s12 m4">
                        <form class="anular-form action-form" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" class="btn-actualizar-url"
                                data-url-base="{{ route('movhabitacion.anular', '') }}">
                            <button type="submit" class="card hoverable center-align action-card action-danger">
                                <div class="card-content">
                                    <i class="material-icons large red-text text-darken-2">delete_forever</i>
                                    <h6 style="font-weight: 600; color: #e53935;">Anular</h6>
                                </div>
                            </button>
                        </form>
                    </div>

                    <!-- Nuevo botón agregado -->
                    <div class="col s12 m4">
                        <a href="" class="card hoverable center-align btn-cargar-crud action-card" data-icono="fas fa-edit"
                            data-titulo="Editar Alojamiento" data-url-base="{{ route('movhabitacion.editCheckin', '') }}">
                            <div class="card-content">
                                <i class="fas fa-edit large amber-text text-darken-1"></i>
                                <h6>Editar</h6>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat modal-ocupada-close">Cerrar</a>
            </div>
        </div>
    </div>
    <div id="anular-venta-modal" class="modal modal-large" style="z-index: 99999999; display: none;">
        <div class="modal-content">
            <div class="row without-top">
                <p class="grey-text text-darken-3 without-tb center" id="modal-title"> <i
                        class="fa-solid fa-ban left"></i>Anular venta
                </p>
            </div>

            <div class="row without-tb" id="modal-form-anular">

                <div class="row">
                    <input type="text" autocomplete="username" tabindex="-1"
                        style="position:absolute; left:-9999px; width:1px; height:1px; opacity:0;">
                    <input type="password" autocomplete="current-password" tabindex="-1"
                        style="position:absolute; left:-9999px; width:1px; height:1px; opacity:0;">
                    <div class="input-field col s12 m6">
                        <input class="submit-form" id="motivo_anulacion" name="motivo_anulacion" type="text"
                            autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"
                            data-lpignore="true" required></input>
                        <label for="motivo_anulacion">Motivo de la anulación <span style="color:red;">*</span></label>
                        <span class="helper-text" id="motivo_anulacion_spn">Ingrese el motivo de anulación</span>
                    </div>

                    <div class="input-group-field" style="display: flex; align-items: center; gap: 0.5rem; ">
                        <div class="right-wrapper input-field" style="flex-grow: 1; margin: 0; margin-top: 16px;">
                            <input class="submit-form" id="codigo_aprobacion_anulacion" name="codigo_aprobacion_anulacion"
                                type="password" autocomplete="new-password" autocapitalize="off" autocorrect="off"
                                spellcheck="false" data-lpignore="true" required />
                            <label id="codigo_aprobacion_anulacion_lbl" for="codigo_aprobacion_anulacion" style="left: 0;">
                                Código de aprobación <span style="color:red;">*</span>
                            </label>
                            <span class="helper-text" id="codigo_aprobacion_movimiento_spn" data-error="" data-success="">
                                Ingrese el código o envíe uno temporal
                            </span>
                        </div>

                        <button class="btn tooltipped green" id="btn-codigo-aprobacion-movimiento-whatsapp"
                            data-tooltip="Solicitar código por WhatsApp" type="button"
                            onclick="sendWhatsAppCodeForAnulacionAlojamiento();" style="height: 36px;">
                            <i class="fa-brands fa-whatsapp"></i>
                        </button>

                        <button class="btn tooltipped" id="btn-codigo-aprobacion-movimiento-email"
                            data-tooltip="Solicitar código por e-mail" type="button" onclick="sendEmailCodeForAnulacionAlojamiento();"
                            style="height: 36px;">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="modal-footer">
            <button id="btn-cancel-anular" type="button"
                class="btn-flat waves-effect waves-dark btn-cancelar btn-mantenimiento grey-text text-darken-3"
                onclick="cancelAnularVentaModal();">
                CANCELAR
            </button>
            <button id="btn-save-anular" type="button"
                class="btn-flat waves-effect waves-light teal-text semibold btn-aceptar btn-mantenimiento"
                onclick="aceptarAnularVentaModal()">
                ANULAR
            </button>
        </div>
    </div>

    <div id="modal-overlay-anular"
        style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1002;">
    </div>
@endsection
