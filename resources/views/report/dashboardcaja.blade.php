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
                        <!-- Resúmenes -->
                        <!-- Filtros de búsqueda -->
                        <div class="col s12">
                            <div class="card minicard">
                                <form id="frmMantReporte">
                                    <input type="hidden" name="_token" value="Qx0rDIGVIW859BWgjaB5k621XZzh9LfWh1ACjVDZ" autocomplete="off">                                <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-magnifying-glass left"></i> Filtros de búsqueda</span>
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
                                                <button class="btn teal tooltipped btn-buscar" id="btn-Reporte" data-tooltip="Actualizar todo" type="button" onclick="actualizarGraficosDeMovCaja();"><i class="fa-solid fa-magnifying-glass white-text"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Izquierda -->
                        <div class="col s12 m12 l6 left-div">
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title">
                                            <i class="fa-solid fa-arrow-trend-up left"></i>
                                            Ingresos vs egresos
                                            <button class="btn btn-floating tooltipped ml10 truncate right" data-tooltip="Actualizar" type="button" onclick="ingresosVsEgresos();">
                                                <i class="fa-solid fa-arrow-rotate-right"></i>
                                            </button>
                                        </span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div id="amchart-ingresos-egresos" style="width: 100%; height: 400px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Egresos por método de pago -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-turn-down left"></i>
                                            Egresos por método de pago
                                            <button class="btn btn-floating tooltipped ml10 truncate right" data-tooltip="Actualizar" type="button" onclick="egresosPorMetodoDePago();"><i class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                                                               <div id="chart-egresos-metodo-pago" style="width: 100%; height: 400px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Egresos por concepto de pago -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-turn-down left"></i>
                                            Egresos por concepto de pago
                                            <button class="btn btn-floating tooltipped ml10 truncate right" data-tooltip="Actualizar" type="button" onclick="egresosPorConceptoDePago();"><i class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                               <div id="chart-egresos-concepto-pago" style="width: 100%; height: 400px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Derecha -->
                        <div class="col s12 m12 l6 right-div">
                         <!-- Ingresos por método de pago -->
<div class="col s12">
    <div class="card minicard">
        <div class="card-content">
            <span class="card-title">
                <i class="fa-solid fa-turn-up left"></i>
                Ingresos por método de pago
                <button class="btn btn-floating tooltipped ml10 truncate right"
                    data-tooltip="Actualizar" type="button"
                    onclick="ingresosPorMetodoDePago();">
                    <i class="fa-solid fa-arrow-rotate-right"></i>
                </button>
            </span>
            <div class="row without-tb">
                <div class="input-field col s12">
                    <div id="chart-ingresos-metodo-pago" style="width: 100%; height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
                            <!-- Ingresos por concepto de pago -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-turn-up left"></i>
                                            Ingresos por concepto de pago
                                            <button class="btn btn-floating tooltipped ml10 truncate right" data-tooltip="Actualizar" type="button" onclick="ingresosPorConceptoDePago();"><i class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div id="chart-ingresos-concepto-pago" style="width: 100%; height: 400px;"></div>
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
                                            <button class="btn btn-floating tooltipped ml10 truncate right" data-tooltip="Actualizar" type="button" onclick="evolucionTiposDocumento();"><i class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div id="chartdiv-evolucion-tipos-documento" style="width: 100%; height: 400px;"></div>
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
<input type="hidden" id="ingresos_concepto_pago_data" value='@json($ingresosPorConcepto)'>
    <input type="hidden" id="ingresos_vs_egresos_data" value='@json($ingresosVsEgresos)'>
        <input type="hidden" id="egresos_metodo_pago_data" value='@json($egresosPorMetodo)'>
    <input type="hidden" id="ingresos_metodo_pago_data" value='@json($ingresosPorMetodo)'>
<input type="hidden" id="egresos_concepto_pago_data" value='@json($egresosPorConcepto)'>
<input type="hidden" id="evolucion_tipos_documento_data" value='@json($evolucionTiposDocumento)'>

@endsection
