@extends('layouts.app')

@section('template_title')
    {{ __('Sucursales') }}
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">

                    <div class="row" id="filtros" style="margin-bottom: 10px; display: flex; flex-wrap: wrap;">
                        <div class="col s12 m6 left-align order-mobile-2 order-desktop-1">
                            <div style="display: flex; align-items: flex-start; gap: 5px; width: 100%;">
                                <div class="input-field" style="flex: 1; margin-top: 0;">
                                    <input type="text" id="search-input" class="validate"
                                        data-url-base="{{ route('verMisSucursales', $empresa_id) }}">
                                    <label for="search-input">Buscar</label>
                                </div>

                                <a class="btn waves-effect waves-light darken-2 tooltipped btn-buscar" data-tooltip="Buscar"
                                    type="button" onclick="realizarBusqueda();">
                                    <i class="fa-solid fa-magnifying-glass white-text"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col s12 m6 right-align order-mobile-1 order-desktop-2"
                            style="margin-top:0; padding-top:0;">
                            @foreach ($operaciones as $operacion)
                                @if ($operacion->tipo == 'Tabla')
                                    <a href="{{ route('sucursal.create', $empresa_id) }}"
                                        class="btn waves-effect waves-light btn-crear {{ $operacion->color }}"
                                        style="border-radius: 10px; margin-top:0;">
                                        {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="row valign-wrapper" style="margin-bottom: 10px;">
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
                        <div class="table-scroll" style="overflow-x: auto;">
                            <table id="data-table" class="highlight striped centered"
                                style="width: max-content; min-width: 100%;">
                                <thead class="thead-custom">
                                    <tr>
                                        <th class="no-hide">Nombre Comercial</th>
                                        <th class="no-hide">RUC</th>
                                        <th>Dirección</th>
                                        <th>Ubigeo</th>
                                        <th class="no-hide">Logo</th>
                                        <th class="no-hide">Situación</th>
                                        <th>Email</th>
                                        <th>Facturación</th>
                                        <th>Razón Social</th>
                                        <th>Tipo</th>
                                        <th>Teléfono Fijo</th>
                                        <th>Teléfono Móvil</th>
                                        <th class="no-hide">Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sucursales as $sucursal)
                                        <tr>
                                            <td>{{ $sucursal->nombrecomercial }}</td>
                                            <td>{{ $sucursal->ruc }}</td>
                                            <td>{{ $sucursal->direccion }}</td>
                                            <td>{{ $sucursal->ubigeo->descripcion }}</td>
                                            <td><img src="{{ $sucursal->logo }}" alt="Logo" style="width: 50px; height: auto;">
                                            </td>
                                            <td>
                                                <span
                                                    class="new badge {{ $sucursal->situacion == 'Activado' ? 'green' : 'red' }}"
                                                    data-badge-caption="{{ $sucursal->situacion }}"></span>
                                            </td>
                                            <td>{{ $sucursal->email }}</td>
                                            <td>{{ $sucursal->facturacion }}</td>
                                            <td>{{ $sucursal->razonsocial }}</td>
                                            <td>{{ $sucursal->tipo }}</td>
                                            <td>{{ $sucursal->telefonofijo }}</td>
                                            <td>{{ $sucursal->telefonomovil }}</td>
                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($operacion->accion === 'sucursal.edit')
                                                            <a href="{{ route($operacion->accion, $sucursal->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-crear"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @elseif ($operacion->accion === 'sucursal.verPersonal' || $operacion->accion === 'sucursal.verPerfiles')
                                                            <a href="{{ route($operacion->accion, $sucursal->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                                data-tooltip="{{ $operacion->descripcion }}"
                                                                data-icono="{{ $operacion->imagen }}"
                                                                data-titulo="{{ $sucursal->razonsocial . ' - ' . $operacion->comentario }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $sucursales->firstItem() }} a {{ $sucursales->lastItem() }} de
                                    {{ $sucursales->total() }} registros
                                </p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $sucursales->links() }}
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection
