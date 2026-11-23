@extends('layouts.app')

@section('template_title')
    Reservas de Salones
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="card-content">
                        <div class="row valign-wrapper">
                            <div class="col s12 m6 left-align">
                                <div class="input-field" style="margin: 0;">
                                    <i class="material-icons prefix">search</i>
                                    <input type="text" id="search-input" class="validate" placeholder="Buscar..."
                                        data-url-base="{{ route('movreserva_paquete.index') }}">
                                </div>
                            </div>

                            <div class="col s12 m2">
                                <div class="input-field">
                                    <select id="estado-filter" name="estado" onchange="filterReservas()">
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado }}"
{{ $estadoSeleccionado == $estado ? 'selected' : '' }}>
{{ $estado }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label>Estado</label>
                                </div>
                            </div>

                            <div class="col s12 m4 right-align operaciones-crear">
                                @foreach ($operaciones as $operacion)
                                    @if ($operacion->tipo == 'Tabla')
                                        <a href="{{ route($operacion->accion) }}"
                                            class="btn waves-effect waves-light btn-cargar-crud {{ $operacion->color }}"
                                            data-icono="fa-solid fa-plus left" data-titulo="{{ $operacion->comentario }}"
                                            style="border-radius: 10px;">
                                            {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                        </a>
                                    @endif
                                @endforeach
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
                                        <th class="center">Código</th>
                                        <th class="center">Cliente</th>
                                        <th class="center">Teléfono</th>
                                        <th class="center">Correo</th>
                                        <th class="center">Fecha Reserva</th>
                                        <th class="center">Paquete</th>
                                        <th class="center">Total</th>
                                        <th class="center">Estado</th>
                                        <th class="center">Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reservas as $reserva)
                                        <tr>
                                            <td class="center">
                                                {{ str_pad($reserva->movimiento->numero ?? 0, 8, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td>
                                                {{ $reserva->movimiento->Persona->nombres ?? 'N/A' }}
                                                {{ $reserva->movimiento->Persona->apellidos ?? '' }}
                                            </td>
                                            <td class="center">{{ $reserva->celular ?? '-' }}</td>
                                            <td class="center">{{ $reserva->email ?? '-' }}</td>
                                            <td class="center">
                                                {{ \Carbon\Carbon::parse($reserva->fechavencimiento)->format('d/m/Y') }}
                                            </td>
                                            <td class="center">{{ $reserva->salon_package_name ?? '-' }}</td>
                                            <td class="center">{{ number_format($reserva->total, 2) }}</td>
                                            <td class="center">
                                                <span
                                                    class="new badge 
                                                    {{ $reserva->estado == 'PENDIENTE' ? 'yellow grey-text text-darken-3' : '' }}
                                                    {{ $reserva->estado == 'CONFIRMADO' ? 'green white-text' : '' }}
                                                    {{ $reserva->estado == 'CANCELADO' ? 'red white-text' : '' }}"
                                                    data-badge-caption="{{ $reserva->estado }}">
                                                </span>
                                            </td>
                                            <td class="center">
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($operacion->accion === 'movreserva_paquete.detalles')
                                                            <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                                data-tooltip="{{ $operacion->comentario }}"
                                                                data-titulo="Reserva de Paquete | Detalles">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @endif
                                                        @if (
                                                            $operacion->accion === 'movreserva_paquete.edit' &&
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
                                                        @if ($operacion->accion === 'movreserva-paquete.generarPDF')
                                                            <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                data-tooltip="Descargar PDF" target="_blank">
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

                            <div class="row">
                                <div class="col s12 m6">
                                    <p>Mostrando {{ $reservas->firstItem() }} a {{ $reservas->lastItem() }} de
                                        {{ $reservas->total() }} registros</p>
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
    </div>
@endsection
