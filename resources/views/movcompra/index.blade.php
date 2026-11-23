@extends('layouts.app')

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="crud-list-toolbar">
                    <div class="row without-bottom filtro" id="filtros" style="display: none; margin-bottom: 10px;">
                        <div class="col s12 m6 l2">
                            <div class="input-field" style="margin-top: 0;">
                                <select id="caja-filter" name="caja_id">
                                    <option value="">Todos</option>
                                    @foreach ($cajas as $caja)
                                        <option value="{{ $caja->id }}"
                                            {{ (string) request('caja_id') === (string) $caja->id ? 'selected' : '' }}>
                                            {{ $caja->numero }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Caja</label>
                            </div>
                        </div>

                        <div class="col s12 m6 l2">
                            <div class="input-field" style="margin-top: 0;">
                                <input type="date" id="fecha-inicio-filter" name="fecha_inicio"
                                    value="{{ request('fecha_inicio') }}">
                                <label for="fecha-inicio-filter" class="{{ request('fecha_inicio') ? 'active' : '' }}">Fecha de inicio</label>
                            </div>
                        </div>

                        <div class="col s12 m6 l2">
                            <div class="input-field" style="margin-top: 0;">
                                <input type="date" id="fecha-fin-filter" name="fecha_fin"
                                    value="{{ request('fecha_fin') }}">
                                <label for="fecha-fin-filter" class="{{ request('fecha_fin') ? 'active' : '' }}">Fecha de fin</label>
                            </div>
                        </div>

                        <div class="col s12 m6 l3">
                            <div class="input-field" style="margin-top: 0;">
                                <select id="tipo-filter" name="tipodocumento_id">
                                    <option value="">Todos</option>
                                    @foreach ($tiposDocumento as $tipoDocumento)
                                        <option value="{{ $tipoDocumento->id }}"
                                            {{ (string) request('tipodocumento_id') === (string) $tipoDocumento->id ? 'selected' : '' }}>
                                            {{ $tipoDocumento->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Tipo</label>
                            </div>
                        </div>

                        <div class="col s12 m6 l3">
                            <div class="input-field" style="margin-top: 0;">
                                <select id="conceptopago-filter" name="conceptopago_id">
                                    <option value="">Todos</option>
                                    @foreach ($conceptospagos as $conceptopago)
                                        <option value="{{ $conceptopago->id }}"
                                            {{ (string) request('conceptopago_id') === (string) $conceptopago->id ? 'selected' : '' }}>
                                            {{ $conceptopago->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Concepto de pago</label>
                            </div>
                        </div>
                    </div>

                    <div class="row without-bottom valign-wrapper" style="margin-bottom: 10px;">
                        <div class="col s12 m12 l6">
                            <div style="display: flex; align-items: flex-start; gap: 5px; width: 100%;">
                                <div class="input-field" style="flex: 1; margin-top: 0;">
                                    <input type="text" id="search-input" class="validate"
                                        value="{{ request('search', '') }}"
                                        data-url-base="{{ route('movcompra.index') }}">
                                    <label for="search-input">Buscar</label>
                                </div>
                                <a class="btn waves-effect waves-light darken-2 tooltipped btn-buscar"
                                    data-tooltip="Buscar" type="button" onclick="filterMovcompra();">
                                    <i class="fa-solid fa-magnifying-glass white-text"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col s12 m12 l6 right-align" style="margin-top: 0;">
                            @foreach ($operaciones as $operacion)
                                @if ($operacion->tipo == 'Tabla')
                                    <a href="{{ route($operacion->accion) }}"
                                        class="btn waves-effect waves-light btn-cargar-crud {{ $operacion->color }}"
                                        data-icono="fa-solid fa-plus left" data-titulo="{{ $operacion->comentario }}"
                                        style="border-radius: 10px; margin-top: 0;">
                                        {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                    </a>
                                @endif
                            @endforeach
                            <a href="{{ route('movcompra.create') }}"
                                class="btn waves-effect waves-light darken-2 btn-cargar-crud"
                                data-icono="fa-solid fa-plus left" data-titulo="Compras | Nuevo"
                                style="border-radius: 10px; margin-top: 0;">
                                <i class="fa-solid fa-plus left"></i> NUEVO
                            </a>
                        </div>
                    </div>

                    <div class="row valign-wrapper" style="margin-bottom: 10px;">
                        <div class="col s12 m8 l8 left-align valign-wrapper">
                            <span style="margin-right: 8px;">Registros por página:</span>
                            <div class="input-field" style="margin: 0;">
                                <select id="records-per-page" class="browser-default" onchange="filterMovcompra();">
                                    <option value="10" {{ (int) request('perPage', 10) === 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ (int) request('perPage', 10) === 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ (int) request('perPage', 10) === 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ (int) request('perPage', 10) === 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>

                        <div class="col s12 m4 l4 right-align"
                            style="display: flex; align-items: center; justify-content: flex-end;">
                            <button class="btn-flat btn-grid-options tooltipped" id="btn-expand-compress" type="button"
                                onclick="expandOrCompressChildRows();" data-position="top" data-tooltip="Expandir filas">
                                <i class="fa-solid fa-expand" style="color:#343a40;"></i>
                            </button>

                            <button class="btn-flat btn-grid-options tooltipped" id="btn-filtro" type="button"
                                onclick="mostrarFiltro();" data-position="top" data-tooltip="Filtros">
                                <i class="fa-solid fa-magnifying-glass-plus" style="color:#343a40;"></i>
                            </button>
                        </div>
                    </div>
                    </div>

                    <div id="tb-container" class="table-responsive">
                        <table id="data-table" class="highlight striped centered nowrap" style="width:100%;">
                            <thead class="thead-custom">
                                <tr>
                                    <th class="no-hide">#</th>
                                    <th class="no-hide">Comprobante</th>
                                    <th class="no-hide">Tipo</th>
                                    <th>Moneda</th>
                                    <th>Subtotal</th>
                                    <th>IGV</th>
                                    <th class="no-hide">Total</th>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th class="no-hide">Persona</th>
                                    <th>Concepto</th>
                                    <th>Responsable</th>
                                    <th>Tipo de detalle</th>
                                    <th>Incluye IGV</th>
                                    <th>Tipo de pago</th>
                                    <th class="no-hide">Estado</th>
                                    <th class="no-hide">Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movcompras as $index => $movcompra)
                                    @php
                                        $correlativo = $movcompras->total() - (($movcompras->currentPage() - 1) * $movcompras->perPage()) - $index;
                                        $concepto = $movcompra->movcaja?->conceptopago?->descripcion ?? ($movcompra->movimiento->comentario ?: 'Pago a proveedor');
                                    @endphp
                                    <tr>
                                        <td>{{ $correlativo }}</td>
                                        <td>{{ $movcompra->serie }}-{{ $movcompra->movimiento->numero }}-{{ $movcompra->anio }}</td>
                                        <td>{{ $movcompra->movimiento->tipodocumento->nombre ?? 'N/A' }}</td>
                                        <td>{{ $movcompra->moneda }}</td>
                                        <td class="inpt-cantidad-mc">{{ number_format($movcompra->subtotal, 2) }}</td>
                                        <td class="inpt-cantidad-mc">{{ number_format($movcompra->igv, 2) }}</td>
                                        <td class="inpt-cantidad-mc">{{ number_format($movcompra->total, 2) }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($movcompra->movimiento->fecha)->format('Y-m-d') }}<br>
                                            {{ \Carbon\Carbon::parse($movcompra->created_at)->format('h:i:s A') }}
                                        </td>
                                        <td>{{ $movcompra->movimiento->usuario ?? 'N/A' }}</td>
                                        <td>
                                            {{ $movcompra->movimiento->Persona->personamaestro->nrodoc ?? '0' }} -
                                            {{ $movcompra->movimiento->persona ?? 'N/A' }}
                                        </td>
                                        <td>{{ $concepto }}</td>
                                        <td>
                                            {{ $movcompra->movimiento->Responsable->personamaestro->nrodoc ?? '0' }} -
                                            {{ $movcompra->movimiento->responsable ?? 'N/A' }}
                                        </td>
                                        <td>{{ $movcompra->tipo_detalle_texto }}</td>
                                        <td>{{ $movcompra->incluye_igv_texto }}</td>
                                        <td>{{ $movcompra->tipo_pago_texto }}</td>
                                        <td>
                                            <span class="new badge {{ $movcompra->movimiento->situacion == 'Activado' ? 'green' : 'red' }}"
                                                data-badge-caption="{{ $movcompra->movimiento->situacion }}"></span>
                                        </td>
                                        <td>
                                            @if ($movcompra->movimiento->situacion == 'Activado')
                                                <a href="{{ route('movcompra.edit', $movcompra->id) }}"
                                                    class="btn-floating yellow darken-2 waves-light tooltipped btn-cargar-crud"
                                                    data-icono="fa-solid fa-edit left"
                                                    data-titulo="Compras | Editar"
                                                    data-tooltip="Editar">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <form action="{{ route('movcompra.anular', $movcompra->id) }}" method="POST"
                                                    class="anular-form" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn-floating red darken-1 waves-light tooltipped"
                                                        data-tooltip="Anular">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('movcompra.verDetalles', $movcompra->id) }}"
                                                class="btn-floating blue darken-1 waves-light tooltipped btn-cargar-crud"
                                                data-icono="fa-solid fa-list left"
                                                data-titulo="Compras | Detalles"
                                                data-tooltip="Ver detalles">
                                                <i class="fa-solid fa-list"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="right-align" style="font-weight: bold;">Totales (página)</td>
                                    <td class="inpt-cantidad-mc" style="font-weight: bold;">{{ number_format($totalPaginaSubtotal, 2) }}</td>
                                    <td class="inpt-cantidad-mc" style="font-weight: bold;">{{ number_format($totalPaginaIgv, 2) }}</td>
                                    <td class="inpt-cantidad-mc" style="font-weight: bold;">{{ number_format($totalPagina, 2) }}</td>
                                    <td colspan="10"></td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $movcompras->firstItem() ?? 0 }} a {{ $movcompras->lastItem() ?? 0 }} de
                                    {{ $movcompras->total() }} registros.</p>
                            </div>
                            <div class="col s12 m6 right-align">
                                {{ $movcompras->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
