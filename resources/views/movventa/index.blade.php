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
    <input type="hidden" id="contraseñaanulacion" name="contraseñaanulacion" value={{ $contraseñaanulacion }}>
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
                  <!-- 🔹 FILTROS SUPERIORES -->
<div class="row" id="filtros" style="margin-bottom: 10px; display: flex; flex-wrap: wrap;">
    <!-- Buscador + Botón Buscar -->
    <div class="col s12 m6 left-align order-mobile-2 order-desktop-1">
        <div style="display: flex; align-items: flex-start; gap: 5px; width: 100%;">
            <!-- Input con label -->
            <div class="input-field" style="flex: 1; margin-top: 0;">
                <input type="text" id="search-input" class="validate"
                    data-url-base="{{ route('movventa.index') }}">
                <label for="search-input">Buscar</label>
            </div>

            <!-- Botón Buscar -->
            <a class="btn waves-effect waves-light darken-2 tooltipped btn-buscar" data-tooltip="Buscar"
                type="button" onclick="realizarBusqueda();">
                <i class="fa-solid fa-magnifying-glass white-text"></i>
            </a>
        </div>
    </div>

    <!-- Botón NUEVO -->
    <div class="col s12 m6 right-align order-mobile-1 order-desktop-2"
        style="margin-top:0; padding-top:0;">
        @foreach ($operaciones as $operacion)
            @if ($operacion->tipo == 'Tabla')
                <a href="{{ route($operacion->accion) }}"
                    class="btn waves-effect waves-light btn-cargar-crud {{ $operacion->color }}"
                    data-icono="fa-solid fa-plus left" data-titulo="{{ $operacion->comentario }}"
                    style="border-radius: 10px; margin-top:0;">
                    {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                </a>
            @endif
        @endforeach
    </div>
</div>

<!-- 🔹 SELECTOR DE REGISTROS Y BOTONES -->
<div class="row valign-wrapper" style="margin-bottom: 10px;">
    <!-- Selector de registros -->
    <div class="col s12 m8 l8 left-align valign-wrapper" style="padding-left:13px;">
        <span style="margin-right: 8px;">Registros por página:</span>
        <div class="input-field" style="margin: 0;">
            <select id="records-per-page" class="browser-default" onchange="realizarBusqueda()">
                <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
            </select>
        </div>
    </div>

    <!-- Botones de expansión y filtros -->
    <div class="col s12 m4 l4 right-align"
        style="display: flex; align-items: center; justify-content: flex-end; padding-right:0;">
        <button class="btn-flat btn-grid-options tooltipped" id="btn-expand-compress" type="button"
            onclick="expandOrCompressChildRows();" data-position="top" data-tooltip="Expandir filas">
            <i class="fa-solid fa-expand" style="color:#343a40;"></i>
        </button>

        <button class="btn-flat btn-grid-options tooltipped" id="btn-filtro" type="button"
            onclick="mostrarFiltro();" data-position="top" data-tooltip="Filtros">
            <i class="fa-solid fa-magnifying-glass-minus" style="color:#343a40;"></i>
        </button>
    </div>
</div>



                    <div id="tb-container" class="table-wrapper">
                        <!-- Contenedor con scroll horizontal solo para la tabla -->
                        <div class="table-scroll" style="overflow-x: auto;">
                            <table id="data-table" class="highlight striped centered"
                                style="width: max-content; min-width: 100%;">
                                <thead class="thead-custom">
                                    <tr>
                                        <th class="no-hide">#</th>

                                        <th class="no-hide">Comprobante</th>
                                        <th class="no-hide">Tipo</th>
                                        <th>Subtotal</th>
                                        <th>IGV</th>
                                        <th class="no-hide">Total</th>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th class="no-hide">Persona</th>
                                        <th>Responsable</th>
                                        <th>Tipo de detalle</th>

                                        <th class="no-hide">Situación</th>
                                        <th>Moneda</th>
                                        <th>T. cambio</th>
                                        <th>Por consumo</th>
                                        <th>Tipo de pago</th>
                                        <th>Comentario</th>
                                        <th>Estado en SUNAT</th>
                                        <th>Origen</th>
                                        <th class="no-hide">Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movventas as $index => $movventa)
                                        @php
                                            $correlativo = $movventas->total() - (($movventas->currentPage() - 1) * $movventas->perPage()) - $index;
                                        @endphp
                                        <tr>

                                            <td>{{ $correlativo }}</td>
                                            <td>{{ $movventa->serie }}-{{ $movventa->movimiento->numero }}-{{ $movventa->anio }}
                                            </td>
                                            <td>{{ $movventa->movimiento->tipodocumento->nombre }}</td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($movventa->subtotal, 2) }}</td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($movventa->igv, 2) }}</td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($movventa->total, 2) }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($movventa->created_at)->format('Y-m-d') }}<br>
                                                {{ \Carbon\Carbon::parse($movventa->created_at)->format('h:i:s A') }}
                                            </td>
                                            <td>{{ $movventa->movimiento->usuario ?? 'N/A' }}</td>
                                            <td>{{ $movventa->movimiento->Persona->personamaestro->nrodoc ?? 'N/A' }} -
                                                {{ $movventa->movimiento->persona ?? 'N/A' }}
                                            </td>
                                            <td>{{ $movventa->movimiento->Responsable->personamaestro->nrodoc ?? 'N/A' }} -
                                                {{ $movventa->movimiento->responsable ?? 'N/A' }}
                                            </td>
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
                                            <td>{{ $movventa->comentario ?? '' }}</td>
                                            @php
                                                $esTicket = $movventa->movimiento->tipodocumento->nombre === 'Ticket de venta';
                                                $badgeColor = !$esTicket ? 'blue' : ($movventa->estado === 'No tributable' ? 'green' : 'blue');
                                                $badgeTexto = !$esTicket ? 'Tributable' : $movventa->estado;
                                            @endphp
                                            <td>
                                                <span class="new badge {{ $badgeColor }}"
                                                    data-badge-caption="{{ $badgeTexto }}"></span>
                                            </td>

                                                <td>{{ $movventa->movimiento->tipodocumento->tipomovimiento->descripcion ?? 'N/A' }}</td>

                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($operacion->accion === 'movventa.edit' && $movventa->movimiento->situacion == 'Activado')
                                                            <a href="{{ route($operacion->accion, $movventa->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                                data-icono="fa-solid fa-edit left"
                                                                data-titulo="{{ $operacion->descripcion }}"
                                                                data-tooltip="{{ $operacion->comentario }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @endif
                                                        @if ($operacion->accion === 'movventa.anular' && $movventa->movimiento->situacion == 'Activado')
                                                            <form action="{{ route($operacion->accion, $movventa->id) }}" method="POST"
                                                                class="anular-form" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form>
                                                        @endif
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
                                        <td colspan="3"></td> <!-- Dejar las columnas vacías anteriores -->
                                        <td class="center-align" style="font-size: 1.5rem; font-weight: bold;">
                                            {{ number_format($totalPaginaSubtotal, 2) }}
                                        </td> <!-- Total -->
                                        <td class="center-align" style="font-size: 1.5rem; font-weight: bold;">
                                            {{ number_format($totalPaginaIgv, 2) }}
                                        </td> <!-- Total -->
                                        <td class="center-align" style="font-size: 1.5rem; font-weight: bold;">
                                            {{ number_format($totalPagina, 2) }}
                                        </td> <!-- Total -->

                                        <td colspan="14"></td> <!-- Colspan para cubrir columnas restantes -->
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $movventas->firstItem() }} a {{ $movventas->lastItem() }} de
                                    {{ $movventas->total() }} registros
                                </p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $movventas->links() }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>



@endsection
