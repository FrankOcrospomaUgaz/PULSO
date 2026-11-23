<div class="row without-bottom">
    <div class="col s12 m12 l8">
        <div class="row  without-bottom ">

        
        @if ($operacion == 'APERTURAR')
            <!-- Campo oculto para enviar 'conceptopago_id' cuando la operación es APERTURA -->
            <input type="hidden" name="tipo_documento_visible" value="7">
            <input type="hidden" name="fecha_visible" value="{{ now()->format('d/m/Y') }}">
        @else
            <!-- Select para 'tipo de movimiento', habilitado o deshabilitado según la operación -->
            <div class="input-field col s12 m3">
<select name="tipo_documento_visible" id="tipo_documento"
    class="validate @error('tipo_documento') invalid @enderror"
    {{ in_array($operacion, ['INGRESO', 'EGRESO']) ? 'disabled' : '' }}
    onchange="cargarConceptosPago(this.options[this.selectedIndex].text)">
    
    @foreach ($tipos_documento as $tipo_documento)
        <option value="{{ $tipo_documento->id }}"
            {{ ($operacion == 'INGRESO' && $tipo_documento->id == 7) 
            || ($operacion == 'EGRESO' && $tipo_documento->id == 8) 
            || old('tipo_documento', $movcaja->movimiento->tipodocumento_id ?? '') == $tipo_documento->id 
                ? 'selected' : '' }}>
{{ $tipo_documento->nombre }}
        </option> 
    @endforeach
</select>


                <label for="tipo_documento">{{ __('Tipo') }}</label>
                {!! $errors->first(
                    'tipo_documento',
                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                ) !!}
            </div>

            <!-- Campo oculto para enviar el valor del select si está deshabilitado -->
            @if (in_array($operacion, ['INGRESO', 'EGRESO']))
                <input type="hidden" name="tipo_documento_visible"
                    value="{{ $operacion == 'INGRESO' ? 7 : ($operacion == 'EGRESO' ? 8 : '') }}">
            @endif
            <!-- Campo de fecha para 'fecha' deshabilitado en el formulario -->
            <div class="input-field col s12 m3">
                <i class="fa-solid fa-calendar-day prefix"></i>
                <input type="text" class="datepicker" name="fecha_visible" id="fecha"
                    value="{{ old('fecha', optional($movcaja->movimiento->fecha ?? '')->format('d/m/Y') ?? now()->format('d/m/Y')) }}"
                    disabled>
                <label for="fecha" class="active">{{ __('Fecha') }}</label>
                {!! $errors->first('fecha', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}

                @if (old('fecha'))
                    <span class="helper-text">{{ \Carbon\Carbon::parse(old('fecha'))->format('h:i:s A') }}</span>
                @elseif(isset($movcaja->movimiento->fecha))
                    <span
                        class="helper-text">{{ \Carbon\Carbon::parse($movcaja->movimiento->fecha)->format('h:i:s A') }}</span>
                @endif
            </div>

        @endif




        <!-- Campo oculto para enviar la fecha -->
        <input type="hidden" name="fecha" id="fecha_oculta"
            value="{{ old('fecha', optional($movcaja->movimiento->fecha ?? '')->format('Y-m-d') ?? now()->format('Y-m-d')) }}">


        @if ($operacion == 'APERTURAR')
            <!-- Campo oculto para enviar 'conceptopago_id' cuando la operación es APERTURA -->
            <input type="hidden" name="conceptopago_id" value="2">
        @else
            <!-- Select para 'concepto de pago' -->
<div class="input-field col s12 m6">
    <select name="conceptopago_id" id="conceptopago_id"
        class="validate @error('conceptopago_id') invalid @enderror">
                    @foreach ($conceptos_pago as $concepto)
                        <option value="{{ $concepto->id }}"
                            {{ old('conceptopago_id', $movcaja->conceptopago_id ?? '') == $concepto->id ? 'selected' : '' }}>
{{ $concepto->descripcion }}
                        </option>
                    @endforeach
                </select>
                <label for="conceptopago_id">{{ __('Concepto de Pago') }}</label>
                                     <span class="helper-text" id="conceptopago_id_spn" data-error="" data-success=""></span>

            </div>
        @endif



            <!-- Campo de autocompletado para persona -->
 <div class="input-field col s12 m6 persona-wrapper">

            <div class="persona-flex">
                <input type="text" id="persona_autocomplete"
                    class="autocomplete validate @error('persona_id') invalid @enderror" placeholder="Seleccionar Persona"
                    data-autocomplete-url="{{ route('autocomplete.personas') }}"
                    value="{{ isset($personaCreada)
                        ? "{$personaCreada->personamaestro->nrodoc} - {$personaCreada->apellidos} {$personaCreada->nombres}"
                        : (old('persona_id') !== null
                            ? $personaSeleccionada ?? ''
                            : (isset($movcaja) && isset($movcaja->movimiento->persona)
                                ? "{$movcaja->movimiento->Persona->personamaestro->nrodoc} - {$movcaja->movimiento->persona}"
                                : '0 - VARIOS CLIENTES')) }}"
                    {{ isset($personaCreada) ? '' : 'disabled' }}>
                <button class="btn red left tooltipped" id="btnQuitarCliente" data-tooltip="Deseleccionar cliente"
                    type="button" onclick="eliminarPersona();" style="border-radius: 99em"><i
                        class="fa-solid fa-user-slash"></i></button>
                <button href="{{ route('cliente.create', [$sucursal_id, 'VENTA']) }}?es_principal=true"
                    class="btn teal right tooltipped btn-crear-cliente" data-tooltip="Agregar Nuevo Cliente"
                    id="btnNuevaPersona" data-target="modal-nuevo-cliente" data-sucursal-id="{{ $sucursal_id }}"
                    onclick="abrirModalRegistro('principal');" style="display: none; border-radius: 99em">
                    <i class="material-icons">person_add</i>
                </button>

            </div>
            
            <input type="hidden" name="persona_id" id="persona_id"
                value="{{ old('persona_id', isset($personaCreada) ? $personaCreada->id : $movventa->movimiento->persona_id ?? '26') }}">
            <label for="persona_autocomplete" class="active">{{ __('Persona') }}</label>

            {!! $errors->first(
                'persona_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
     
            
        </div>

            <!-- Select para 'responsable' -->
            <div class="input-field col s12 m6">
                <i class="fa-solid fa-user-tie prefix"></i>
                <select name="responsable_id" id="responsable_id"
                class="validate @error('responsable_id') invalid @enderror">
            @foreach ($responsables as $responsable)
                <option value="{{ $responsable->id }}"
                    {{ old('responsable_id', $movcaja->movimiento->responsable_id ?? ($persona->id ?? '')) == $responsable->id ? 'selected' : '' }}>
{{ $responsable->personamaestro->nrodoc }} - {{ $responsable->apellidos }} {{ $responsable->nombres }}
                </option>
            @endforeach
        </select>
        
                <label for="responsable_id">{{ __('Responsable') }}</label>
                {!! $errors->first(
                    'responsable_id',
                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                ) !!}
            </div>
  
        <div class="input-field col s12 m4">
            <select name="caja_id" id="caja_id" class="validate @error('caja_id') invalid @enderror">
                @foreach ($cajas as $caja)
<option value="{{ $caja->id }}"
    {{ old('caja_id', $movcaja->caja_id ?? '') == $caja->id ? 'selected' : '' }}>
    {{ $caja->numero }}
</option>
                @endforeach
            </select>
            <label for="caja_id">{{ __('Caja') }}</label>
            {!! $errors->first(
                'caja_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>


        <input type="hidden" name="operacion" value="{{ $operacion }}">


        @if ($operacion == 'APERTURAR')

            <!-- Select para 'turno' -->
            <div class="input-field col s12 m4">
                <select name="turno_id" id="turno_id" class="validate @error('turno_id') invalid @enderror">
                    @foreach ($turnos as $turno)
<option value="{{ $turno->id }}"
    {{ old('turno_id', $movcaja->turno_id ?? '') == $turno->id ? 'selected' : '' }}>
    {{ $turno->nombre }}
</option>
                    @endforeach
                </select>
                <label for="turno_id">{{ __('Turno') }}</label>
                {!! $errors->first(
                    'turno_id',
                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                ) !!}
            </div>
        @else
            <!-- Campo de texto para 'pagado' -->
            <div class="input-field col s12 m4">
                <input type="number" step="0.01" name="pagado" id="pagado"
                    class="validate @error('pagado') invalid @enderror"
                    value="{{ old('pagado', $movcaja->total ?? 0.0) }}" readonly>
                <label for="pagado" class="active">{{ __('Pagado') }}</label>
                {!! $errors->first('pagado', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
            </div>
        @endif


        @if ($operacion === 'APERTURAR')
            @if (isset($montoCierreAnterior) && $montoCierreAnterior !== null)
                <!-- Mostrar el monto del cierre anterior -->
                <div class="input-field col s12 m4">
                    <input type="number" step="0.01" name="total" id="total"
                        class="validate @error('total') invalid @enderror totalapertura" value="{{ $montoCierreAnterior }}"
                        readonly>
                    <label for="total" class="active">{{ __('Monto del cierre anterior') }}</label>
                    {!! $errors->first('total', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                </div>
            @else
                <!-- No hay monto anterior -->
                <div class="input-field col s12 m4">
                    <input type="number" step="0.01" name="total" id="total"
                        class="validate @error('total') invalid @enderror totalapertura" value="{{ $movcaja->total ?? '0.00' }}"
                        readonly>
                    <label for="total" class="active">{{ __('Total') }}</label>
                    {!! $errors->first('total', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                </div>
            @endif
        @else
            <!-- Campo de total para otras operaciones -->
            <div class="input-field col s12 m4">
                <input type="number" step="0.01" name="total" id="total"
                    class="validate @error('total') invalid @enderror"
                    value="{{ old('total', $movcaja->total ?? 0.0) }}" readonly>
                <label for="total" class="active">{{ __('Total') }}</label>
                {!! $errors->first('total', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
            </div>
        @endif

        <!-- Notas -->
            <div class="input-field col s12">
                <i class="fa-solid fa-note-sticky prefix"></i>
                <textarea name="comentario" id="notas_caja" class="materialize-textarea validate @error('comentario') invalid @enderror"
                    rows="1" style="height: auto; overflow-y: hidden; resize: vertical;"
                    oninput="this.style.height='';this.style.height=this.scrollHeight+'px'">{{ old('comentario',    isset($movcaja) ? $movcaja?->movimiento?->comentario : '') }}</textarea>

                <label for="comentario"
                    class="{{ $movcaja?->movimiento?->comentario ? 'active' : '' }}">
                    {{ __('Notas al movimiento de caja') }}
                </label>
            </div>

        </div>
    </div>
    <!-- Select para 'tabla' -->
    <div class="col s12 m12 l4 ">
        <input type="hidden" id="detalles_movimientos_json" name="detalles_movimientos_json"
            value="{{ old('detalles_movimientos_json', json_encode($detallesMovimientos ?? [])) }}">
        <!-- Tabla Dinámica para Detalle de Movimientos de Caja -->
        <div class="row">
            <div class="col s12">
                <div class="table-responsive">
                    <table class="highlight striped centered data-table" id="tabla-medios-pago">
                        <thead class="thead-custom">
                            <tr style="color: white">
                                <th>{{ __('Tipo') }}</th>
                                <th>{{ __('Monto') }}</th>
                                <th>{{ __('Medio de Pago') }}</th>
                                <th>{{ __('Detalles') }}</th>
                                <th>{{ __('Notas') }}</th>
                                <th>{{ __('F. Venc.') }}</th>

                                <th>
                                    <button type="button" class="btn btn-small green white" id="addRowBtn"
                                        onclick="agregarMovimiento()"
                                        style=" border-radius: 50%; color: black; position: relative;">
                                        <i class="material-icons"
                                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">add</i>
                                    </button>

                                </th>
                            </tr>
                        </thead>
                        <tbody id="detalle-movcaja">
                            @if ($operacion === 'APERTURAR' && isset($montoCierreAnterior) && $montoCierreAnterior !== null)
                                <!-- Mostrar el monto del cierre anterior en la tabla -->
                                <tr onclick="editarMovimiento(this, event)">
                                    <td>
                                        <span class="new badge green white-text" data-badge-caption="Pagado"></span>
                                    </td>
                                    <td class="inpt-cantidad-mc monto">{{ number_format($montoCierreAnterior, 2) }}</td>
                                    <td class="medio-pago" data-mediopago-id="1">Efectivo</td>
                                    <td class="detalles">-</td>
                                    <td class="notas">Monto inicial basado en cierre anterior</td>
                                    <td class="fecha-vencimiento">-</td>

                                    <td>
                                        <button type="button" class="btn btn-small red darken-2"
                                            onclick="eliminarFila(this); event.stopPropagation();"
                                            style="border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                            <i class="material-icons"
                                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                                        </button>
                                    </td>
                                </tr>
                            @elseif($operacion === 'APERTURAR' && (!isset($montoCierreAnterior) || $montoCierreAnterior == null))
                                <!-- Si detallesMovimientos está vacío -->
                                @if (empty($detallesMovimientos))
                                <tr onclick="editarMovimiento(this, event)">
                                    <td><span class="new badge green white-text" data-badge-caption="Pagado"></span></td>
                                        <td class="inpt-cantidad-mc monto">0.00</td>
                                        <td class="medio-pago" data-mediopago-id="1">Efectivo</td>
                                        <td class="detalles">-</td>
                                        <td class="notas">Sin notas</td>
                                        <td class="fecha-vencimiento">-</td>

                                        <td>
                                            <button type="button" class="btn btn-small red darken-2"
                                                onclick="eliminarFila(this); event.stopPropagation();"
                                                style=" border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                                <i class="material-icons"
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                                            </button>
                                        </td>
                                    </tr>
                                @else
                                    <!-- Si detallesMovimientos tiene datos -->
                                    @php
                                        $detalles = json_decode($detallesMovimientos, true);
                                    @endphp
                                    @foreach ($detalles as $detalle)
                                    <tr onclick="editarMovimiento(this, event)">
                                      <td>
    <span class="new badge {{ $detalle['tipo'] === 'Pagado' ? 'green' : 'red' }} white-text"
          data-badge-caption="{{ $detalle['tipo'] }}"></span>
</td>
                                            <td class="inpt-cantidad-mc monto">{{ number_format($detalle['monto'], 2) }}</td>
                                            </td>
                                            <td class="medio-pago"
                                                data-mediopago-id="{{ $detalle['mediopago_id'] }}">
                                                {{ $detalle['mediopago'] }}</td>
                                            <td class="detalles">{{ $detalle['numero'] ?? '-' }}</td>
                                            <td class="notas">{{ $detalle['comentario'] ?? 'Sin notas' }}</td>
                                            <td class="fecha-vencimiento">{{ $detalle['fechavencimiento'] ?? '-' }}

                                            <td>
                                                <button type="button" class="btn btn-small red darken-2"
                                                    onclick="eliminarFila(this); event.stopPropagation();"
                                                    style=" border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                                    <i class="material-icons"
                                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @else
                                @if (old('detalles_movimientos_json'))
                                    @php
                                        $detalles = json_decode(old('detalles_movimientos_json'), true);
                                    @endphp
                                    @foreach ($detalles as $detalle)
                                    <tr onclick="editarMovimiento(this, event)">
                                        <td><span
                                                    class="badge {{ $detalle['tipo'] === 'Pagado' ? 'green' : 'red' }} white-text">{{ $detalle['tipo'] }}</span>
                                            </td>
                                            <td class="inpt-cantidad-mc monto">{{ number_format($detalle['monto'], 2) }}</td>
                                            </td>
                                            <td class="medio-pago"
                                                data-mediopago-id="{{ $detalle['mediopago_id'] }}">
                                                {{ $detalle['mediopago'] }}</td>
                                            <td class="detalles">{{ $detalle['numero'] ?? '-' }}</td>
                                            <td class="notas">{{ $detalle['comentario'] ?? 'Sin notas' }}</td>
                                            <td class="fecha-vencimiento">{{ $detalle['fechavencimiento'] ?? '-' }}

                                            <td>
                                                <button type="button" class="btn btn-small red darken-2"
                                                    onclick="eliminarFila(this); event.stopPropagation();"
                                                    style=" border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                                    <i class="material-icons"
                                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif (isset($detallesMovimientos) && $operacion === 'APERTURAR')
                                    @foreach ($detallesMovimientos as $detalle)
                                    <tr onclick="editarMovimiento(this, event)">
                                        <td><span
                                                    class="badge {{ $detalle->tipo === 'Pagado' ? 'green' : 'red' }} white-text">{{ $detalle->tipo }}</span>
                                            </td>
                                            <td class="inpt-cantidad-mc monto">{{ number_format($detalle->monto, 2) }}</td>
                                            
                                            <td class="medio-pago"
                                                data-mediopago-id="{{ $detalle['mediopago_id'] }}">
                                                {{ $detalle['mediopago'] }}</td>
                                            <td class="detalles">{{ $detalle->numero ?? '-' }}</td>
                                            <td class="notas">{{ $detalle->comentario ?? 'Sin notas' }}</td>
                                            <td class="fecha-vencimiento">
                                                {{ $detalle->fechavencimiento ? $detalle->fechavencimiento->format('d/m/Y') : '-' }}
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-small red darken-2"
                                                    onclick="eliminarFila(this); event.stopPropagation();"
                                                    style=" border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                                    <i class="material-icons"
                                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif ($operacion === 'INGRESO' or $operacion === 'EGRESO')
                                @if (!empty($detallesMovimientos) && count($detallesMovimientos) > 0)
                                @foreach ($detallesMovimientos as $detalle)
                                    <!-- Fila de la tabla -->
                                    <tr onclick="editarMovimiento(this, event)">
                                        <!-- Tipo -->
                                        <td>
                                            <span class="badge {{ $detalle['tipo'] === 'Pagado' ? 'green' : 'red' }} white-text">
                                                {{ $detalle['tipo'] }}
                                            </span>
                                        </td>
                            
                                        <!-- Monto -->
                                        <td class="inpt-cantidad-mc monto">{{ number_format($detalle['monto'], 2) }}</td>

                            
                                        <!-- Medio de pago -->
                                        <td class="medio-pago" data-mediopago-id="{{ $detalle['mediopago_id'] }}">
                                            {{ $detalle['mediopago'] }}
                                        </td>
                            
                                        <!-- Detalles (como JSON) -->
                                        <td class="detalles">
                                            {{ collect($detalle['detalles'])->map(function($item) {
                                                // Obtener el valor del primer elemento del array asociativo
                                                return reset($item);
                                            })->implode(' | ') }}
                                        </td>
                                        
                                        <!-- Notas o comentarios -->
                                        <td class="notas">{{ $detalle['comentario'] ?? 'Sin notas' }}</td>
                                                        
                                        <!-- Fecha de vencimiento -->
                                        <td class="fecha-vencimiento">
                                            {{ $detalle['fechavencimiento'] ? \Carbon\Carbon::parse($detalle['fechavencimiento'])->format('d/m/Y') : '-' }}
                                        </td>
                                        <!-- Botón para eliminar -->
                                        <td>
                                            <button type="button" class="btn btn-small red darken-2"
                                                onclick="eliminarFila(this); event.stopPropagation();"
                                                style=" border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                                <i class="material-icons"
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                        
                            
                                    @else
                                        <!-- Valores por defecto si no hay detalles existentes -->
                                        <tr onclick="editarMovimiento(this, event)">
                                            <td><span class="new badge green white-text" data-badge-caption="Pagado"></span></td>
                                            <td class="inpt-cantidad-mc monto" id="total-display">
                                                0.00
                                            </td>
                                            <td class="medio-pago" data-mediopago-id="1">Efectivo</td>
                                            <td class="detalles"></td>
                                            <td class="notas"></td>
                                            <td class="fecha-vencimiento">-</td>

                                            <td>
                                                <button type="button" class="btn btn-small red darken-2"
                                                    onclick="eliminarFila(this); event.stopPropagation();"
                                                    style="border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                                    <i class="material-icons"
                                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @else
                                    <!-- Valores por defecto si no hay old ni detalles existentes -->
                                    <tr onclick="editarMovimiento(this, event)">
                                        <td><span class="new badge green white-text" data-badge-caption="Pagado"></span></td>
                                        <td class="inpt-cantidad-mc monto" id="total-display">
                                            0.00
                                        </td>
                                        <td class="medio-pago" data-mediopago-id="1">Efectivo</td>
                                        <td class="detalles">-</td>
                                        <td class="notas">Sin notas</td>
                                        <td class="fecha-vencimiento">-</td>

                                        <td>
                                            <button type="button" class="btn btn-small red darken-2"
                                                onclick="eliminarFila(this); event.stopPropagation();"
                                                style=" border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                                <i class="material-icons"
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>





    <!-- Radio buttons para 'moneda' -->
    <div class="input-field col s12 m4">

        <label>
            <input type="radio" name="moneda" id="moneda_pen" value="PEN"
                {{ old('moneda', $movcaja?->moneda ?? 'PEN') == 'PEN' ? 'checked' : '' }} />
            <span>S/.</span>
        </label>

        <label style="margin-left: 100px;">
            <input type="radio" name="moneda" id="moneda_usd" value="USD"
                {{ old('moneda', $movcaja?->moneda) == 'USD' ? 'checked' : '' }} />
            <span>$</span>
        </label>
        {!! $errors->first('moneda', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>
    <!-- Campo oculto para 'sucursal_id' -->
    <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">


</div>

<!-- Modal para Detalle de Caja -->
<div id="detalleModal" class="modal modal-bottom-sheet" style="width: 100%; border-radius: 0%; bottom: 0;">
    <div class="modal-content">
        <div class="row without-top">

            <h5 class="grey-text text-darken-3 modal-title without-tb center" id="det_movcaja-submodal-title">
                <i class="fa-solid fa-money-bills"></i>
                Detalle de caja
            </h5>

        </div>
        <div class="row">
            <!-- Select para Tipo -->
            <div class="input-field col s12 m2">
                <select id="tipo_detalle">
                    <option value="Pagado" selected>Pagado</option>
                    <option value="Deuda">Deuda</option>
                </select>
                <label for="tipo_detalle">Tipo</label>
            </div>

            <!-- Campo para Monto -->
            <div class="input-field col s12 m2">
                <input type="number" id="monto_detalle" step="0.01" value="0.00">
                <label for="monto_detalle" class="active">Monto</label>
            </div>
            <!-- Select para Medio de Pago -->
            <div class="input-field col s12 m2 custom-dropdown">
                <select id="medio_pago_detalle" name="medio_pago_detalle" onchange="mostrarSelects(this)">
                    @foreach ($medio_pagos as $medio_pago)
                        <option value="{{ $medio_pago->id }}" data-descripcion="{{ $medio_pago->descripcion }}">
    {{ $medio_pago->descripcion }}
                        </option>
                    @endforeach
                </select>
                <label for="medio_pago_detalle">Medio de Pago</label>
            </div>


            <!-- Select para P.O.S. -->
            <div class="input-field col s12 m2 custom-dropdown" id="pos_container" style="display: none;">
                <select id="pos_detalle" name="pos_detalle">
                    @foreach ($pos as $posItem)
<option value="{{ $posItem->id }}">{{ $posItem->descripcion }}</option>
                    @endforeach
                </select>
                <label for="pos_detalle">P.O.S.</label>
            </div>

<!-- Select para Tarjeta -->
<div class="input-field col s12 m3 custom-dropdown" id="tarjeta_container" style="display: none;">
    <select id="tarjeta_detalle" name="tarjeta_detalle">
        @foreach ($tarjetas as $tarjeta)
<option value="{{ $tarjeta->id }}">
    {{ $tarjeta->descripcion }} - @if($tarjeta->tipo === 'C') Crédito @else Débito @endif
</option>
        @endforeach
    </select>
    <label for="tarjeta_detalle">Tarjeta</label>
</div>

            <div class="input-field col s12 m6 l2 mediopago-data mediopago-3 mediopago-4"  id="det_numero"  style="display: none">
                <input class="mediopago-3 mediopago-4" id="det_numero" name="det_numero" type="text">
                <label id="lblDet_numero" for="det_numero" class="">Nro. operación</label>
                <span class="helper-text" id="det_numero_spn" data-error="" data-success=""></span>
            </div>
            <!-- Select para Banco -->
            <div class="input-field col s12 m2 custom-dropdown" id="banco_container" style="display: none;">
                <select id="banco_detalle" name="banco_detalle">
                    @foreach ($bancos as $banco)
<option value="{{ $banco->id }}">{{ $banco->descripcion }}</option>
                    @endforeach
                </select>
                <label for="banco_detalle">Banco</label>
            </div>

            <!-- Select para Billetera Digital -->
            <div class="input-field col s12 m2 custom-dropdown" id="billetera_container" style="display: none;">
                <select id="billetera_detalle" name="billetera_detalle">
                    @foreach ($billeteras as $billetera)
<option value="{{ $billetera->id }}">{{ $billetera->descripcion }}</option>
                    @endforeach
                </select>
                <label for="billetera_detalle">Billetera Digital</label>
            </div>


            <!-- Campo para Notas -->
            <div class="input-field col s12 m4">
                <textarea id="notas_detalle" class="materialize-textarea"></textarea>
                <label for="notas_detalle">Notas</label>
            </div>
        </div>
    </div>
    <div class="modal-footer right-align">
        <button type="button" class="modal-close btn darken-2" onclick="agregarMovimientoAlDetalle()">
            <i class="material-icons left">save</i>Guardar
        </button>
        <a href="#!" class="modal-close btn-flat">Cancelar</a>
    </div>
</div>
<!-- Botones de Acción -->
<div class="row padding-1 p-1">
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer right-align">
            <button type="submit" class="btn darken-2 form-retroceder">
                <i class="material-icons left">save</i>{{ __('Guardar') }}</button>
            <a href="{{ route('movcaja.index') }}"
                class="modal-close waves-effect waves-green btn-flat btn-cargar-crud btn-retroceder"
                data-icono="fa fa-solid fa-cash-register left" data-titulo="Caja chica">{{ __('Cancelar') }} <i
                    class="material-icons right">clear</i></a>

        </div>
    </div>
</div>
