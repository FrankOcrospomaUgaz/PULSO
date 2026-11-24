@extends('layouts.app')

@section('template_title')
    Solicitudes
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
          <!-- 🔹 Filtros superiores -->
<div class="row" id="filtros" style="margin-bottom: 10px; display: flex; flex-wrap: wrap;">
    <!-- Buscador + Botón Buscar -->
    <div class="col s12 m4 left-align order-mobile-2 order-desktop-1">
        <div style="display: flex; align-items: flex-start; gap: 5px; width: 100%;">
            <!-- Input con icono -->
            <div class="input-field" style="flex: 1; margin-top: 0;">
                <i class="material-icons prefix">search</i>
                <input type="text" id="search-input" class="validate"
                       data-url-base="{{ route('solicitud.index') }}">
                <label for="search-input">Buscar</label>
            </div>
            <!-- Botón Buscar -->
            <a class="btn waves-effect waves-light darken-2 tooltipped btn-buscar"
               data-tooltip="Buscar" type="button" onclick="realizarBusqueda();">
               <i class="fa-solid fa-magnifying-glass white-text"></i>
            </a>
        </div>
    </div>

    <!-- Botones NUEVO / ESPECIALES -->
    <div class="col s12 m8 right-align order-mobile-1 order-desktop-2">
        @foreach ($operaciones as $operacion)
            @if ($operacion->tipo == 'Tabla')
                @if (
                    $operacion->accion === 'comprobante.sync' or
                    $operacion->accion === 'comprobante.send_pending_boletas' or
                    $operacion->accion === 'comprobante.send_pending_facturas'
                )
                    <a href="{{ route($operacion->accion) }}"
                       class="btn waves-effect waves-light {{ $operacion->color }} btn-activar"
                       style="border-radius: 10px; margin-right:10px; margin-bottom:10px;">
                       {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                    </a>
                @else
                    <a href="{{ route($operacion->accion) }}"
                       class="btn waves-effect waves-light btn-crear {{ $operacion->color }}"
                       style="border-radius: 10px; margin-right:10px; margin-bottom:10px;">
                       {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                    </a>
                @endif
            @endif
        @endforeach
    </div>
</div>

<!-- 🔹 Configuración de registros por página y botones de acción -->
<div class="row valign-wrapper" style="margin-bottom: 10px;">
    <!-- Selector de registros -->
    <div class="col s12 m8 l8 left-align valign-wrapper" style="padding-left:13px;">
        <span style="margin-right: 8px;">Registros por página:</span>
        <div class="input-field" style="margin: 0;">
            <select id="records-per-page" class="browser-default" onchange="realizarBusqueda()">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    <!-- Botones de expansión y filtros -->
    <div class="col s12 m4 l4 right-align"
         style="display: flex; align-items: center; justify-content: flex-end; padding-right:0;">
        <button class="btn-flat btn-grid-options tooltipped" id="btn-expand-compress"
                type="button" onclick="expandOrCompressChildRows();"
                data-position="top" data-tooltip="Expandir filas">
            <i class="fa-solid fa-expand" style="color:#343a40;"></i>
        </button>
        <button class="btn-flat btn-grid-options tooltipped" id="btn-filtro"
                type="button" onclick="mostrarFiltro();"
                data-position="top" data-tooltip="Filtros">
            <i class="fa-solid fa-magnifying-glass-minus" style="color:#343a40;"></i>
        </button>
    </div>
</div>

               

                    <!-- Tabla de datos -->
                    <div id="tb-container" class="table-responsive">
                        <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                            <thead class="thead-custom">
                                <tr>
                                    <th class="center">Tipo</th>
                                    <th class="center">Número</th>
                                    <th class="center">Nro. de documento</th>
                                    <th class="center">Persona</th>
                                    <th class="center">Moneda</th>
                                    <th class="center">Total</th>
                                    <th class="center">Estado</th>
                                    <th class="center">Fecha de solicitud</th>
                                    <th class="center">F. de envío</th>
                                    <th class="center">F. de respuesta</th>
                                    <th class="center">F. de anulación</th>
                                    <th class="center">RUC de facturador</th>
                                    <th class="center">Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($solicituds as $solicitud)
                                    <tr>
                                        @php
                                            $tipoDocumentoVista = $solicitud->tipo_documento;
                                            if ($tipoDocumentoVista === 'N') {
                                                $dataSolicitud = json_decode($solicitud->data, true) ?: [];
                                                $tipoDocumentoVista = data_get($dataSolicitud, 'documento_afectado.tipo_documento_pulso', 'N');
                                            }
                                        @endphp
                                        <td class="center">{{ ['B' => 'Boleta', 'F' => 'Factura', 'N' => 'Nota de crédito'][$solicitud->tipo_documento] ?? 'Comprobante' }}
                                        </td>

                                        <td class="center">{{ $tipoDocumentoVista . $solicitud->serie . '-' . str_pad((string) $solicitud->correlativo, 8, '0', STR_PAD_LEFT) }}</td>
                                        <td class="center">{{ $solicitud->persona?->personamaestro?->nrodoc ?? '—' }}</td>
                                        <td class="center">{{ $solicitud->movimiento?->persona ?? '—' }}</td>
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

                                        <td class="center">
                                            @foreach ($operaciones as $operacion)
                                                @if ($operacion->tipo == 'Registro')
                                                    @if ($solicitud->tipo_documento === 'N' && $operacion->accion === 'comprobante.generarPDF')
                                                        @continue
                                                    @endif
                                                    @if ($operacion->accion === 'comprobante.generarPDF')
                                                        <a href="{{ route($operacion->accion, $solicitud->movimiento->movventa->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                            data-icono="fa-solid fa-edit left"
                                                            data-titulo="{{ $operacion->descripcion }}"
                                                            data-tooltip="{{ $operacion->comentario }}" target="_blank">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @else
                                                        <a href="{{ route($operacion->accion, $solicitud->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear"
                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                @endif
                                            @endforeach

                                            @if (in_array($solicitud->tipo_documento, ['B', 'F'], true) && in_array($solicitud->estado, ['M', 'T'], true))
                                                <a href="{{ route('solicitud.notaCredito.create', $solicitud->id) }}" class="btn-floating amber darken-2 waves-effect waves-light tooltipped" data-tooltip="Emitir nota de crédito">
                                                    <i class="fa-solid fa-file-circle-minus"></i>
                                                </a>
                                            @elseif ($solicitud->tipo_documento === 'N')
                                                <a href="{{ route('solicitud.downloadXml', $solicitud->id) }}" class="btn-floating blue darken-2 waves-effect waves-light tooltipped" data-tooltip="Descargar XML">
                                                    <i class="fa-solid fa-file-code"></i>
                                                </a>
                                                @if ($solicitud->nombre)
                                                    <a href="{{ route('solicitud.downloadCdr', $solicitud->id) }}" class="btn-floating green darken-2 waves-effect waves-light tooltipped" data-tooltip="Descargar CDR">
                                                        <i class="fa-solid fa-file-circle-check"></i>
                                                    </a>
                                                @endif
                                            @endif

                                            <a href="#!"
                                                 class="btn-floating blue darken-1 waves-light tooltipped btn-ver-respuesta-sunat"
                                                data-tooltip="Ver respuesta SUNAT"
                                                data-tipo="{{ $solicitud->tipo_documento == 'B' ? 'BOLETA' : 'FACTURA' }}"
                                                data-numero="{{ $solicitud->serie . '-' . str_pad($solicitud->correlativo, 8, '0', STR_PAD_LEFT) }}"
                                                data-nombre="{{ $solicitud->nombre ?? '' }}"
                                                data-respuesta="{{ base64_encode((string) ($solicitud->error_text ?? '')) }}">
                                                <i class="fa-solid fa-circle-info"></i>
                                            </a>

                                            @if (!empty($solicitud->nombre))
                                                <button class="btn-floating  tooltipped orange" id="operacion4-{{ $solicitud->id }}" data-tooltip="XML" type="button" onclick="generarXML('{{ $solicitud->id }}');"><i class="fa-solid fa-file-arrow-down white-text"></i></button>
                                                <button class="btn-floating  tooltipped purple darken-1" id="operacion5-{{ $solicitud->id }}" data-tooltip="CDR" type="button" onclick="generarCDR('{{ $solicitud->id }}');"><i class="fa-solid fa-file-circle-check white-text"></i></button>
                                            @endif

                                            <!-- Mostrar botón solo si el estado es 'Aún no declarado' -->
                                            @if ($solicitud->estado == 'R')
                                   
                                                    <a href="{{ route( 'solicitud.cambiarEstado', $solicitud->id) }}"
                                                   class="btn-floating red darken-1 waves-light tooltipped btn-activar"     data-tooltip="Cambiar estado">
                                                   <i class="fa fa-sync-alt"></i> 
                                                </a>
                                            
                                            @endif
                                </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>


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

    <div id="modal-respuesta-sunat" class="modal modal-fixed-footer">
        <div class="modal-content">
            <div class="row without-top">
                <p class="grey-text text-darken-3 modal-title without-tb center">
                    <i class="fa-solid fa-receipt"></i> Respuesta SUNAT
                </p>
            </div>

            <div class="row">
                <div class="col s12 m6">
                    <p><strong>TIPO:</strong> <span id="sunat-respuesta-tipo">-</span></p>
                </div>
                <div class="col s12 m6">
                    <p><strong>NÚMERO:</strong> <span id="sunat-respuesta-numero">-</span></p>
                </div>
            </div>

            <div class="row">
                <div class="col s12">
                    <table class="highlight striped centered">
                        <thead class="thead-custom">
                            <tr>
                                <th class="center">Nombre</th>
                                <th class="center">Respuesta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td id="sunat-respuesta-nombre">-</td>
                                <td id="sunat-respuesta-texto">Sin respuesta registrada.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>
@endsection
