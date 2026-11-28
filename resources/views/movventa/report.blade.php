@extends('layouts.app')

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
    <div id="anular-venta-modal" class="modal modal-large" style="z-index: 100001; display: none;">
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
        style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:100000;">
    </div>
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="card-content" style="margin-top: -20px">
                        <div class="row without-bottom filtro" id="filtro-MovVenta">
                            <div class="col s12 m6 l2">
                                <div class="input-field">
                                    <select id="caja_id" name="caja_id">
                                        @foreach ($cajas as $caja)
                                            <option value="{{ $caja->id }}"
{{ old('caja_id') == $caja->id ? 'selected' : '' }}>{{ $caja->numero }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="caja_id">Caja</label>
                                </div>
                            </div>
                            <div class="col s12 m6 l6">
                                <div class="input-field">
                                    <div class="select-wrapper">
                                        <select id="rel_turnocaja_id" name="rel_turnocaja_id" onchange="filterVentas()"
                                            tabindex="-1">
                                            <option value="0">Todos</option>
                                            @foreach ($relTurnocajas as $relTurno)
                                                <option value="{{ $relTurno->id }}"
                                                    @if (empty($relTurno->fechafin)) selected @endif>
{{ $relTurno->movcajainicio->caja }} -
{{ $relTurno->movcajainicio->movimiento->movcaja->turno->nombre ?? '' }} |
{{ $relTurno->fechainicio }}
{{ empty($relTurno->fechafin) ? '(En curso)' : '- ' . $relTurno->fechafin }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>
                                    <label for="rel_turnocaja_id">Turno</label>
                                </div>
                            </div>
                            <div class="col s12 m6 l4">
                                <div class="input-field">
                                    <input class="submit-search" id="txtBuscar" name="txtBuscar" type="text">
                                    <label for="txtBuscar">Buscar</label>
                                </div>
                            </div>
                            <div class="col s12 m6 l2">
                                <div class="input-field">
                                    <input id="txtFechaInicio" name="txtFechaInicio" type="date" value=" ">
                                    <label for="txtFechaInicio">Fecha de inicio</label>
                                </div>
                            </div>
                            <div class="col s12 m6 l2">
                                <div class="input-field">
                                    <input id="txtFechaFin" name="txtFechaFin" type="date" value=" ">
                                    <label for="txtFechaFin">Fecha de fin</label>
                                </div>
                            </div>
                            <div class="col s12 m6 l2">
                                <div class="input-field">
                                    <div class="select-wrapper">
                                        <select id="tipo-filter" name="tipo" onchange="filterVentas()">
                                            <option value="0">Todos</option>
                                            @foreach ($tipodocumentos as $tipodocumento)
<option value="{{ $tipodocumento->id }}"
{{ request('tipodocumento') == $tipodocumento ? 'selected' : '' }}>
{{ $tipodocumento->nombre }}
</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label for="txtIdTipodocumento">Tipo</label>
                                </div>
                            </div>


                            <div class="row valign-wrapper" style="margin-bottom: -25px;">
                                <div class="col s12 m8 left-align valign-wrapper">
                                    <label for="records-per-page" style="margin-right: 10px;">Registros por página:</label>
                                    <div class="input-field" style="margin: 0;">
                                        <select id="records-per-page" class="browser-default" onchange="filterVentas()">
                                            <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10
                                            </option>
                                            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25
                                            </option>
                                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50
                                            </option>
                                            <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100
                                            </option>
                                        </select>
                                    </div>
                                </div>


                                <div class="col s12 m12  l4 search-with-report right-align ">
                                    <div class="input-field">
                                        <button class="btn teal tooltipped btn-buscar" id="btn-MovVenta"
                                            data-tooltip="Buscar" type="button" onclick="filterVentas()"><i
                                                class="fa-solid fa-magnifying-glass white-text"></i></button>
                                        <button class="btn red tooltipped truncated btn-pdf" id="btn-pdf-MovVenta"
                                            data-tooltip="Reporte en PDF" type="button"
                                            onclick="generatePDFReportVentas()"><i
                                                class="fa-solid fa-file-pdf white-text right"></i>PDF</button>
                                        <button class="btn green tooltipped truncated btn-excel" id="btn-excel-MovVenta"
                                            data-tooltip="Reporte en Excel" type="button"
                                            onclick="generateExcelReportVentas()"><i
                                                class="fa-solid fa-file white-text right"></i>Excel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($message = Session::get('success'))
                        <div class="card-panel green lighten-4 green-text text-darken-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div id="tb-container" class="table-responsive">
                        <table id="data-table" class="highlight striped centered" style="width:100%">
                            <thead class="thead-custom">
                                <tr>
                                    <th>Comprobante</th>
                                    <th>Tipo</th>
                                    <th>Subtotal</th>
                                    <th>IGV</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th>Persona</th>
                                    <th>Responsable</th>
                                    <th>Tipo de detalle</th>

                                    <th>Situación</th>
                                    <th>Moneda</th>
                                    <th>T. cambio</th>
                                    <th>Por consumo</th>
                                    <th>Tipo de pago</th>
                                    <th>Comentario</th>
                                    <th>Estado en SUNAT</th>
                                    <th>Origen</th>
                                    <th>Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movventas as $movventa)
                                    <tr>
                                        <td>{{ $movventa->serie }}-{{ $movventa->movimiento->numero }}-{{ $movventa->anio }}
                                        </td>
                                        <td>{{ $movventa->movimiento->tipodocumento->nombre }}</td>
                                        <td class="inpt-cantidad-mc ">{{ number_format($movventa->subtotal, 2) }}</td>
                                        <td class="inpt-cantidad-mc ">{{ number_format($movventa->igv, 2) }}</td>
                                        <td class="inpt-cantidad-mc ">{{ number_format($movventa->total, 2) }}</td>
                                        <td>{{ $movventa->movimiento->fecha }}</td>
                                        <td>{{ $movventa->movimiento->usuario ?? 'N/A' }}</td>
                                        <td>{{ $movventa->movimiento->Persona->personamaestro->nrodoc ?? 'N/A' }} -
                                            {{ $movventa->movimiento->persona ?? 'N/A' }}</td>
                                        <td>{{ $movventa->movimiento->Responsable->personamaestro->nrodoc ?? 'N/A' }} -
                                            {{ $movventa->movimiento->responsable ?? 'N/A' }}</td>
                                        <td>{{ $movventa->tipo_detalle }}</td>

                                        <td>
                                            <span
                                                class="new badge {{ $movventa->movimiento->situacion == 'Activado' ? 'green' : 'red' }}"
                                                data-badge-caption="{{ $movventa->movimiento->situacion }}"></span>
                                        </td>
                                        <td>{{ $movventa->moneda }}</td>
                                        <td>{{ $movventa->tipocambio }}</td>

                                        <td>{{ $movventa->consumo }}</td>
                                        <td>{{ $movventa->tipo_pago }}</td>
                                        <td>{{ $movventa->comentario ?? 'N/A' }}</td>
                                        <td>
                                            <span
                                                class="new badge {{ $movventa->estado == 'No tributable' ? 'green' : 'blue' }}"
                                                data-badge-caption="{{ $movventa->estado }}"></span>
                                        </td>
                                        <td>{{ $movventa->movimiento->tipodocumento->tipomovimiento->descripcion ?? 'N/A' }}
                                        </td>
                                        <td>
                                            @foreach ($operaciones as $operacion)
                                                @if ($operacion->tipo == 'Registro')
                                                    {{-- Botón de ver detalles --}}
                                                    @if ($operacion->accion === 'movventa.verDetalles')
                                                        <a href="{{ route($operacion->accion, $movventa->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                            data-icono="fa-solid fa-edit left"
                                                            data-tooltip="{{ $operacion->descripcion }}"
                                                            data-titulo="Movimientos de Venta | Detalles">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                    @if ($operacion->accion === 'movventa.edit' && $movventa->movimiento->situacion == 'Activado')
                                                        <a href="{{ route($operacion->accion, $movventa->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                            data-icono="fa-solid fa-edit left"
                                                            data-titulo="{{ $operacion->descripcion }}"
                                                            data-tooltip="{{ $operacion->comentario }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                    @if ($operacion->accion === 'movventa.generarPDF' && $movventa->movimiento->situacion == 'Activado')
                                                        <a href="{{ route($operacion->accion, $movventa->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                            data-icono="fa-solid fa-edit left"
                                                            data-titulo="{{ $operacion->descripcion }}"
                                                            data-tooltip="{{ $operacion->comentario }}" target="_blank">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                    @if ($operacion->accion === 'movventa.imprimirComprobante' && $movventa->movimiento->situacion == 'Activado')
                                                        <a href="{{ route($operacion->accion, $movventa->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                            data-icono="fa-solid fa-edit left"
                                                            data-titulo="{{ $operacion->descripcion }}"
                                                            data-tooltip="{{ $operacion->comentario }}" target="_blank">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                    @if ($operacion->accion === 'movventa.anular' && $movventa->movimiento->situacion == 'Activado')
                                                    <form action="{{ route($operacion->accion, $movventa->id) }}"
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

                                                @endif
                                            @endforeach
                                            @if (($movventa->movimiento->tipodocumento_id == 9 || ($movventa->movimiento->tipodocumento->nombre ?? '') === 'Ticket de venta') && $movventa->movimiento->situacion == 'Activado')
                                                <a href="{{ route('movventa.convertirAComprobante', $movventa->id) }}"
                                                    class="btn-floating orange darken-1 waves-light btn-crear tooltipped"
                                                    data-icono="fa-solid fa-file-circle-check"
                                                    data-titulo="Convertir a comprobante"
                                                    data-tooltip="Convertir ticket a comprobante electrónico">
                                                    <i class="fa-solid fa-file-circle-check"></i>
                                                </a>
                                            @endif
                                        </td>


                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2"></td> <!-- Dejar las columnas vacías anteriores -->
                                    <td class="center-align" style="font-size: 1.5rem; font-weight: bold;">
                                        {{ number_format($totalPaginaSubtotal, 2) }}</td> <!-- Total -->
                                    <td class="center-align" style="font-size: 1.5rem; font-weight: bold;">
                                        {{ number_format($totalPaginaIgv, 2) }}</td> <!-- Total -->
                                    <td class="center-align" style="font-size: 1.5rem; font-weight: bold;">
                                        {{ number_format($totalPagina, 2) }}</td> <!-- Total -->

                                    <td colspan="14"></td> <!-- Colspan para cubrir columnas restantes -->
                                </tr>
                            </tfoot>
                        </table>

                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $movventas->firstItem() }} a {{ $movventas->lastItem() }} de
                                    {{ $movventas->total() }} registros</p>
                            </div>

                            <!-- Paginación -->
                            <div class="col s12 m6 right-align">
                                {{ $movventas->appends(request()->query())->links() }}
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
