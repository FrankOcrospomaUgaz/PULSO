@extends('layouts.app')

@section('template_title')
    Solicitudes
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">

                    <div class="row" id="filtros" style="margin-bottom: 10px;">
                        <div class="col s12 m2">
                            <div class="input-field">
                                <select id="tipo-filter" name="tipo" onchange="filterComprobantes()">
                                    <option value="0">Todos</option>
                                    @foreach ($tipodocumentos as $tipodocumento)
                                        <option value="{{ $tipodocumento->id }}"
                                            {{ request('tipodocumento') == $tipodocumento ? 'selected' : '' }}>
                                            {{ $tipodocumento->nombre }} </option>
                                    @endforeach
                                </select>
                                <label>Tipo</label>
                            </div>
                        </div>
                        @php
                            $fecha_inicio = date('Y-m-01'); // Primer día del mes actual
                            $fecha_fin = date('Y-m-d'); // Día actual
                        @endphp

                        <div class="col s12 m2">
                            <div class="input-field">
                                <input type="text" id="search-input" class="validate"
                                    data-url-base="{{ route('comprobante.reporte.index') }}">
                                <label for="search-input">Buscar</label>
                            </div>
                        </div>

                        <!-- Fecha de inicio -->
                        <div class="col s12 m2">
                            <div class="input-field">
                                <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ $fecha_inicio }}">
                                <label for="fecha_inicio" class="active">Fecha de inicio</label>
                            </div>
                        </div>

                        <!-- Fecha de fin -->
                        <div class="col s12 m2">
                            <div class="input-field">
                                <input type="date" id="fecha_fin" name="fecha_fin" value="{{ $fecha_fin }}">
                                <label for="fecha_fin" class="active">Fecha de fin</label>
                            </div>
                        </div>

                        <!-- Filtro de Situación -->
                        <div class="col s12 m2">
                            <div class="input-field">
                                <select id="situacion-filter" name="situacion" onchange="filterHabitaciones()">
                                    <option value="0">Todos</option>
                                    @foreach ($estados as $estado)
                                        <option value="{{ $estado }}"
                                            {{ request('estado') == $estado ? 'selected' : '' }}>
                                            {{ $estado }} </option>
                                    @endforeach
                                </select>
                                <label>Estado</label>
                            </div>
                        </div>



                    </div>

                    <div class="row valign-wrapper" style="margin-bottom: 10px;">
                        <div class="col s12 m8 left-align valign-wrapper">
                            <label for="records-per-page" style="margin-right: 10px;">Registros por página:</label>
                            <div class="input-field" style="margin: 0;">
                                <select id="records-per-page" class="browser-default">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <div class="col s12 m12  l4 search-with-report right-align ">

                            <button class="btn teal tooltipped btn-buscar" id="btn-Comprobante" data-tooltip="Buscar"
                                type="button" onclick="document.getElementById('page').value=1;buscar();"><i
                                    class="fa-solid fa-magnifying-glass white-text"></i></button>
                            <button class="btn red tooltipped truncated btn-pdf" id="btn-pdf-Comprobante"
                                data-tooltip="Reporte en PDF" type="button" onclick="generatePDFReport()">
                                <i class="fa-solid fa-file-pdf white-text right"></i>PDF
                            </button>

                            <button class="btn green tooltipped truncated btn-excel" id="btn-excel-Comprobante"
                                data-tooltip="Reporte en Excel" type="button" onclick="generateExcelReport()"><i
                                    class="fa-solid fa-file white-text right"></i>Excel</button>
                        </div>

                    </div>


                    <!-- Tabla de datos -->
                    <div id="tb-container" class="table-wrapper">
                        <!-- Contenedor con scroll horizontal solo para la tabla -->
                        <div class="table-scroll" style="overflow-x: auto;">
                            <table id="data-table" class="highlight striped centered"
                                style="width: max-content; min-width: 100%;">
                                <thead class="thead-custom">
                                    <tr>
                                        <th class="no-hide">Tipo</th>
                                        <th class="no-hide">Número</th>
                                        <th class="center">Nro. de documento</th>
                                        <th class="center">Persona</th>
                                        <th class="center">Moneda</th>
                                        <th class="center">Total</th>
                                        <th class="no-hide">Estado</th>
                                        <th class="center">Fecha de solicitud</th>
                                        <th class="center">F. de envío</th>
                                        <th class="center">F. de respuesta</th>
                                        <th class="center">F. de anulación</th>
                                        <th class="center">RUC de facturador</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($solicituds as $solicitud)
                                        <tr>
                                            <td class="center">
                                                {{ $solicitud->tipo_documento == 'B' ? 'Boleta' : 'Factura' }}
                                            </td>
                                            <td class="center">{{ $solicitud->serie . '-' . $solicitud->correlativo }}</td>
                                            <td class="center">{{ $solicitud->persona->personamaestro->nrodoc }}</td>
                                            <td class="center">{{ $solicitud->movimiento->persona }}</td>
                                            <td class="center">{{ $solicitud->moneda }}</td>
                                            <td class="center inpt-cantidad-mc">{{ $solicitud->total }}</td>
                                            <td class="center">
                                                @php
                                                    switch ($solicitud->estado) {
                                                        case 'R':
                                                            $estado = 'Aún no declarado';
                                                            $color = 'yellow black-text';
                                                            break;
                                                        case 'E':
                                                        case 'P':
                                                            $estado = 'No registrado';
                                                            $color = 'orange black-text';
                                                            break;
                                                        case 'M':
                                                        case 'T':
                                                            $estado = 'Declarado y activo';
                                                            $color = 'green';
                                                            break;
                                                        case 'B':
                                                            $estado = 'Declarado y anulado';
                                                            $color = 'red white-text';
                                                            break;
                                                        case 'I':
                                                            $estado = 'Declarado e incorrecto';
                                                            $color = 'purple white-text';
                                                            break;
                                                        case 'S':
                                                        case 'C':
                                                            $estado = 'Declarado sin comprobar';
                                                            $color = 'blue white-text';
                                                            break;
                                                        default:
                                                            $estado = 'No tributable';
                                                            $color = 'grey white-text';
                                                            break;
                                                    }
                                                @endphp
                                                <span class="new badge {{ $color }}"
                                                    data-badge-caption="{{ $estado }}"></span>
                                            </td>
                                            <td class="center">{{ $solicitud->fecha }}</td>
                                            <td class="center">{{ $solicitud->fecha_envio }}</td>
                                            <td class="center">{{ $solicitud->fecha_respuesta }}</td>
                                            <td class="center">{{ $solicitud->fecha_anulacion }}</td>
                                            <td class="center">
                                                {{ $solicitud->facturador->sucursal->empresa->ruc }}</td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                        <!-- Paginación -->
                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $solicituds->firstItem() }} a {{ $solicituds->lastItem() }} de
                                    {{ $solicituds->total() }} registros</p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $solicituds->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
