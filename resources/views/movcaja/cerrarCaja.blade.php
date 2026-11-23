@extends('layouts.app')
@section('content')
    <div class="main-body">
        <div class="row">
            <div class="card main-content">

                <div class="card-body bg-white">
                    <form id="form" class="form-retroceder" enctype="multipart/form-data">

                        @csrf
                        <input type="hidden" id="form-action-url" value="{{ route('movcaja.storeCierre', $cajaId) }}">

                        <!-- Campo oculto para la operación -->
                        <input type="hidden" name="operacion" value="CERRAR">
                        <input type="hidden" name="detalles_movimientos_json" id="detalles_movimientos_json"
                            value='@json($detallesAgrupados)'>

                        <div class="row padding-1 p-1">
                            <div class="col s12 m6">
                                <div class="row">
                                    <!-- Persona -->
                                    <div class="input-field col s12 m6 persona-wrapper">

                                        <div class="persona-flex">
                                            <input type="text" id="persona_autocomplete"
                                                class="autocomplete persona-input" placeholder="Seleccionar Persona"
                                                value="0 - CLIENTES VARIOS" disabled>

                                            <button class="btn red tooltipped persona-btn" id="btnQuitarCliente"
                                                data-tooltip="Deseleccionar persona" type="button"
                                                onclick="eliminarPersona();" style="border-radius:99em">
                                                <i class="fa-solid fa-user-slash"></i>
                                            </button>
                                            <button
                                                href="{{ route('persona.create', [$sucursal->id, 'VENTA', $habitacion->id ?? null]) }}"
                                                class="btn teal right tooltipped btn-crear persona-btn" id="btnNuevaPersona"
                                                data-tooltip="Nueva persona" type="button"
                                                style="display:none; border-radius:99em">
                                                <i class="fa-solid fa-user-plus"></i>
                                            </button>
                                        </div>



                                        <input type="hidden" name="persona_id" value="26" id="persona_id">
                                        <label for="persona_autocomplete" class="active">{{ __('Persona') }}</label>
                                        <span class="helper-text" id="persona_id_spn" data-error="" data-success=""></span>
                                    </div>

                                    <!-- Responsable -->
                                    <div class="input-field col s12 m6">
                                        <i class="fa-solid fa-user-tie prefix"></i>
                                        <select name="responsable_id" id="responsable_id">
                                            @foreach ($responsables as $responsable)
                                                <option value="{{ $responsable->id }}"
{{ old('responsable_id', Auth::user()->persona_id) == $responsable->id ? 'selected' : '' }}>
{{ $responsable->personamaestro->nrodoc }} - {{ $responsable->apellidos }} {{ $responsable->nombres }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="responsable_id">{{ __('Responsable') }}</label>
                                        <span class="helper-text" id="responsable_id_spn" data-error="" data-success=""></span>
                                    </div>

                                    <!-- Caja -->
                                    <div class="input-field col s12 m3">
                                        <div class="select-wrapper">
                                            <select name="caja_id" id="caja_id" class="validate">
                                                @foreach ($cajas as $caja)
                                                    <option value="{{ $caja->id }}"
{{ old('caja_id') == $caja->id ? 'selected' : '' }}>
{{ $caja->numero }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <label for="caja_id">{{ __('Caja') }}</label>
                                        <span class="helper-text" id="caja_id_spn" data-error="" data-success=""></span>
                                    </div>


                                    <!-- Total -->
                                    <div class="input-field col s12 m3">
                                        <input type="number" step="0.01" name="total" id="total"
                                            value="{{ $totales['total'] }}" readonly>
                                        <label for="total" class="active">{{ __('Total') }}</label>
                                    </div>
                                    <div class="input-field col s12 m6 l3">
                                        <input autocomplete="off" class="inpt-cantidad" id="totalpagado" name="totalpagado"
                                            type="number" value="{{ $totales['total'] }}" readonly>
                                        <label class="active" for="totalpagado">Pagado</label>
                                        <span class="helper-text" id="totalpagado_spn" data-error=""
                                            data-success=""></span>
                                    </div>
                                    <div class="input-field col s12 m4">
                                        <label>
                                            <input type="radio" name="moneda" value="PEN" checked>
                                            <span>S/.</span>
                                        </label>
                                        <label style="margin-left: 100px;">
                                            <input type="radio" name="moneda" value="USD">
                                            <span>$</span>
                                        </label>
                                    </div>
                                    {{-- Notas --}}
                                    <div class="input-field col s12 m8">
                                        <i class="fa-solid fa-note-sticky prefix"></i>
                                        <textarea name="notas" id="notas" class="materialize-textarea validate @error('notas') invalid @enderror"></textarea>
                                        <label for="notas">{{ __('Notas al movimiento de caja') }}</label>
                                        {!! $errors->first('notas', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                                    </div>

                                </div>

                                <div class="row padding-1 p-1">
                                    <!-- Botón y tabla de Monedas -->
                                    <div class="input-field col s12 m6 l6 center">
                                        <button id="btn-monedas" style="width:100%; margin-bottom: 15px"
                                            class="btn white-text truncate bg-primary" type="button"
                                            onclick="toggleMoney('moneda')">
                                            <i class="fa-solid fa-coins right"></i>Monedas
                                        </button>
                                        <table class="bordered display responsive nowrap data-table"
                                            id="dinero-monedaTable">
                                            <thead class="thead-custom">
                                                <tr>
                                                    <th class="center" width="30%">Dinero</th>
                                                    <th class="center" width="30%">Cant.</th>
                                                    <th class="center" width="40%">Notas</th>
                                                </tr>
                                            </thead>
                                            <tbody id="dinero-monedaBody">
                                                @foreach ($monedas as $index => $moneda)
                                                    <tr id="filaDinero{{ $moneda->id }}">
                                                        <td class="center">{{ $moneda->descripcion }}</td>
                                                        <td class="center">
                                                            <input type="hidden" id="dinero_id{{ $moneda->id }}"
                                                                name="dinero_id[]" value="{{ $moneda->id }}">
                                                            <input autocomplete="off"
                                                                class="inpt-cantidad without-bottom dinero"
                                                                id="dinero_cantidad{{ $moneda->id }}"
                                                                name="dinero_cantidad[]" type="number" value="0"
                                                                oninput="validateDecimalNumbers(this);calcularDinero()"
                                                                money-value="{{ number_format($moneda->valor, 2) }}"
                                                                inputmode="numeric"
                                                                onchange="validateEmptyNumber(this);calcularDinero()">
                                                        </td>
                                                        <td class="center">
                                                            <div class="col s12">
                                                                <textarea class="materialize-textarea without-bottom" id="dinero_comentario{{ $moneda->id }}"
                                                                    name="dinero_comentario[]" rows="1" oninput="autoResize(this)"></textarea>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                        </table>
                                    </div>

                                    <!-- Botón y tabla de Billetes -->
                                    <div class="input-field col s12 m6 l6 center">
                                        <button id="btn-billetes" style="width:100%; margin-bottom: 15px"
                                            class="btn white-text truncate bg-primary" type="button"
                                            onclick="toggleMoney('billete')">
                                            <i class="fa-solid fa-money-bill right"></i>Billetes
                                        </button>
                                        <table class="bordered display responsive nowrap data-table"
                                            id="dinero-billeteTable">
                                            <thead class="thead-custom">
                                                <tr>
                                                    <th class="center" width="30%">Dinero</th>
                                                    <th class="center" width="30%">Cant.</th>
                                                    <th class="center" width="40%">Notas</th>
                                                </tr>
                                            </thead>
                                            <tbody id="dinero-billeteBody">
                                                @foreach ($billetes as $index => $billete)
                                                    <tr id="filaDinero{{ $billete->id }}">
                                                        <td class="center">{{ $billete->descripcion }}</td>
                                                        <td class="center">

                                                            <input type="hidden" id="dinero_id{{ $billete->id }}"
                                                                name="dinero_id[]" value="{{ $billete->id }}">
                                                            <input autocomplete="off"
                                                                class="inpt-cantidad without-bottom dinero"
                                                                id="dinero_cantidad{{ $billete->id }}"
                                                                name="dinero_cantidad[]" type="number" value="0"
                                                                oninput="validateDecimalNumbers(this);calcularDinero()"
                                                                money-value="{{ number_format($billete->valor, 2) }}"
                                                                inputmode="numeric"
                                                                onchange="validateEmptyNumber(this);calcularDinero()">
                                                        </td>
                                                        <td class="center">
                                                            <div class="col s12">
                                                                <textarea class="materialize-textarea without-bottom" id="dinero_comentario{{ $billete->id }}"
                                                                    name="dinero_comentario[]" rows="1" oninput="autoResize(this)"></textarea>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                        </table>
                                    </div>

                                    <!-- Total Dinero -->
                                    <div class="input-field col s12 m6 l3">
                                        <input autocomplete="off" class="inpt-cantidad" id="dinero" type="number"
                                            value="0" readonly>
                                        <label class="active" for="dinero">Dinero</label>
                                        <span class="helper-text" id="dinero_spn" data-error="" data-success=""></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Detalle de cierre -->
                            <div class="col s12 m6">
                                <div class="row">

                                    <h5 class="semibold center">Detalle de cierre</h5>

                                    <div class="col s12">
                                        <div class="row without-tb">
                                            <div class="input-field col s3">
                                                <input type="number" step="0.01" name="ventas_efectivo"
                                                    id="ventas_efectivo"
                                                    value="{{ old('ventas_efectivo', $totales['ventas_efectivo']) }}"readonly>
                                                <label for="ventas_efectivo" class="active">Ventas en efectivo</label>
                                                <span class="helper-text">Pagos de clientes</span>

                                            </div>
                                            <div class="input-field col s3">
                                                <input type="number" step="0.01" name="apertura" id="apertura"
                                                    value="{{ old('apertura', $totales['apertura']) }}"readonly>
                                                <label for="apertura" class="active">Apertura en Efectivo</label>
                                                <span class="helper-text">Apertura</span>

                                            </div>
                                            <div class="input-field col s3">
                                                <input type="number" step="0.01" name="ingresos_efectivo"
                                                    id="ingresos_efectivo"
                                                    value="{{ old('ingresos_efectivo', $totales['ingresos_efectivo'] - $totales['apertura']) }}"readonly>
                                                <label for="ingresos_efectivo" class="active">Ingresos en efectivo</label>
                                                <span class="helper-text">Otros ingresos

                                            </div>
                                            <div class="input-field col s3">
                                                <input type="number" step="0.01" name="egresos_efectivo"
                                                    id="egresos_efectivo"
                                                    value="{{ old('egresos_efectivo', -$totales['egresos_efectivo']) }}"readonly>
                                                <label for="egresos_efectivo" class="active">Egresos en efectivo</label>
                                                <span class="helper-text">Gastos</span>

                                            </div>
                                  
                                            <div class="input-field col s12 m6 l4">
                                                <i class="fa-solid fa-money-bill prefix"></i>
                                                <input autocomplete="off" class="inpt-cantidad" id="efectivo_real"
                                                    name="efectivo_real" type="number" value="0">
                                                <label class="active" for="efectivo_real">Efectivo real</label>
                                                <span class="helper-text" id="efectivo_real_spn" data-error=""
                                                    data-success=""></span>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <i class="fa-solid fa-piggy-bank prefix"></i>
                                                <input autocomplete="off" class="inpt-cantidad" id="efectivo_caja"
                                                    type="number"
                                                    value="{{ old('efectivo_caja', $totales['efectivo_caja']) }}"
                                                    readonly>
                                                <label class="active" for="efectivo_caja">Efectivo en caja</label>
                                                <span class="helper-text" id="efectivo_caja_spn" data-error=""
                                                    data-success="">EC = P + A + O - G</span>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <i class="fa-solid fa-hand-holding-dollar prefix"></i>
                                                <input autocomplete="off" class="inpt-cantidad"
                                                    id="efectivo_caja_sin_apertura" type="number"
                                                    value="{{ $totales['efectivo_caja'] - $totales['apertura'] }}"
                                                    readonly>
                                                <label class="active" for="efectivo_caja_sin_apertura">Efectivo sin
                                                    apertura</label>
                                                <span class="helper-text" id="efectivo_caja_sin_apertura_spn"
                                                    data-error="" data-success="">P + O - G</span>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Tabla Dinámica para Detalle de Cierre -->
                                    <div class="table-responsive col s12 m12 l12 mb10">
                                        <table class="highlight striped centered" id="data-table">
                                            <thead class="thead-custom">
                                                <tr style="color: white">
                                                    <th>Tipo</th>
                                                    <th>Monto</th>

                                                    <th>Medio</th>
                                                    <th>Detalles</th>

                                                    <th>Notas</th>
                                                    <th></th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($detallesAgrupados as $detalle)
                                                    <tr>
                                                        <!-- Mostrar el valor de 'tipo' con un badge -->
                                                        <td>
                                                            @php
                                                                $colorBadge =
                                                                    ($detalle['tipo'] ?? '') === 'Pagado'
                                                                        ? 'green'
                                                                        : (($detalle['tipo'] ?? '') === 'Deuda'
                                                                            ? 'red'
                                                                            : 'grey');
                                                            @endphp
                                                            <span class="new badge {{ $colorBadge }} white-text"
                                                                data-badge-caption="{{ $detalle['tipo'] ?? 'N/A' }}">
                                                            </span>
                                                        </td>

                                                        <!-- Monto formateado -->
                                                        <td style="font-weight: 700; font-size: 1.5rem !important;">
                                                            {{ $detalle['nota'] == 'Egresos' ? '-' : '' }}{{ $detalle['monto'] }}
                                                        </td>

                                                        <!-- Medio de pago -->
                                                        <td>{{ $detalle['mediopago'] ?? 'N/A' }}</td>
                                                        <td>{{ $detalle['detalles'] ?? 'N/A' }}</td>

                                                        <!-- Nota con badge de color según el valor -->
                                                        <td>
                                                            <span
                                                                class="new badge 
                                                            {{ $detalle['nota'] == 'Apertura de caja'
                                                                ? 'yellow black-text'
                                                                : ($detalle['nota'] == 'Egresos'
                                                                    ? 'red'
                                                                    : ($detalle['nota'] == 'Pago de cliente'
                                                                        ? 'purple'
                                                                        : 'blue')) }}"
                                                                data-badge-caption="{{ $detalle['nota'] == 'Pago de cliente' ? 'Pago de cliente' : $detalle['nota'] ?? 'N/A' }}">
                                                            </span>

                                                        </td>
                                                        <td class="center">
                                                            <a href="{{ route('verDetalleMovimiento', [
                                                                'movcaja_ids' => implode(',', $detalle['movcaja_ids']),
                                                                'nota' => $detalle['nota'],
                                                                'mediopago' => $detalle['mediopago'],
                                                            ]) }}"
                                                                class="btn-floating btn-small tooltipped blue btn-crear"
                                                                data-tooltip="Ver movimientos" type="button">
                                                                <i class="fa-solid fa-eye white-text"></i>
                                                            </a>

                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>


                                        </table>
                                    </div>

                                    <div class="col s3">
                                        <div class="input-field">
                                            <input type="number" step="0.01" name="total_ventas" id="total_ventas"
                                                value="{{ old('total_ventas', $totales['ventas']) }}" readonly>
                                            <label for="total_ventas" class="active">Total de ventas:</label>
                                            <span class="helper-text">Pagos de clientes</span>

                                        </div>
                                    </div>
                                    <div class="col s3">
                                        <div class="input-field">
                                            <input type="number" step="0.01" name="total_ingresos"
                                                id="total_ingresos"
                                                value="{{ old('total_ingresos', $totales['ingresos']) }}" readonly>
                                            <label for="total_ingresos" class="active">Total de ingresos:</label>
                                            <span class="helper-text">Otros ingresos</span>

                                        </div>
                                    </div>
                                    <div class="col s3">
                                        <div class="input-field">
                                            <input type="number" step="0.01" name="total_egresos" id="total_egresos"
                                                value="{{ old('total_egresos', -$totales['egresos']) }}" readonly>
                                            <label for="total_egresos" class="active">Total de egresos:</label>
                                            <span class="helper-text">Gastos</span>

                                        </div>
                                    </div>
                                    <div class="col s3">
                                        <div class="input-field">

                                            <button id="btn-print-movcaja"
                                                class="btn btn-floating purple tooltipped mt20 ml10 truncate right"
                                                data-tooltip="Ticket" type="button"
                                                onclick="window.open('{{ route('movcaja.generarPdfCierreTicket', ['cajaId' => $caja->id]) }}', '_blank')"><i
                                                    class="fa-solid fa-print white-text left"></i></button>
                                            <button id="btn-pdf-movcaja"
                                                class="btn btn-floating red tooltipped mt20 ml10 truncate right"
                                                data-tooltip="PDF" type="button"
                                                onclick="window.open('{{ route('movcaja.generarPdfCierre', ['cajaId' => $caja->id]) }}', '_blank')">
                                                <i class="fa-solid fa-file-pdf white-text left"></i>
                                            </button>

                                        </div>
                                    </div>


                                </div>

                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="row">
                            <div class="col s12">
                                <div class="right-align">
                                    <button type="submit" class="btn darken-2"><i
                                            class="material-icons left">save</i>Guardar</button>
                                    <a href="{{ route('movcaja.index') }}"
                                        class="modal-close waves-effect darken-2 btn-flat btn-cargar-crud"
                                        data-icono="fa fa-solid fa-cash-register left"
                                        data-titulo="Caja chica">{{ __('Cancelar') }} <i
                                            class="material-icons right">clear</i></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
