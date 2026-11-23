@extends('layouts.app')

@section('content')
    <style>
        .card-supertitle {
            font-size: 3rem !important;
            font-weight: 700 !important;
        }

        .bold {
            font-weight: 900 !important;
        }
    </style>
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="row without-bottom">
                        @php
                            $boleta = $ventasPorTipoDocumento[4] ?? null;
                            $ticket = $ventasPorTipoDocumento[9] ?? null;
                            $factura = $ventasPorTipoDocumento[10] ?? null;
                        @endphp
                        @php
                            use Carbon\Carbon;
                            Carbon::setLocale('es');
                            $mesActual = Carbon::now()->translatedFormat('F Y'); // "mayo 2025"
                            $mesActual = ucfirst($mesActual); // "Mayo 2025"
                        @endphp

                        <!-- 🟩 Ticket -->
                        <div class="col s12 m4">
                            <div class="card minicard light-green accent-1">
                                <div class="card-content grey-text text-darken-3">
                                    <span class="card-supertitle">
                                        <font id="total-9-pen">
                                            {{ number_format($ticket['totales']['PEN'] ?? 0, 2) }}
                                        </font>
                                        <a class="tooltipped grey-text text-darken-3 right"
                                            onclick="totalPorTipoDocumentoAlMes(9);" href="#" data-tooltip="Actualizar"
                                            data-position="top">
                                            <i class="fa-solid fa-piggy-bank"></i>
                                        </a>
                                    </span>
                                    <p class="bold">Total de {{ $ticket['nombre'] ?? 'Ticket' }} (PEN) en {{ $mesActual }}
                                    </p>
                                    <p>+ <font id="total-9-usd">
                                            {{ number_format($ticket['totales']['USD'] ?? 0, 2) }}
                                        </font> USD</p>
                                </div>
                            </div>
                        </div>

                        <!-- 🟡 Boleta -->
                        <div class="col s12 m4">
                            <div class="card minicard amber">
                                <div class="card-content grey-text text-darken-3">
                                    <span class="card-supertitle">
                                        <font id="total-4-pen">
                                            {{ number_format($boleta['totales']['PEN'] ?? 0, 2) }}
                                        </font>
                                        <a class="tooltipped grey-text text-darken-3 right"
                                            onclick="totalPorTipoDocumentoAlMes(4);" href="#" data-tooltip="Actualizar"
                                            data-position="top">
                                            <i class="fa-solid fa-hand-holding-dollar"></i>
                                        </a>
                                    </span>
                                    <p class="bold">Total de Boleta (PEN) en {{ $mesActual }}
                                    </p>
                                    <p>+ <font id="total-4-usd">
                                            {{ number_format($boleta['totales']['USD'] ?? 0, 2) }}
                                        </font> USD</p>
                                </div>
                            </div>
                        </div>

                        <!-- 🔴 Factura -->
                        <div class="col s12 m4">
                            <div class="card minicard red accent-1">
                                <div class="card-content grey-text text-darken-3">
                                    <span class="card-supertitle">
                                        <font id="total-10-pen">
                                            {{ number_format($factura['totales']['PEN'] ?? 0, 2) }}
                                        </font>
                                        <a class="tooltipped grey-text text-darken-3 right"
                                            onclick="totalPorTipoDocumentoAlMes(10);" href="#" data-tooltip="Actualizar"
                                            data-position="top">
                                            <i class="fa-solid fa-landmark"></i>
                                        </a>
                                    </span>
                                    <p class="bold">Total de {{ $factura['nombre'] ?? 'Factura' }} (PEN) en {{ $mesActual }}
                                    </p>
                                    <p>+ <font id="total-10-usd">
                                            {{ number_format($factura['totales']['USD'] ?? 0, 2) }}
                                        </font> USD</p>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros de búsqueda -->
                        <div class="col s12">
                            <div class="card minicard">
                                <form id="frmMantReporte">
                                    <input type="hidden" name="_token" value="DLUJOGqOcmSigO8W3rNx3O7M0PrYj4z5dteGqlP9"
                                        autocomplete="off">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-magnifying-glass left"></i> Filtros
                                            de búsqueda</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l2">
                                                <input id="fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ $fechaInicio->format('Y-m-d') }}">
                                                <label for="fecha_inicio" class="active">Fecha de inicio</label>
                                            </div>

                                            <div class="input-field col s12 m6 l2">
                                                <input id="hora_inicio" name="hora_inicio" type="time" value="00:00:00">
                                                <label for="hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l2">
                                                <input id="fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ $fechaFin->format('Y-m-d') }}">
                                                <label for="fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l2">
                                                <input id="hora_fin" name="hora_fin" type="time" value="23:59:59">
                                                <label for="hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l3">
                                                <button class="btn teal tooltipped btn-buscar" id="btn-Reporte"
                                                    data-tooltip="Actualizar todo" type="button"
                                                    onclick="actualizarGraficosDeMovCaja();"><i
                                                        class="fa-solid fa-magnifying-glass white-text"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Izquierda -->
                        <div class="col s12 m12 l6 left-div">
                            <!-- Tipos de documento -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title">
                                            <i class="fa-solid fa-chart-pie left"></i>
                                            Totales por tipo de documento
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button" onclick="tiposDocumento();">
                                                <i class="fa-solid fa-arrow-rotate-right"></i>
                                            </button>
                                        </span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div id="amchart-tipos-documento" style="width: 100%; height: 400px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Evolución de tipos de documento -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-chart-line left"></i>
                                            Evolución de tipos de documento
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button"
                                                onclick="evolucionTiposDocumento();"><i
                                                    class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div id="chartdiv-evolucion-tipos-documento"
                                                    style="width: 100%; height: 500px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Descuentos por usuario -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-percentage left"></i>
                                            Descuentos hechos por usuarios
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button" onclick="descuentosPorUsuario();"><i
                                                    class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div class="chart-container" style="position: relative; width:100%;">
                                                    <div id="chartdiv-descuentos-usuario"
                                                        style="width: 100%; height: 500px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-handshake left"></i>
                                            Cortesías totales hechas por usuarios
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button" onclick="cortesiasPorUsuario();"><i
                                                    class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div class="chart-container" style="position: relative; width:100%;">
                                                    <div id="chartdiv-cortesias-usuario"
                                                        style="width: 100%; height: 265px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Derecha -->
                        <div class="col s12 m12 l6 right-div">
                            <!-- Evolución de ventas -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-chart-line left"></i>
                                            Evolución de ventas
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button" onclick="evolucionVentas();"><i
                                                    class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div class="chart-container" style="position: relative; width:100%;">
                                                    <div id="chartdiv-evolucion-ventas" style="width: 100%; height: 500px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Descuentos por persona -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-percentage left"></i>
                                            Descuentos por persona
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button" onclick="descuentosPorPersona();"><i
                                                    class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div class="chart-container" style="position: relative; width:100%;">
                                                    <div id="chartdiv-descuentos-persona"
                                                        style="width: 100%; height: 500px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-chart-pie left"></i>
                                            Top de 10 habitaciones con más descuento
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button"
                                                onclick="topProductosConDescuento();"><i
                                                    class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div class="chart-container" style="position: relative; width:100%;">
                                                    <div id="top-habitaciones-descuento"
                                                        style="width: 100%; height: 400px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-handshake left"></i>
                                            Cortesías totales por persona
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button" onclick="cortesiasPorPersona();"><i
                                                    class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div class="chart-container" style="position: relative; width:100%;">
                                                    <div id="chartdiv-cortesias-persona"
                                                        style="width: 100%; height: 265px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-chart-pie left"></i>
                                            Top de 10 habitaciones dados como cortesía
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button"
                                                onclick="topHabitacionesConCortesia();"><i
                                                    class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div class="chart-container" style="position: relative; width:100%;">
                                                    <div id="top-habitaciones-cortesia" style="width: 100%; height: 530px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="descuentos_usuario_data" value='@json($descuentosPorUsuario)'>
    <input type="hidden" id="cortesias_usuario_data" value='@json($cortesiasPorUsuario)'>
    <input type="hidden" id="cortesias_persona_data" value='@json($cortesiasPorPersona)'>
    <input type="hidden" id="top_habitaciones_cortesia_data" value='@json($topHabitacionesCortesia)'>
    <input type="hidden" id="tipos_documento_data" value='@json($ventasPorTipoDocumento)'>
    <input type="hidden" id="descuentos_persona_data" value='@json($descuentosPorPersona)'>
    <input type="hidden" id="evolucion_tipos_documento_ventas_data" value='@json($ventasAgrupadas)' />
    <input type="hidden" id="evolucion_ventas_data" value='@json($evolucionVentas)'>
    <input type="hidden" id="top_habitaciones_descuento_data" value='@json($topHabitacionesDescuento)'>

@endsection