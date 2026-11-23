@extends('layouts.app')

@section('template_title')
    Movimientos de Reserva
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
    @php
        $user = Auth::user();
        $persona = App\Models\Persona::find($user->persona_id);
        $sucursal = App\Models\Sucursal::findOrFail($persona->sucursal_id);
        $contraseñaanulacion = App\Models\RelParametrosucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Contraseña para anulación de venta');
            })
            ->value('valor');


        $telefonoadmin = App\Models\RelParametrosucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Celular de administración');
            })
            ->value('valor');
    @endphp
    <input type="hidden" id="contraseñaanulacion" name="contraseñaanulacion" value="{{ $contraseñaanulacion }}">
    <input type="hidden" name="telefonoadmin" id="telefonoadmin" value='@json($telefonoadmin)'>

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
                            onclick="sendWhatsAppCodeForAnulacionVenta();" style="height: 36px;">
                            <i class="fa-brands fa-whatsapp"></i>
                        </button>

                        <button class="btn tooltipped" id="btn-codigo-aprobacion-movimiento-email"
                            data-tooltip="Solicitar código por e-mail" type="button" onclick="sendEmailCodeForAnulacionVenta();"
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

    <!-- Fondo oscuro para el modal de anulación -->
    <div id="modal-overlay-anular"
        style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1002;">
    </div>
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="without-bottom filtro" id="filtros">
                        <div class="col s12">
                            <div class="row without-bottom">
                                <div class="col s12 m6 l5">
                                    <div class="input-field">
                                        <input type="text" id="search-input" class="validate" value="{{ $search }}">
                                        <label for="search-input" class="{{ $search ? 'active' : '' }}">Buscar por cliente, documento, c&oacute;digo o habitaci&oacute;n...</label>
                                    </div>
                                </div>

                                <div class="col s12 m3 l2">
                                    <div class="input-field">
                                        <input id="txtFechaInicio" type="date" value="{{ $fechaInicio }}">
                                        <label for="txtFechaInicio" class="{{ $fechaInicio ? 'active' : '' }}">Fecha Inicio</label>
                                    </div>
                                </div>

                                <div class="col s12 m3 l2">
                                    <div class="input-field">
                                        <input id="txtFechaFin" type="date" value="{{ $fechaFin }}">
                                        <label for="txtFechaFin" class="{{ $fechaFin ? 'active' : '' }}">Fecha Fin</label>
                                    </div>
                                </div>

                                <div class="col s12 m6 l3">
                                    <div class="input-field">
                                        <select id="situacion-filter" onchange="filterReservasReport()">
                                            @foreach ($estados as $estado)
<option value="{{ $estado }}" {{ $estadoSeleccionado == $estado ? 'selected' : '' }}>
{{ $estado }}
</option>
                                            @endforeach
                                        </select>
                                        <label>Situaci&oacute;n</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row without-bottom">
                                <div class="col s12 m4 l2">
                                    <div class="input-field">
                                        <select id="origen-filter" onchange="filterReservasReport()">
                                            <option value="">Todos</option>
                                            @foreach ($origenes as $origen)
<option value="{{ $origen->id }}" {{ (string) $origenId === (string) $origen->id ? 'selected' : '' }}>
{{ $origen->descripcion }}
</option>
                                            @endforeach
                                        </select>
                                        <label>Origen</label>
                                    </div>
                                </div>

                                <div class="col s12 m4 l2">
                                    <div class="input-field">
                                        <select id="habitacion-filter" onchange="filterReservasReport()">
                                            <option value="">Todas</option>
                                            @foreach ($habitaciones as $habitacion)
    <option value="{{ $habitacion->id }}" {{ (string) $habitacionId === (string) $habitacion->id ? 'selected' : '' }}>
    {{ $habitacion->numero }}
    </option>
                                            @endforeach
                                        </select>
                                        <label>Habitaci&oacute;n</label>
                                    </div>
                                </div>

                                <div class="col s12 m4 l3">
                                    <div class="input-field">
                                        <select id="movimiento-situacion-filter" onchange="filterReservasReport()">
                                            @foreach ($movimientoSituaciones as $item)
<option value="{{ $item }}" {{ $movimientoSituacion === $item ? 'selected' : '' }}>
{{ $item }}
</option>
                                            @endforeach
                                        </select>
                                        <label>Situaci&oacute;n del movimiento</label>
                                    </div>
                                </div>

                                <div class="col s12 m12 l5 search-with-report right-align nowrap-buttons">
                                    <div class="input-field checkout-actions">
                                        <button class="btn teal tooltipped btn-buscar" data-tooltip="Buscar" type="button"
                                            onclick="filterReservasReport()">
                                            <i class="fa-solid fa-magnifying-glass white-text"></i>
                                        </button>
                                        <button class="btn red tooltipped btn-pdf" data-tooltip="PDF" type="button"
                                            onclick="generatePDFReportReservas()">
                                            <i class="fa-solid fa-file-pdf white-text left"></i> PDF
                                        </button>
                                        <button class="btn green tooltipped btn-excel" data-tooltip="Excel" type="button"
                                            onclick="generateExcelReportReservas()">
                                            <i class="fa-solid fa-file-excel white-text left"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row valign-wrapper">
                        <div class="col s12 m8 left-align valign-wrapper">
                            Registros por p&aacute;gina:
                            <div class="input-field" style="margin: 0;">
                                <select id="records-per-page" class="browser-default" onchange="filterReservasReport()">
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
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

                    @if ($message = Session::get('success'))
                        <div class="card-panel green lighten-4 green-text text-darken-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div id="tb-container" class="table-responsive">
                        <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                            <thead class="thead-custom">
                                <tr>
                                    <th class="center">C&oacute;digo</th>
                                    <th class="center">Id</th>
                                    <th class="ocultar">Estado real</th>
                                    <th class="center">F. Registro</th>
                                    <th class="center">Habitaci&oacute;n(es)</th>
                                    <th class="center">F. reserva</th>
                                    <th class="center">F. Inicio</th>
                                    <th class="center">F. Salida</th>
                                    <th class="center">Usuario</th>
                                    <th class="center">Cliente</th>
                                    <th class="center">Celular</th>
                                    <th class="center"># Personas</th>
                                    <th class="center">D&iacute;as</th>
                                    <th class="center">Total</th>
                                    <th class="center dtr-hidden" style="display: none;">Moneda</th>
                                    <th class="center">Estado</th>
                                    <th class="center">Situaci&oacute;n</th>
                                    <th class="center dtr-hidden" style="display: none;">Tipo Cambio</th>
                                    <th class="center dtr-hidden" style="display: none;">Comentario</th>
                                    <th class="center">F. Cancelaci&oacute;n</th>
                                    <th class="center">E-mail</th>
                                    <th class="center dtr-hidden" style="display: none;">Origen</th>
                                    <th class="center">Operaciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($reservas as $reserva)
                                    <tr class="dt-hasChild dtr-expanded">
                                        <td class="center dtr-control" tabindex="0">
                                            {{ str_pad($reserva->movimiento->numero, 8, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="center">{{ $reserva->id }}</td>
                                        <td class="ocultar">
                                            {{ $reserva->estado }}
                                            <a href="{{ route('finalizarmovreserva', $reserva->id) }}"
                                                class="btn-floating red darken-1 waves-light tooltipped btn-activar">
                                                <i class=""></i>
                                            </a>
                                        </td>
                                        <td class="center">{{ $reserva->created_at->format('Y-m-d') }}<br>{{ $reserva->created_at->format('h:i:s A') }}</td>
                                        <td class="inpt-cantidad-mc">
                                            {{ collect(json_decode($reserva->json_habitaciones))->pluck('numero')->map(fn($num) => trim($num))->join(', ') }}
                                        </td>
                                        <td class="center">
                                            {{ $reserva->fechavencimiento ? \Carbon\Carbon::parse($reserva->fechavencimiento)->format('Y-m-d') : '' }}<br>
                                            {{ $reserva->fechavencimiento ? \Carbon\Carbon::parse($reserva->fechavencimiento)->format('h:i:s A') : '' }}
                                        </td>
                                        <td class="center">
                                            {{ $reserva->fechainicio ? \Carbon\Carbon::parse($reserva->fechainicio)->format('Y-m-d') : '' }}<br>
                                            {{ $reserva->fechainicio ? \Carbon\Carbon::parse($reserva->fechainicio)->format('h:i:s A') : '' }}
                                        </td>
                                        <td class="center">
                                            {{ $reserva->fechasalida ? \Carbon\Carbon::parse($reserva->fechasalida)->format('Y-m-d') : '' }}<br>
                                            {{ $reserva->fechasalida ? \Carbon\Carbon::parse($reserva->fechasalida)->format('h:i:s A') : '' }}
                                        </td>
                                        <td>{{ strtoupper($reserva->movimiento->Responsable->usuario->name ?? 'N/A') }}</td>
                                        <td>{{ $reserva->movimiento->Persona->personamaestro->nrodoc ?? 'N/A' }} - <br>{{ $reserva->movimiento->persona ?? 'N/A' }}</td>
                                        <td class="center">{{ $reserva->celular }}</td>
                                        <td class="inpt-cantidad-mc">{{ $reserva->nropersonas }}</td>
                                        <td class="inpt-cantidad-mc">{{ $reserva->dias }}</td>
                                        <td class="inpt-cantidad-mc">{{ number_format($reserva->total, 2) }}</td>
                                        <td class="center dtr-hidden" style="display: none;">{{ $reserva->moneda }}</td>
                                        <td class="center">
                                            <span
                                                class="new badge 
                                                {{ $reserva->estado == 'PENDIENTE' ? 'yellow grey-text text-darken-3' : '' }}
                                                {{ str_contains($reserva->estado, 'ATENCI') ? 'blue white-text' : '' }}
                                                {{ $reserva->estado == 'FINALIZADO' ? 'teal white-text' : '' }}
                                                {{ $reserva->estado == 'AUSENTE' ? 'red white-text' : '' }}"
                                                data-badge-caption="{{ $reserva->estado }}">
                                            </span>
                                        </td>
                                        <td class="center">
                                            <span
                                                class="new badge 
                                                {{ $reserva->movimiento->situacion == 'Activado' ? 'green white-text' : '' }}
                                                {{ $reserva->movimiento->situacion == 'Desactivado' ? 'red white-text' : '' }}"
                                                data-badge-caption="{{ $reserva->movimiento->situacion }}">
                                            </span>
                                        </td>
                                        <td class="inpt-cantidad-mc dtr-hidden" style="display: none;">{{ number_format($reserva->tipocambio, 3) }}</td>
                                        <td class="center dtr-hidden" style="display: none;">{{ $reserva->observacion }}</td>
                                        <td class="center">{{ $reserva->fechacancelacion ?? '-' }}</td>
                                        <td class="center">{{ $reserva->email ?? '-' }}</td>
                                        <td class="center dtr-hidden" style="display: none;">{{ $reserva->origen->descripcion ?? '-' }}</td>
                                        <td class="center">
                                            @foreach ($operaciones as $operacion)
                                                @if ($operacion->tipo == 'Registro')
                                                    @if (
                                                        $operacion->accion === 'movreserva.atender' &&
                                                            $reserva->movimiento->situacion == 'Activado' &&
                                                            $reserva->estado == 'PENDIENTE')
                                                        <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                            data-icono="fa-solid fa-calendar-check"
                                                            data-titulo="{{ $operacion->comentario }}"
                                                            data-tooltip="{{ $operacion->comentario }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                    @if (
                                                        $operacion->accion === 'movreserva.anular' &&
                                                            $reserva->movimiento->situacion == 'Activado' &&
                                                            $reserva->estado == 'PENDIENTE')
                                                        <form action="{{ route($operacion->accion, $reserva->id) }}"
                                                            method="POST" class="anular-form" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if ($operacion->accion === 'movreserva.detalles')
                                                        <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                            data-icono="fa-solid fa-eye left"
                                                            data-tooltip="{{ $operacion->descripcion }}"
                                                            data-titulo="Movimientos de Reserva | Detalles">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                    @if (
                                                        $operacion->accion === 'movreserva.edit' &&
                                                            $reserva->movimiento->situacion == 'Activado' &&
                                                            $reserva->estado !== 'FINALIZADO')
                                                        <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                            data-tooltip="{{ $operacion->descripcion }}"
                                                            data-icono="fa-solid fa-EDIT left"
                                                            data-titulo="Movimientos de reserva | Editar">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                    @if ($operacion->accion === 'movreserva.generarPDF' && $reserva->movimiento->situacion == 'Activado')
                                                        <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                            data-icono="fa-solid fa-edit left"
                                                            data-titulo="{{ $operacion->descripcion }}"
                                                            data-tooltip="{{ $operacion->comentario }}" target="_blank">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                @endif
                                            @endforeach
                                            @if ($reserva->estado == 'FINALIZADO' && $reserva->movhabitacion)
                                                <a href="{{ route('movhabitacion.historial-ventas', $reserva->movhabitacion->id) }}"
                                                    class="btn btn-floating purple white-text tooltipped btn-crear"
                                                    data-tooltip="Historial de Ventas" type="button">
                                                    <i class="fa-solid fa-history left"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $reservas->firstItem() }} a {{ $reservas->lastItem() }} de {{ $reservas->total() }} registros</p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $reservas->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
