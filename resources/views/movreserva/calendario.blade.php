@extends('layouts.app')

@section('content')

    <input type="hidden" id="contraseñaanulacion" name="contraseñaanulacion"
        value="{{ $contrasenaAnulacion }}">

    <!-- Modal de Anulación de Venta -->
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
                                    data-lpignore="true" required ></input>
                                <label for="motivo_anulacion">Motivo de la anulación <span
                                        style="color:red;">*</span></label>
                                <span class="helper-text" id="motivo_anulacion_spn">Ingrese el modtivo de anulación</span>
                            </div>

                            <div class="input-group-field" style="display: flex; align-items: center; gap: 0.5rem; ">
                                <div class="right-wrapper input-field" style="flex-grow: 1; margin: 0; margin-top: 16px;">
                                    <input class="submit-form" id="codigo_aprobacion_anulacion"
                                        name="codigo_aprobacion_anulacion" type="password" autocomplete="new-password"
                                        autocapitalize="off" autocorrect="off" spellcheck="false" data-lpignore="true"
                                        required />
                                    <label id="codigo_aprobacion_anulacion_lbl" for="codigo_aprobacion_anulacion"
                                        style="left: 0;">
                                        Código de aprobación <span style="color:red;">*</span>
                                    </label>
                                    <span class="helper-text" id="codigo_aprobacion_anulacion_spn" data-error=""data-success="">
                                        Ingrese el código de aprobación
                                    </span>
                                </div>

                                <button class="btn tooltipped green" id="btn-codigo-aprobacion-anulacion-whatsapp"
                                    data-tooltip="Solicitar código por WhatsApp" type="button"
                                    onclick="sendWhatsAppCodeForAnulacion();" style="height: 36px;">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </button>

                                <button class="btn tooltipped" id="btn-codigo-aprobacion-anulacion-email"
                                    data-tooltip="Solicitar código por e-mail" type="button"
                                    onclick="sendEmailCodeForAnulacion();" style="height: 36px;">
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

    <!-- Fondo oscuro para el modal de anulación -->
    <div id="modal-overlay-anular"
        style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1002;">
    </div>
    <div class="main-body">
        <div class="row">
            <div class="card main-content">

                <div class="card-body bg-white">
                    <div class="calendar-header-container" id="calendar"
                        style="display: flex; align-items: center; justify-content: space-between; position: relative;">
                        <div style="position: absolute; left: 0; display: flex; align-items: center; gap: 10px;">
                            <div class="legend-item">
                                <span class="legend-color"
                                    style="background-color: {{ $coloresEstado['PENDIENTE'] ?? '#ccc' }}"></span>
                                <span class="legend-text">Pendiente</span>
                            </div>
                            @if ($finalizadas === 'Si')
                                <div class="legend-item">
                                    <span class="legend-color"
                                        style="background-color: {{ $coloresEstado['FINALIZADO'] ?? '#ccc' }}"></span>
                                    <span class="legend-text">Finalizado</span>
                                </div>
                            @else
                                <div class="legend-item">
                                    <span class="legend-color"
                                        style="background-color: {{ $coloresEstado['LIMPIEZA'] ?? '#ccc' }}"></span>
                                    <span class="legend-text">Limpieza</span>
                                </div>
                            @endif

                            <div class="legend-item">
                                <span class="legend-color"
                                    style="background-color: {{ $coloresEstado['EN ATENCIÓN'] ?? '#ccc' }}"></span>
                                <span class="legend-text">En Atención</span>
                            </div>
                        </div>

                        <!-- Contenedor centrado de navegación y fecha -->
                       <div style="display: flex; align-items: center; gap: 10px; margin: auto;">
    <button id="prevMonth" class="btn nav-btn">«</button>
    <button id="prevWeek" class="btn nav-btn">‹</button>

<h4 id="currentMonthYear" class="center-align"
    style="margin: 0; text-transform: capitalize; width: 320px;">
</h4>

    <button id="nextWeek" class="btn nav-btn">›</button>
    <button id="nextMonth" class="btn nav-btn">»</button>
</div>


                        <!-- Contenedor alineado a la derecha para los botones de operaciones -->
                        <div style="position: absolute; right: 0; display: flex; align-items: center; gap: 10px;">
                            <input id="expand-compress-rows-Calendario" type="hidden" value="N">
                            <input id="mostrar-filtro-Calendario" type="hidden" value="N">
                            <button class="btn-flat btn-grid-options tooltipped" id="btn-expand-compress-Calendario"
                                type="button" data-position="top" data-tooltip="Expandir filas">
                                <i class="fa-solid fa-expand" style="color:#343a40;"></i>
                            </button>
                            <button class="btn-flat btn-grid-options tooltipped" id="btn-filtro-Calendario"
                                type="button" data-position="top" data-tooltip="Filtros">
                                <i class="fa-solid fa-magnifying-glass-plus" style="color:#343a40;"></i>
                            </button>
                            @foreach ($operaciones as $operacion)
                                @if ($operacion->tipo == 'Tabla')
                                    <a href="{{ route($operacion->accion) }}"
                                        class="btn waves-effect waves-light btn-cargar-crud {{ $operacion->color }}"
                                        data-icono="fa-solid fa-plus left" data-titulo="{{ $operacion->comentario }}"
                                        style="border-radius: 10px;">
                                        {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div id="filtrosCalendario" class="row calendar-filters" style="display: none;">
                        <div class="input-field col s12 m6 l5" style="margin-top: 0;">
                            <i class="material-icons prefix">search</i>
                            <input id="buscarReservaCalendario" type="text" class="validate" autocomplete="off"
                                aria-label="Buscar reserva por nombre, apellidos o número de documento">
                           <label for="lblBusquedaCalendario">Buscar por nombres, DNI o RUC</label>
                            <span id="resultadoBusquedaCalendario" class="helper-text" aria-live="polite"></span>
                        </div>
                        <div class="input-field col s12 m3 l3 calendar-filter-select" style="margin-top: 0;">
                            <select id="estadoReservaCalendario" class="validate">
                                <option value="">Todos los estados</option>
                                <option value="PENDIENTE">Pendiente</option>
                                <option value="EN ATENCION">En atención</option>
                            </select>
                           
                            <span class="helper-text" id="estadoReservaCalendario_spn" data-error="" data-success=""></span>
                        </div>
                        <div class="input-field col s10 m2 l3 calendar-filter-select" style="margin-top: 0;">
                            <select id="habitacionReservaCalendario" class="validate">
                                <option value="">Todas las habitaciones</option>
                                @foreach ($habitaciones as $habitacion)
                                    <option value="{{ $habitacion->id }}">{{ $habitacion->numero }} - {{ $habitacion->tipohabitacion->nombre }}</option>
                                @endforeach
                            </select>
                       
                            <span class="helper-text" id="habitacionReservaCalendario_spn" data-error="" data-success=""></span>
                        </div>
                        <div class="col s2 m1 l1 calendar-filter-clear">
                            <button id="limpiarFiltrosCalendario" type="button" class="btn-flat tooltipped"
                                data-position="top" data-tooltip="Limpiar filtros" aria-label="Limpiar filtros">
                                <i class="fa-solid fa-eraser"></i>
                            </button>
                        </div>
                    </div>

                    <div class="timeline-container" id="timelineCalendario">
                        <div class="rooms-column">
                            <script type="application/json" id="habitacionesData">@json($habitacionesData)</script>
                            <script type="application/json" id="reservasData"
                                data-history-url="{{ route('calendarioreservas.index', ['scope' => 'history']) }}">@json($reservas)</script>
                            <script type="application/json" id="origenesReservasData">@json($origenes)</script>

                            @php
                                // Lista de colores predefinidos (puedes expandirla)
                                $coloresDisponibles = [
                                    '#FF5733',
                                    '#33FF57',
                                    '#3357FF',
                                    '#FF33A8',
                                    '#A833FF',
                                    '#FFD700',
                                    '#00FFFF',
                                ];

                                // Array para asignar colores a cada tipo de habitación
                                $coloresTipos = [];

                                // Asignar colores únicos a cada tipo de habitación
                                foreach ($habitaciones as $habitacion) {
                                    $tipo = $habitacion->tipohabitacion->nombre;
                                    if (!isset($coloresTipos[$tipo])) {
                                        // Asignar color de la lista disponible o generar aleatorio si se acaban
                                        $coloresTipos[$tipo] =
                                            array_shift($coloresDisponibles) ?? sprintf('#%06X', mt_rand(0, 0xffffff));
                                    }
                                }
                            @endphp

                       <h5 style="text-align: center;">Habitaciones</h5>



@foreach ($habitaciones as $habitacion)
    @php
        $estado = $habitacion->situacion;

        $estadoUI = [
            'Disponible' => ['icon' => 'fa-door-open', 'color' => '#28a745', 'bg' => '#eafaf1'],
            'Ocupada' => ['icon' => 'fa-user-lock', 'color' => '#dc3545', 'bg' => '#fdecea'],
            'Limpieza' => ['icon' => 'fa-broom', 'color' => '#ffc107', 'bg' => '#fff8e1'],
            'Mantenimiento' => ['icon' => 'fa-tools', 'color' => '#805008 ', 'bg' => '#f1f3f5'],
        ];

        $ui = $estadoUI[$estado] ?? $estadoUI['Disponible'];
    @endphp

    <div class="room-card" style="background: {{ $ui['bg'] }}" data-id="{{ $habitacion->id }}">
        <div class="room-left">
            <div class="room-number">{{ $habitacion->numero }}</div>
            <i class="fas {{ $ui['icon'] }} room-icon" style="color: {{ $ui['color'] }}"></i>
        </div>

        <div class="room-right">
            <span class="room-type">{{ $habitacion->tipohabitacion->nombre }}</span>
           
        </div>
    </div>
@endforeach




                        </div>
                        <div class="calendar-wrapper">
                            <div id="calendarStickySentinel" class="calendar-sticky-sentinel" aria-hidden="true"></div>
                            <div class="calendar-controls">
                                {{-- <button id="prevWeek" class="btn-floating waves-effect waves-light darken-2 lighten-2"><i
                            class="material-icons">chevron_left</i></button> --}}
                                <div class="calendar-header" id="calendarHeader"></div>
                                {{-- <button id="nextWeek" class="btn-floating waves-effect waves-light darken-2 lighten-2"><i
                            class="material-icons">chevron_right</i></button> --}}
                            </div>

                            <div class="calendar-body" id="calendarBody"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal para reservas PENDIENTES -->
        <div id="modalPendiente" class="modal z-depth-4 modal-opciones-reserva">
            <div class="modal-content">
                <h5 class="center-align"><i class="fas fa-clock"></i> Opciones de la Reserva - Pendiente</h5>
                <div class="row opciones-reserva-grid">
                    @foreach ($operaciones as $operacion)
                        @if ($operacion->tipo == 'Registro')
                            @if ($operacion->accion == 'movreserva.anular')
                                <!-- Botón "Anular" con Formulario -->
                                <form action="{{ route($operacion->accion, ':id') }}" method="POST" class="anular-form" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="card hoverable waves-effect waves-light {{ $operacion->color }} btn-actualizar-url"
                                        data-url-base="{{ route($operacion->accion, ':id') }}"
                                        style="flex: 1; min-width: 120px; max-width: 150px; text-align: center; padding: 15px 10px; margin-right: 10px; border: none;">
                                        <div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                            <span class="white-text" style="font-size: 32px;">{!! $operacion->imagen !!}</span>
                                            <h6 class="white-text" style="font-size: 14px; margin-top: 5px;">
                                                {{ $operacion->descripcion }}
                                            </h6>
                                        </div>
                                    </button>
                                </form>
                            @elseif ($operacion->accion === 'movreserva.detalles')
                                <a href="#"
                                    class="card hoverable btn-cargar-crud waves-effect waves-light {{ $operacion->color }}"
                                    data-url-base="{{ route($operacion->accion, ':id') }}"
                                    data-icono="{{ $operacion->imagen }}"
                                    data-titulo="{{ $operacion->comentario }}"
                                    style="flex: 1; min-width: 120px; max-width: 150px; text-align: center; padding: 10px; margin-right: 10px;">
                                    <div>
                                        <span class="white-text" style="font-size: 32px;">{!! $operacion->imagen !!}</span>
                                        <h6 class="white-text" style="font-size: 12px; margin-top: 5px;">
                                            {{ $operacion->descripcion }}
                                        </h6>
                                    </div>
                                </a>
                            
                            @elseif (in_array($operacion->accion, ['movreserva.atender', 'movreserva.edit']))
                                <a href="#"
                                    class="card hoverable btn-cargar-crud waves-effect waves-light {{ $operacion->color }}"
                                    data-url-base="{{ route($operacion->accion, ':id') }}"
                                    data-icono="{{ $operacion->imagen }}" 
                                    data-requiere-habitacion="1"
                                    data-titulo="{{ $operacion->comentario }}"
                                    style="flex: 1; min-width: 120px; max-width: 150px; text-align: center; padding: 10px; margin-right: 10px;">
                                    <div>
                                        <span class="white-text" style="font-size: 32px;">{!! $operacion->imagen !!}</span>
                                        <h6 class="white-text" style="font-size: 12px; margin-top: 5px;">
                                            {{ $operacion->descripcion }}</h6>
                                    </div>
                                </a>
                            @elseif ($operacion->accion === 'movreserva.generarPDF')
                                <!-- Botón "Generar PDF" -->
                                <a href="#"
                                    class="card hoverable btn-pdf waves-effect waves-light {{ $operacion->color }}"
                                    data-url-base="{{ route($operacion->accion, ':id') }}"
                                    data-icono="{{ $operacion->imagen }}"
                                    data-titulo="{{ $operacion->comentario }}"
                                    style="flex: 1; min-width: 120px; max-width: 150px; text-align: center; padding: 10px; margin-right: 10px; border: none;"
                                    target="_blank">
                                    <div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                        <span class="white-text" style="font-size: 32px;">{!! $operacion->imagen !!}</span>
                                        <h6 class="white-text" style="font-size: 14px; margin-top: 5px;">
                                            Reserva PDF
                                        </h6>
                                    </div>
                                </a>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
            </div>
        </div>



        <!-- Modal para reservas EN ATENCIÓN -->
        <div id="modalAtencion" class="modal z-depth-4 modal-opciones-reserva">
            <div class="modal-content">
                <h5 class="center-align"><i class="fas fa-concierge-bell"></i> Opciones de la Reserva - En Atención</h5>
                <div class="row opciones-reserva-grid">
                    @foreach ($operaciones as $operacion)
                        @if ($operacion->tipo == 'Registro' && in_array($operacion->accion, ['movhabitacion.checkout', 'movreserva.edit']))
                        <a href="#"
                                    class="card hoverable btn-cargar-crud waves-effect waves-light {{ $operacion->color }}"
                                    data-url-base="{{ route($operacion->accion, ':id') }}"
                                    data-icono="{{ $operacion->imagen }}" 
                                    data-requiere-habitacion="1"
                                    data-titulo="{{ $operacion->comentario }}"
                                    style="flex: 1; min-width: 120px; max-width: 150px; text-align: center; padding: 10px; margin-right: 10px;">
                                    <div>
                                        <span class="white-text" style="font-size: 32px;">{!! $operacion->imagen !!}</span>
                                        <h6 class="white-text" style="font-size: 12px; margin-top: 5px;">
                                            {{ $operacion->descripcion }}</h6>
                                    </div>
                                </a>
                        @endif
                    @endforeach

                    <a id="btn-finalizar-reserva-rapido" href="#"
                        class="card hoverable btn-activar waves-effect waves-light red"
                        data-url-base="{{ route('finalizarmovreserva', ':id') }}"
                        title="Finalizar reserva"
                        style="display: none; flex: 1; min-width: 120px; max-width: 150px; text-align: center; padding: 10px; margin-right: 10px;">
                        <div>
                            <span class="white-text" style="font-size: 32px;">
                                <i class="fa-solid fa-flag-checkered"></i>
                            </span>
                            <h6 class="white-text" style="font-size: 12px; margin-top: 5px;">
                                FINALIZAR
                            </h6>
                        </div>
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
            </div>
        </div>
        <!-- Modal de Mantenimiento -->
        <div id="modalMantenimiento" class="modal">
            <div class="modal-content">
                <h4>Habitación en Mantenimiento</h4>
                <p>La <span class="habitacion-mantenimiento"></span> está en mantenimiento y no se pueden hacer reservas en
                    este momento.</p>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat">Entendido</a>
            </div>
        </div>


        <!-- Modal para reservas FINALIZADAS -->
        <div id="modalFinalizado" class="modal z-depth-4 modal-opciones-reserva">
            <div class="modal-content">
                <h5 class="center-align"><i class="fas fa-check-circle"></i> Opciones de la Reserva - Finalizada</h5>
                <div class="row opciones-reserva-grid">
                    @foreach ($operaciones as $operacion)
                        @if ($operacion->tipo == 'Registro' && in_array($operacion->accion, ['movreserva.detalles']))
                            <a href="#"
                                class="card hoverable btn-cargar-crud waves-effect waves-light {{ $operacion->color }}"
                                data-url-base="{{ route($operacion->accion, ':id') }}"
                                data-icono="{{ $operacion->icono }}" data-titulo="{{ $operacion->descripcion }}"
                                style="flex: 1; min-width: 120px; max-width: 150px; text-align: center; padding: 10px; margin-right: 10px;">
                                <div>
                                    <span class="white-text" style="font-size: 32px;">{!! $operacion->imagen !!}</span>
                                    <h6 class="white-text" style="font-size: 12px; margin-top: 5px;">
                                        {{ $operacion->descripcion }}</h6>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
            </div>
        </div>



        <style>
            .modal-opciones-reserva {
                width: min(90vw, 820px);
                max-width: 820px;
                max-height: 90vh;
                border-radius: 36px;
            }

            .modal-opciones-reserva .modal-content {
                padding: 28px 32px 12px;
            }

            .opciones-reserva-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(125px, 1fr));
                gap: 16px;
                margin: 18px 0 0;
                padding: 10px;
                overflow: visible;
            }

            .opciones-reserva-grid > a,
            .opciones-reserva-grid > form,
            .opciones-reserva-grid > form > button {
                width: 100%;
                min-width: 0 !important;
                max-width: none !important;
                margin: 0 !important;
            }

            .opciones-reserva-grid > a,
            .opciones-reserva-grid > form > button {
                height: 120px;
                padding: 12px 8px !important;
                border: 0;
                border-radius: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
            }

            .opciones-reserva-grid > a > div,
            .opciones-reserva-grid > form > button > div {
                width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .opciones-reserva-grid h6 {
                width: 100%;
                margin: 6px 0 0 !important;
                text-align: center;
            }

            @media (max-width: 600px) {
                .modal-opciones-reserva {
                    width: 94vw;
                    border-radius: 24px;
                }

                .modal-opciones-reserva .modal-content {
                    padding: 22px 16px 8px;
                }

                .modal-opciones-reserva h5 {
                    font-size: 1.35rem;
                }

                .opciones-reserva-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 12px;
                    padding: 4px;
                }
            }

            .legend-container {
                background: #fff;
                padding: 15px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                max-width: 300px;
                font-family: 'Arial', sans-serif;
                text-align: center;
            }

            .legend-title {
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 10px;
                color: #333;
                text-transform: uppercase;
            }

            .legend-item {
                display: flex;
                align-items: center;
                margin: 8px 0;
            }

            .legend-color {
                width: 20px;
                height: 20px;
                border-radius: 50%;
                display: inline-block;
                margin-right: 10px;
            }

            .pending {
                background: #FFD700;
            }

            /* Amarillo */
            .in-attention {
                background: #FF5733;
            }

            /* Rojo */
            .finished {
                background: #28A745;
            }

            /* Verde */

            .legend-text {
                font-size: 14px;
                font-weight: 600;
                color: #444;
            }

            .calendar-header-container {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
            }

            .calendar-filters {
                margin: 4px 0 12px;
                padding: 0 8px 2px;
            }

            .calendar-filter-select {
                padding-top: 0 !important;
            }

            .calendar-filter-clear {
                padding-top: 18px !important;
            }

            @media (max-width: 992px) {
                .calendar-filters {
                    padding: 0 6px 8px;
                }

                .calendar-filters > .col {
                    margin: 0 !important;
                }

                .calendar-filter-clear {
                    padding-top: 0 !important;
                    text-align: right;
                }
            }

            @media (max-width: 600px) {
                .calendar-filter-clear button {
                    width: 100%;
                }
            }

            .timeline-container {
                display: flex;
                align-items: flex-start;
                gap: 10px;
            }

            .rooms-column {
                width: 200px;
                flex-shrink: 0;
                background: #f8f9fa;
                padding: 10px;
                border-radius: 8px;
                margin-top: 19px
            }

.room-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2px;
    border-radius: 10px;
    border-left: 6px solid transparent;
    transition: all 0.2s ease;
    height: 48px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);

}

.room-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}

.room-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.room-number {
    font-size: 1.1rem;
    font-weight: 800;
    width: 36px;
    text-align: right;
}

.room-icon {
    font-size: 1.2rem;
}

.room-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.room-type {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 12px;
    background: #e9ecef;
    text-transform: uppercase;
}

.room-state {
    font-size: 0.75rem;
    font-weight: 600;
    opacity: 0.9;
}

            .room-status {
                font-size: 12px;
                padding: 4px 6px;
                border-radius: 5px;
            }

            .disponible {
                background: #d4edda;
                color: #155724;
            }

            .ocupado {
                background: #f8d7da;
                color: #721c24;
            }

            .limpieza {
                background: #fff3cd;
                color: #856404;
            }

            .calendar-wrapper {
                display: flex;
                flex-direction: column;
                flex-grow: 1;
            }

            .calendar-controls {
                position: sticky !important;
                top: 0;
                z-index: 996;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                margin-bottom: 10px;
                padding: 0;
                background: transparent;
            }

            .calendar-controls.is-stuck {
                position: fixed !important;
                top: 0;
                box-sizing: border-box;
            }

            .calendar-sticky-sentinel {
                width: 1px;
                height: 1px;
                pointer-events: none;
            }

            .calendar-header {
                display: flex;
                justify-content: center;
                flex-grow: 1;
            }

            .day {
                min-width: 80px;
                text-align: center;
                padding: 10px;
                border: 1px solid #ddd;
                background: #fff;
                font-weight: bold;
                border-radius: 10px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .today {
                background: #ffffff !important;
                border: 2px solid #26a69a;
                color: #26a69a;
            }

            .day-number {
                font-size: 18px;
                font-weight: bold;
            }

            .calendar-body {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .calendar-row {
                display: flex;
            }

            .calendar-cell {
                min-width: 80px;
                height: 50px;
                border: 1px solid #ddd;
                border-radius: 5px;
                position: relative;
                transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease;
            }

            .calendar-cell-disponible > a.btn-cargar-crud {
                cursor: pointer;
            }

            .calendar-cell-disponible > a.btn-cargar-crud::after {
                content: "+";
                position: absolute;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #00897b;
                font-size: 25px;
                font-weight: 500;
                opacity: 0;
                transform: scale(.75);
                transition: opacity .18s ease, transform .18s ease;
            }

            .calendar-cell-disponible:has(> a.btn-cargar-crud:hover) {
                background-color: rgba(38, 166, 154, .12);
                border-color: #26a69a;
                box-shadow: inset 0 0 0 1px #26a69a, 0 3px 9px rgba(38, 166, 154, .2);
            }

            .calendar-cell-disponible > a.btn-cargar-crud:hover::after,
            .calendar-cell-disponible > a.btn-cargar-crud:focus-visible::after {
                opacity: 1;
                transform: scale(1);
            }

            .calendar-cell-disponible:has(> a.btn-cargar-crud:focus-visible) {
                background-color: rgba(38, 166, 154, .12);
                border-color: #26a69a;
                box-shadow: inset 0 0 0 2px #26a69a;
            }

            .timeline-container.calendar-expanded .room-card,
            .timeline-container.calendar-expanded .calendar-cell {
                height: 66px;
            }

            .timeline-container.calendar-expanded .reserva {
                height: 52px;
                line-height: 52px;
                margin-top: 7px;
            }

            .btn-floating {
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .reserva {
                position: absolute;
                height: 40px;
                line-height: 40px;
                text-align: center;
                border-radius: 5px;
                font-size: 14px;
                color: white;
                padding: 0 10px;
                font-weight: bold;
                z-index: 999;
                margin-top: 5px;
                border-radius: 50px;
            }

            .tooltip-reserva-calendario {
                position: absolute;
                background: #3f51b5;
                color: white;
                padding: 8px;
                border-radius: 8px;
                font-size: 12px;
                box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.2);
                display: block;
                z-index: 9999;
                pointer-events: none;
                white-space: nowrap;
                opacity: 0;
                visibility: hidden;
                transform: translateY(7px) scale(.98);
                transition: opacity .22s ease, transform .22s ease, visibility 0s linear .22s;
            }

            .tooltip-reserva-calendario[hidden] {
                display: none !important;
            }

            .tooltip-reserva-calendario.is-visible {
                opacity: 1;
                visibility: visible;
                transform: translateY(0) scale(1);
                transition-delay: 0s;
            }

            .tooltip-reserva-calendario::after {
                content: "";
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border-width: 5px;
                border-style: solid;
                border-color: #3f51b5 transparent transparent transparent;
            }
            .nav-btn {
    font-size: 22px;          /* 🔥 tamaño del icono */
    font-weight: bold;
    min-width: 42px;
    height: 42px;
    padding: 0;
    display: flex;

    justify-content: center;
}

        </style>
    @endsection
