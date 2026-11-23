@extends('layouts.app')

@section('template_title')
    Checkouts de Habitaciones
@endsection


@section('styles')
<style>
    .nowrap-buttons .input-field .btn {
        white-space: nowrap;
    }

    .checkout-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
    }

    @media only screen and (max-width: 600px) {
        .nowrap-buttons {
            text-align: left;
        }

        .checkout-actions {
            justify-content: flex-start;
            flex-wrap: wrap;
            gap: 8px;
        }

        .checkout-actions .btn {
            width: calc(50% - 4px);
            margin: 0;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }
    }
</style>
@endsection
@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                 

                        <!-- Filtros superiores -->
                        <div class="without-bottom filtro" id="filtros">
                            <div class="col s12">
                                <div class="row without-bottom">
                                    <div class="col s12 m6 l6">
                                        <div class="input-field">
                                            <div class="select-wrapper">
                                                <select id="rel_turnocaja_id" name="rel_turnocaja_id" onchange="filtrarAlojamientos()"
                                                    tabindex="-1">
                                                    <option value="0">Todos</option>
                                                    @foreach ($relTurnocajas as $relTurno)
                                                        <option value="{{ $relTurno->id }}"
                                                            @if (($turnoEnCursoId ?? null) == $relTurno->id) selected @endif>
{{ $relTurno->movcajainicio->caja }} -
{{ $relTurno->movcajainicio->movimiento->movcaja->turno->nombre }} | {{ $relTurno->fechainicio }}
{{ empty($relTurno->fechafin) ? '(En curso)' : '- ' . $relTurno->fechafin }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <label for="rel_turnocaja_id">Turno</label>
                                        </div>
                                    </div>

                                    <div class="col s12 m3 l2">
                                        <div class="input-field">
                                            <input type="date" id="fecha_inicio" name="fecha_inicio"
                                                value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                                            <label for="fecha_inicio">Fecha Inicio</label>
                                        </div>
                                    </div>
                                    <div class="col s12 m2 l1">
                                        <div class="input-field">
                                            <input type="time" id="hora_inicio" name="hora_inicio" value="00:00">
                                            <label for="hora_inicio" class="active">Hora Inicio</label>
                                        </div>
                                    </div>

                                    <div class="col s12 m3 l2">
                                        <div class="input-field">
                                            <input type="date" id="fecha_fin" name="fecha_fin"
                                                value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                            <label for="fecha_fin">Fecha Fin</label>
                                        </div>
                                    </div>
                                    <div class="col s12 m2 l1">
                                        <div class="input-field">
                                            <input type="time" id="hora_fin" name="hora_fin" value="23:59">
                                            <label for="hora_fin" class="active">Hora Fin</label>
                                        </div>
                                    </div>

                                    
                                </div>

                                <div class="row without-bottom">
                                    <div class="col s12 m3 l2">
                                        <div class="input-field">
                                            <select id="modalidad-filter" name="modalidad">
                                                <option value="" selected>Todas</option>
                                                <option value="D">Noches</option>
                                                <option value="H">Horas</option>
                                            </select>
                                            <label for="modalidad-filter">Modalidad</label>
                                        </div>
                                    </div>
                                    <div class="col s12 m3 l2">
                                        <div class="input-field">
                                            <select id="situacion-filter" name="situacion">
                                                <option value="" selected>Todas</option>
                                                <option value="A">En curso</option>
                                                <option value="C">Culminada</option>
                                            </select>
                                            <label for="situacion-filter">Situación</label>
                                        </div>
                                    </div>
                                    <div class="col s12 m6 l4">
                                        <div class="input-field">
                                            <input type="text" id="search-input" class="validate">
                                            <label for="search-input">Buscar por habitación o cliente...</label>
                                        </div>
                                    </div>
                                    <div class="col s12 m12 l4 search-with-report right-align nowrap-buttons">
                                        <div class="input-field checkout-actions">
                                            <button class="btn teal tooltipped btn-buscar" data-tooltip="Buscar" type="button" 
                                                onclick="filtrarAlojamientos()">
                                                <i class="fa-solid fa-magnifying-glass white-text"></i>
                                            </button>
                                            <button class="btn red tooltipped btn-pdf" data-tooltip="PDF" type="button" 
                                                onclick="generarPDFAlojamientos()">
                                                <i class="fa-solid fa-file-pdf white-text left"></i> PDF
                                            </button>
                                            <button class="btn green tooltipped btn-excel" data-tooltip="Excel" type="button" 
                                                onclick="generarExcelAlojamientos()">
                                                <i class="fa-solid fa-file-excel white-text left"></i> Excel
                                            </button>
                                            <button class="btn purple tooltipped btn-resumen-pdf" id="btn-resumen-pdf"
                                                data-tooltip="Resumen en ticket" type="button"
                                                onclick="generarResumenPDF()">
                                                <i class="fa-solid fa-print white-text right"></i> Resumen
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Segunda fila: registros y botones -->
                        <div class="row valign-wrapper">
                            <div class="col s12 m8 left-align valign-wrapper">
                                 Registros por página:
                                <div class="input-field" style="margin: 0;">
                                    <select id="records-per-page" class="browser-default" onchange="filtrarAlojamientos()">
                                        <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col s2 m2 l4 right right-align">
                                
                                    <input id="expand-compress-rows-MovCaja" type="hidden" value="N">
                                <input id="mostrar-filtro-MovCaja" type="hidden" value="N">
                                <button class="btn-flat btn-grid-options tooltipped" id="btn-expand-compress-MovCaja"
                                    type="button" onclick="expandOrCompressChildRows();" data-position="top"
                                    data-tooltip="Expandir filas">
                                    <i class="fa-solid fa-expand" style="color:#343a40;"></i>
                                </button>

                                <button class="btn-flat btn-grid-options tooltipped" id="btn-filtro" type="button"
                                    onclick="mostrarFiltro();" data-position="top" data-tooltip="Filtros">
                                    <i class="fa-solid fa-magnifying-glass-minus" style="color:#343a40;"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de Checkouts -->
                        <div id="tb-container" class="table-responsive">
                            <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                                <thead class="thead-custom">
                                    <tr>
                            
                                        <th class="no-hide">Número</th>
                                        <th class="no-hide">Habitaciones</th>
                                        <th class="ocultar">Estado real</th>
                                        <th class="no-hide">Huespeds</th>
                                        <th class="no-hide">Ingreso</th>
                                        <th class="no-hide">Salida Programada</th>
                                        <th class="no-hide">Salida Real</th>
                                        <th class="no-hide">Estadía</th>
                                        <th>Early Check-In</th>
                                        <th>Late Checkout</th>
                                        <th>Descuento</th>
                                        <th class="no-hide">Total</th>
                                        <th>Moneda</th>
                                        <th>Origen</th>
                                        <th>Comentario</th>
                                         <th class="no-hide">Situación</th>
                                        <th class="no-hide">Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movimientoHabitacion as $index => $mov)
                               
                                        <tr>

                                          
                                            <td>{{ $mov->movimiento->numero ?? 'N/A' }}</td>
                                            <td class="inpt-cantidad-mc ">{{ collect(json_decode($mov->habitaciones))->pluck('numero')->map(fn($num) => trim($num))->join(', ') }}</td>
                                            <td class="ocultar">
                                                {{ $mov->situacion }}
                                                <a href="{{ route( 'reactivarmovhabitacion', $mov->id) }}"
                                                    class="btn-floating red darken-1 waves-light tooltipped btn-activar">
                                                    <i class=""></i>
                                                </a>
                                                <a href="{{ route('finalizarmovhabitacion', $mov->id) }}"
                                                    class="btn-floating blue darken-1 waves-light tooltipped btn-activar">
                                                    <i class=""></i>
                                                </a>
                                            </td>
                                             <td>
                    @foreach($mov->huespeds as $huesped)
                    {{ $huesped->persona->personamaestro->nrodoc }} - {{ $huesped->persona->nombres }} {{ $huesped->persona->apellidos }}<br>
                    @endforeach
                    @if($mov->huespeds->isEmpty())
                        0 -CLIENTES VARIOS
                    @endif
                </td>
                                            <td>{{ \Carbon\Carbon::parse($mov->fechaingreso)->format('Y-m-d') }}<br>{{ \Carbon\Carbon::parse($mov->fechaingreso)->format('h:i:s A') }}</td>

                                            <td>
                                                @if($mov->fechasalida)
                                                    {{ \Carbon\Carbon::parse($mov->fechasalida)->format('Y-m-d') }}<br>{{ \Carbon\Carbon::parse($mov->fechasalida)->format('h:i:s A') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            
                                            <td>
                                                @if($mov->fecha_salida_real)
                                                    {{ \Carbon\Carbon::parse($mov->fecha_salida_real)->format('Y-m-d') }}<br>{{ \Carbon\Carbon::parse($mov->fecha_salida_real)->format('h:i:s A') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            
                                            <td>
                                                {{ $mov->horas 
                                                    ? $mov->horas . ' hrs' 
                                                    : ($mov->dias == 1 
                                                        ? '1 noche' 
                                                        : ( number_format($mov->dias, 0) ?? 0) . ' noches') 
                                                }}
                                            </td>
                                            <td class="inpt-cantidad-mc "> {{ number_format($mov->early_checkin ?? 0, 2) }}</td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($mov->late_checkout ?? 0, 2) }}</td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($mov->descuento ?? 0, 2) }}</td>
                                            <td class="inpt-cantidad-mc " > {{ number_format($mov->total, 2) }}</td>
                                            <td>{{ $mov->moneda }}</td>
                                            <td>{{ $mov->movimiento->tipomovimiento->descripcion }}</td>
                                        
                                            <td>{{ $mov->comentario ?? '-' }}</td>
                                            <td>
                                                <span class="new badge {{ $mov->movimiento->situacion === 'Activado' ? 'green' : 'red' }}"
                                                    data-badge-caption="{{ $mov->movimiento->situacion }}">
                                                </span>
                                            </td>
                                            <td>
                                                <span class="new badge {{ $mov->situacion === 'C' ? 'blue' : 'yellow black-text' }}"
                                                    data-badge-caption="{{ $mov->situacion === 'C' ? 'Culminada' : 'En curso' }}">
                                                </span>
                                            </td>
                                            <td>
                                               
                                                    <!-- Botón para ver el historial de ventas -->
                                                    <a href="{{ route('movhabitacion.historial-ventas', $mov->id) }}" 
                                                        class="btn btn-floating purple white-text tooltipped mt20 ml10 truncate btn-crear" id="btn-historial-ventas"
                                                        data-tooltip="Historial de Ventas" type="button">
                                                        <i class="fa-solid fa-history left"></i>
                                                    </a>
                                                    <a href="{{ route('checkouts.detalleConsumoPdf', $mov->id) }}"
                                                        class="btn btn-floating red white-text tooltipped mt20 ml10"
                                                        data-tooltip="Detalle de consumo PDF" target="_blank" type="button">
                                                        <i class="fa-solid fa-file-pdf"></i>
                                                    </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                
                                
                            </table>

                            <!-- Paginación -->
                            <div class="row">
                                <div class="col s12 m6">
                                    <p>Mostrando {{ $movimientoHabitacion->firstItem() }} a {{ $movimientoHabitacion->lastItem() }} de
                                        {{ $movimientoHabitacion->total() }} registros</p>
                                </div>
    
                                <!-- Paginación -->
                                <div class="col s12 m6 right-align">
                                    {{ $movimientoHabitacion->appends(request()->query())->links() }}
                                </div>
    
                            </div>
                        </div>
                   
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para mostrar el historial de ventas -->
<div id="modal-ventas" class="modal">
    <div class="modal-content">
        <h4>Historial de Ventas</h4>
        <div id="ventas-list">
            <!-- Aquí irán las ventas cargadas dinámicamente -->
            <p>Cargando ventas...</p>
        </div>
    </div>
    <div class="modal-footer">
        <button class="modal-close btn-flat">Cerrar</button>
    </div>
</div>
@endsection



