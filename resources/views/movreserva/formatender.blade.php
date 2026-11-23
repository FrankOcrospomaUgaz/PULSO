@php
    $movreserva = $movreserva ?? null;
    $responsableId = $movreserva->responsable_id ?? null;
    $clasificacion = $movreserva->clasificacion ?? '';
    $fechaActual = now()->format('Y-m-d');
    $horaActual = now()->format('H:i');
@endphp

<div class="row padding-1 p-1">
    <div class="col s12 m12 l6">

        <div class="row">

            <div class="col s12 m6 l3 input-field">
                <input id="fecha" name="fecha" type="text"
                    value="{{ $movreserva?->fecha_registro ?? $fechaActual }}" readonly>
                <label class="active" for="fecha">Fecha de registro</label>
                <span class="helper-text" id="fecha_spn"></span>
            </div>
            <div class="col s12 m6 l4 input-field">
                <i class="fa-solid fa-calendar-day prefix"></i>
                <input id="fechavencimiento" name="fechavencimiento" type="date"
                    value="{{ old('fechavencimiento', $movreserva?->fechavencimiento ? \Carbon\Carbon::parse($movreserva->fechavencimiento)->format('Y-m-d') : $fechaSeleccionada ?? '') }}">
                <label class="active" for="fechavencimiento">F. programada check-in</label>
                <span class="helper-text" id="fechavencimiento_spn"></span>
            </div>

            <input type="hidden" id="habitacion_id" name="habitacion_id" value="{{ $habitacion_id }}">


            <div class="col s12 m6 l5 input-field">
                <i class="fa-solid fa-clock prefix"></i>
                @php
                    $horaVencimiento = $movreserva?->fechavencimiento 
                        ? \Carbon\Carbon::parse($movreserva->fechavencimiento)->format('H:i:s') 
                        : ($horaEntrada ?? $horaActual);
                @endphp
                <input id="horavencimiento" name="horavencimiento" type="time" value="{{ old('horavencimiento', $horaVencimiento) }}">
                <label class="active" for="horavencimiento">H. programada</label>
                <span class="helper-text" id="horavencimiento_spn"></span>
            </div>


            <div class="input-field col s12 m3">
                <i class="fa-solid fa-calendar-days prefix"></i>
                <input id="dias" name="dias" type="number" min="1"
                    value="{{ $movreserva?->dias ?? 1 }}">
                <label for="dias" class="active">Días</label>
            </div>
            <div class="input-field col s12 m3" id="divtarifaprincipal"
            style="display: {{ isset($movreserva) && $movreserva->modo === 'G' ? 'none' : 'block' }};">
            @php
                $habitacionId = $habitacionSeleccionada->id ?? 'default';
                $habitacionPrecio = $habitacionSeleccionada->precio ?? $movreserva->preciohabitacion ?? 0;
            @endphp
        
            <input type="number" id="tarifa-habitacion"
                name="tarifa_habitacion_unico" value="{{ $habitacionPrecio }}"
                class="tarifa-input" oninput="deshabilitarBotonGuardar()">
            <label for="tarifa-{{ $habitacionId }}" class="active">Tarifa</label>
        </div>
        
            
            
            <div class="input-field col s12 m2 left-align" id="calcularboton">
                <button type="button" class="btn waves-effect waves-light darken-2"
                    onclick="calcularTotalDiasReserva()" readonly>
                    Calcular
                </button>
            </div>
            {{-- <div class="col s12 m4 l3 input-field center">
                <a href="{{ route('calendarioreservas.index') }}"
                    class="btn orange center truncate tooltipped btn-cargar-crud" data-tooltip="Calendario de reservas"
                    type="button" data-icono="fa-solid fa-clock left"´data-titulo="Calendario de reservas">
                    <i class="fa-solid fa-calendar white-text right"></i>CALENDARIO
                </a>
            </div> --}}
            <div class="col s12 m4 input-field">
                <i class="fa-solid fa-users prefix"></i>
                <input id="nropersonas" name="nropersonas" type="number" 
                    value="{{ $movreserva?->nropersonas ?? $habitacionSeleccionada?->tipohabitacion?->capacidad ?? 1 }}">
                <span class="helper-text"></span>
            </div>
            
            <div class="input-field col s12 m4">
                <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                <input id="early" type="number" name="early"
                    value="{{ $movreserva_habitacion->early_checkin ?? '0' }}"  oninput="deshabilitarBotonGuardar()">
                <label for="early" class="active">Early checkin</label>
            </div>

            <div class="input-field col s12 m4">
                <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                <input id="late" type="number" name="late"
                    value="{{ $movreserva_habitacion->late_checkout ?? '0' }}" oninput="deshabilitarBotonGuardar()">
                <label for="late" class="active">Late checkout</label>
            </div>
 
                <div class="col s12 m6 l2">
                    <p class="without-tb left-align">
                        <label class="truncate">
                            <input class="filled-in" name="dayuse" type="checkbox" id="dayuseCheckbox"
                                value="0" onchange="">
                            <span>Day use</span>
                        </label>
                    </p>
                </div>

            <!-- Select para 'Motivo de Reserva' -->
            <div class="input-field col s12 m6">
                <i class="fa-solid fa-clipboard-list prefix"></i>

                <select name="motivo_id" id="motivo_id" class="validate @error('motivo_id') invalid @enderror">
                    @foreach ($motivos_reserva as $motivo)
                        <option value="{{ $motivo->id }}"
{{ old('motivo_id', $movreserva->motivo_id ?? '') == $motivo->id ? 'selected' : '' }}>
{{ $motivo->descripcion }}
                        </option>
                    @endforeach
                </select>
                <label for="motivo_id">{{ __('Motivo de Reserva') }}</label>
                {!! $errors->first(
                    'motivo_id',
                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                ) !!}
            </div>
            <div class="input-field col s12 m6">
                <i class="fa-solid fa-globe prefix"></i>

                <select name="origen_id" id="origen_id" class="validate @error('origen_id') invalid @enderror">
                    @foreach ($origenreserva as $origen)
                        <option value="{{ $origen->id }}"
{{ old('origen_id', $movreserva->origen_id ?? '') == $origen->id ? 'selected' : '' }}>
{{ $origen->descripcion }}
                        </option>
                    @endforeach
                </select>
                <label for="origen_id">{{ __('Origen de Reserva') }}</label>
                {!! $errors->first(
                    'origen_id',
                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                ) !!}
            </div>
            <!-- Cliente -->
            <div class="input-field col s12 m5">
                <input type="hidden" name="persona_id" id="persona_id"
                    value="{{ old('persona_id', isset($personaCreada) ? $personaCreada->id : $movreserva->movimiento->persona_id ?? '26') }}">
                <input type="text" id="persona_autocomplete"
                    class="autocomplete validate @error('persona_id') invalid @enderror"
                    placeholder="Seleccione un pasajero principal" data-autocomplete-url="{{ route('autocomplete.personas') }}"
                    value="{{ isset($personaCreada)
                        ? "{$personaCreada->personamaestro->nrodoc} - {$personaCreada->apellidos} {$personaCreada->nombres}"
                        : ($movreserva->movimiento->Persona->personamaestro->nrodoc ?? '') .
                            (isset($movreserva->movimiento->persona) ? ' - ' . $movreserva->movimiento->persona : '') }}"
                    autocomplete="off" required>
                <label for="persona_id" class="active">{{ __('Persona') }}</label>

                {!! $errors->first(
                    'persona_id',
                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                ) !!}
            </div>


            <!-- Botón para quitar cliente -->
            <div class="input-field col s12 m1">
                <button class="btn red left tooltipped" id="btnQuitarCliente" data-tooltip="Deseleccionar cliente"
                    onclick="eliminarPersona();"
                    style="border-radius: 99em; {{ isset($personaCreada) ? 'display: block;' : 'display: none;' }}">
                    <i class="fa-solid fa-user-slash"></i>
                </button>

                <!-- Botón para agregar nuevo cliente -->
                <button href="{{ route('cliente.create', [$sucursal->id, 'RESERVA']) }}?es_principal=true"
                    class="btn teal right tooltipped btn-crear-persona" data-tooltip="Agregar Nuevo Cliente"
                    id="btnNuevaPersona" data-target="modal-nuevo-cliente" data-sucursal-id="{{ $sucursal->id }}"
                    onclick="abrirModalRegistro('principal');"
                    style="border-radius: 99em; {{ isset($personaCreada) ? 'display: none;' : 'display: block;' }}">
                    <i class="material-icons">person_add</i>
                </button>

            </div>
            <div class="input-field col s12 m5">
                <i class="material-icons prefix">group_add</i>
                <input type="hidden" name="acompanantes" id="acompanantes"
                        value="{{ old('acompanantes', isset($acompanantes) ? json_encode($acompanantes, JSON_UNESCAPED_UNICODE) : '') }}">

            
                <input type="text" id="huespedes_autocomplete"
                    class="autocomplete validate @error('acompanantes') invalid @enderror"
                    placeholder="Seleccione acompañantes" autocomplete="off"
                    data-autocomplete-url="{{ route('autocomplete.personas') }}">

                <label for="acompanantes_autocomplete" class="active">Huespedes</label>
                {!! $errors->first(
                    'acompanantes',
                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                ) !!}
            </div>
            <div class="input-field col s12 m1">
                <button
                    href="{{ route('cliente.create', [$sucursal->id, 'RESERVA', $habitacion->id ?? null]) }}?es_acompanante=true"
                    class="btn teal right tooltipped btn-crear-persona" style="border-radius: 99em" type="button"
                    data-tooltip="Agregar Nuevo Cliente" data-target="modal-nuevo-cliente"
                    onclick="abrirModalRegistro('acompanante');">
                    <i class="material-icons">person_add</i>
                </button>

            </div>
            <div id="acompanantes-row" class="row">
                <div class="col s12">
                    <table id="acompanantes-table" class="highlight centered">
                        <thead class="bg-primary">
                            <tr style="color: white">
                                <th>Nro Doc.</th>
                                <th>Nombre Completo</th>
                                <th>Habitación</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                             @foreach ($acompanantesIds as $acompananteId)
                            @php
                                $acompanante = App\Models\Persona::find($acompananteId);
                                $huesped = App\Models\Huesped::where('movreserva_id', $movreserva->id)
                                    ->where('persona_id', $acompananteId)
                                    ->first();
                        
                                $habitacionId = $huesped?->habitacion_id; // puede ser null
                            @endphp
                        
                            <tr data-id="{{ $acompananteId }}">
                                <td>{{ $acompanante->personamaestro->nrodoc }}</td>
                                <td>{{ strtoupper($acompanante->nombres . ' ' . $acompanante->apellidos) }}</td>
                                <td>
                                    <select class="browser-default" name="habitacion_acompanantes[{{ $acompananteId }}]"
                                        onchange="actualizarInputAcompanantes({{ $acompananteId }}, this.value)">
                                        @foreach ($movreserva->habitaciones as $h)
                                            <option value="{{ $h['id'] }}" {{ $h['id'] == $habitacionId ? 'selected' : '' }}>
                                                {{ $h['numero'] }} - {{ $h['tipohabitacion']['nombre'] ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                    
                                
                                <td>
                                    <button class="btn red btn-small" type="button" onclick="eliminarAcompanante(this, {{ $acompananteId }})">
                                        <i class="material-icons">delete</i>
                                    </button>
                                    
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="input-field col s12 m4 l4">
                <i class="fa-brands fa-whatsapp prefix"></i>
                <input id="celular" name="celular" type="text" value="{{ $movreserva?->celular ?? '' }}">
                <label for="celular">Celular</label>
                <span class="helper-text"></span>
            </div>
            <div class="input-field col s12 m8 l8">
                <i class="fa-brands fa-at prefix"></i>
                <input id="email" name="email" type="email" value="{{ $movreserva?->email ?? '' }}">
                <label for="email">Correo electrónico</label>
                <span class="helper-text"></span>
            </div>
            <!-- Select para 'responsable' -->
            <div class="input-field col s12 m6">
                <i class="fa-solid fa-user-tie prefix"></i>
                <select name="responsable_id" id="responsable_id"
                    class="validate @error('responsable_id') invalid @enderror">
                    @foreach ($responsables as $responsable)
                        <option value="{{ $responsable->id }}"
{{ old('responsable_id', $movreserva?->movimiento->responsable_id ?? ($persona->id ?? '')) == $responsable->id ? 'selected' : '' }}>
{{ $responsable->personamaestro->nrodoc }} - {{ $responsable->apellidos }}
{{ $responsable->nombres }}
                        </option>
                    @endforeach
                </select>

                <label for="responsable_id">{{ __('Responsable') }}</label>
                {!! $errors->first(
                    'responsable_id',
                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                ) !!}
            </div>
            <div class="col s12 m12 l6 input-field">
                <i class="fa-solid fa-note-sticky prefix"></i>
                <textarea id="comentario" name="comentario" class="materialize-textarea">{{ $movreserva->observacion ?? '' }}</textarea>
                <label for="comentario" class="{{ !empty($movreserva->observacion) ? 'active' : '' }}">Notas a la reserva</label>
            </div>
            
        </div>
    </div>
    <div class="col s12 m12 l6">
        <div class="row">
            <div class="col s12 m12 l8 input-field">
                <i class="fa-solid fa-bag-shopping prefix"></i>
                <input id="buscarproducto" name="buscarproducto" type="text">
                <label for="buscarproducto">Producto o servicio...</label>
            </div>
            <div class="input-field col s12 m6 l4">
                <select id="clasificacion">
                    <option value="bien" {{ $clasificacion == 'bien' ? 'selected' : '' }}>Bien</option>
                    <option value="servicio" {{ $clasificacion == 'servicio' ? 'selected' : '' }}>Servicio</option>
                </select>
                <label for="clasificacion">Clasificación</label>
            </div>
        </div>
        <div class="row">
            <div class="col s12">
                <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                    <thead class="thead-custom">
                        <tr>
                            <th>Producto o servicio</th>
                            <th>Unidad</th>
                            <th>Cantidad</th>
                            <th>Monto</th>
                            <th>Duración</th>
                            <th>Notas</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movreserva?->detalles ?? [] as $detalle)
                            <tr>
                                <td>{{ $detalle->producto }}</td>
                                <td>{{ $detalle->unidad }}</td>
                                <td>{{ $detalle->cantidad }}</td>
                                <td>{{ $detalle->monto }}</td>
                                <td>{{ $detalle->duracion }} min</td>
                                <td>{{ $detalle->notas }}</td>
                                <td>
                                    <button class="btn red"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Contenedor donde se mostrarán las habitaciones seleccionadas -->
        <div class="row">
            @if (isset($habitacionSeleccionada) && $habitacionSeleccionada)
                <input type="hidden" name="detalles_habitaciones_json" id="detalles_habitaciones_json"
                    value='{!! old(
                        'detalles_habitaciones_json',
                        json_encode(
                            $habitacionSeleccionada
                                ? [
                                    [
                                        'id' => $habitacionSeleccionada->id,
                                        'numero' => $habitacionSeleccionada->numero,
                                        'tipo' => $habitacionSeleccionada->tipohabitacion->nombre,
                                        'situacion' => $habitacionSeleccionada->situacion,
                                        'precio' => $habitacionSeleccionada->precio,
                                    ],
                                ]
                                : [],
                        ),
                    ) !!}' />
            @else

            @php
    $detallesHabitaciones = [];

    if (isset($movreserva)) {
        foreach ($movreserva->movreserva_habitacion as $detalle) {
            $habitacion = $detalle->habitacion;

            if ($habitacion) {
                $detallesHabitaciones[] = [
                    'id' => $habitacion->id,
                    'numero' => $habitacion->numero,
                    'tipo' => $habitacion->tipohabitacion->nombre ?? '',
                    'situacion' => strtolower($habitacion->situacion),
                    'precio' => $detalle->tarifa,
                ];
            }
        }
    }

    $jsonHabitaciones = json_encode($detallesHabitaciones);
@endphp

<input type="hidden" name="detalles_habitaciones_json" id="detalles_habitaciones_json"
value='{{ old("detalles_habitaciones_json", $jsonHabitaciones ?? "[]") }}' />

            @endif



                @if (isset($habitacionSeleccionada) && $habitacionSeleccionada)
                <div class="col s12" id="habitacionesSeleccionadas">

                    <div class="col s12 m4 l2 habitacion-item" id="habitacion-{{ $habitacionSeleccionada->id }}"
                        style="position: relative;">
                        <div class="card custom-card card-habitacion disponible"
                            data-habitacion-id="{{ $habitacionSeleccionada->id }}">

                            <!-- Botón eliminar -->
                            <button class="btn-remove-habitacion"
                                onclick="eliminarHabitacion({{ $habitacionSeleccionada->id }})"
                                title="Eliminar habitación">
                                <i class="fa-solid fa-times"></i>
                            </button>

                            <div class="card-body"
                                style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                <div class="card-item-title">
                                    <span>{{ $habitacionSeleccionada->tipohabitacion->nombre }}</span>
                                </div>
                                <div class="card-item-body">
                                    <div class="habitacion-detalles d-flex flex-column">
                                        <h4 class="font-weight-bold text-dark text-light numerohabitacion">
                                            {{ trim($habitacionSeleccionada->numero) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Input de Tarifa -->
                        <div class="input-field col s12" id="tarifa-grupo" style="display: none">
                            <input type="number" id="tarifa-{{ $habitacionSeleccionada->id }}"
                                name="tarifa_habitacion[]" value="{{ $habitacionSeleccionada->precio ?? 0 }}"
                                class="tarifa-input-grupal">
                            <label for="tarifa-{{ $habitacionSeleccionada->id }}" class="active">Tarifa</label>
                        </div>
                    </div>
                </div>
                @else
                    <!-- Contenedor donde se mostrarán las habitaciones seleccionadas -->

                    <div class="col s12" id="habitacionesSeleccionadas">
                        @if(isset($movreserva) && $habitacion_id)
                        @php
                            // Buscar en la relación movreserva_habitacion el registro que tenga el habitacion_id deseado
                            $detalle = $movreserva->movreserva_habitacion
                                         ->where('habitacion_id', $habitacion_id)
                                         ->first();
                        @endphp
                    
                        {{-- Si encontramos la relación, renderizamos SOLO esa habitación --}}
                        @if($detalle)
                            @php
                                $habitacion = $detalle->habitacion; // El modelo Habitacion relacionado
                                $tarifa = $detalle->tarifa ?? $habitacion->precio;
                            @endphp
                    
                            <div class="col s12 m4 l3 habitacion-item" id="habitacion-{{ $habitacion->id }}"
                                 style="position: relative;">
                                <div class="card custom-card card-habitacion disponible"
                                     data-habitacion-id="{{ $habitacion->id }}">
                                     
                                    <!-- Botón eliminar -->
                                    <button class="btn-remove-habitacion"
                                            onclick="eliminarHabitacion({{ $habitacion->id }})"
                                            title="Eliminar habitación">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                    
                                    <div class="card-body"
                                         style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                        <div class="card-item-title">
                                            <span>{{ $habitacion->tipohabitacion->nombre }}</span>
                                        </div>
                                        <div class="card-item-body">
                                            <div class="habitacion-detalles d-flex flex-column">
                                                <h4 class="font-weight-bold text-dark text-light numerohabitacion">
                                                    {{ trim($habitacion->numero) }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    
                                <!-- Input de Tarifa -->
                                <div class="input-field col s12">
                                    <input type="number"
                                           id="tarifa-{{ $habitacion->id }}"
                                           name="tarifa_habitacion[]"
                                           value="{{ old('tarifa_habitacion.0', $tarifa) }}"
                                           class="tarifa-input-grupal">
                                    <label for="tarifa-{{ $habitacion->id }}" class="active">Tarifa</label>
                                </div>
                            </div>
                        @endif
                    @endif
                    
                    </div>
                    

                @endif
          
            <div class="col s12 center-align" style="display:none;" id="botonAgregarHabitacion"> 
                <div class="card custom-card card-habitacion disponible hoverable"
                     style="cursor: pointer; padding: 20px; width: 150px"
                     onclick="abrirModalHabitaciones()">
                    <div class="card-body">
                        <i class="fa-solid fa-plus fa-2x"></i>
                        <p>Agregar Habitación</p>
                    </div>
                </div>
            </div>
            
                
        </div>


        <!-- Modal para seleccionar habitaciones -->
        <div id="modalHabitaciones" class="modal">
            <div class="modal-content">
                <h5>Seleccionar Habitaciones</h5>
                @foreach ($pisos as $piso)
                    <h6 class="blue-text"> {{ $piso->nombre }}</h6>
                    <div class="card-action row" style="margin: 20px">
                        @foreach ($habitaciones->where('piso_id', $piso->id) as $habitacion)
                            <div class="col s12 m4 l2 habitacion-item">
                                <div class="card custom-card card-habitacion {{ strtolower($habitacion->situacion) }}"
                                    data-habitacion-id="{{ $habitacion->id }}">
                                    <div class="card-body"
                                        style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                        <div class="card-item-title">
                                            <span>{{ $habitacion->tipohabitacion->nombre }}</span>
                                        </div>
                                        <div class="card-item-body">
                                            <div class="habitacion-detalles d-flex flex-column">
                                                <h4 class="font-weight-bold text-dark text-light numerohabitacion">
                                                    {{ $habitacion->numero }}
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="card-item-footer">
                                            <label>
                                                <input type="checkbox" class="filled-in habitacion-checkbox"
                                                    data-id="{{ $habitacion->id }}" data-piso="{{ $piso->id }}"
                                                    data-tipo="{{ $habitacion->tipohabitacion->nombre }}"
                                                    data-situacion="{{ strtolower($habitacion->situacion) }}"
                                                    data-precio="{{ $habitacion->precio }}"
                                                    onclick="toggleHabitacionSelection(this)">
                                                <span style="color: white">Asignar</span>
                                            </label>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button class="modal-close btn-flat">Cerrar</button>
                <a class="btn blue" onclick="confirmarHabitaciones()">Confirmar</a>
            </div>
        </div>
        <!-- Checkbox de Pago -->
        {{-- <div class="col s12 m6 l2">
    <p class="without-tb left-align">
        <label class="truncate">
            <input class="filled-in" name="pagar" type="checkbox" id="pagarCheckbox"
                value="0" onchange="activarPago();">
            <span>Pago</span>
        </label>
    </p>
</div> --}}


        <!-- Columna Izquierda: Tipo de Pago -->
        <div class="col s12 m12" id="tipopago">




            

        </div>
    </div>

</div>
<div class="card-action right-align">
    <button class="btn-flat waves-effect waves-dark btn-cancelar btn-mantenimiento grey-text text-darken-3"
        id="btn-cancel-MovReserva" type="button" onclick="back();">
        CANCELAR <i class="material-icons right">clear</i>
    </button>

    <button class="btn waves-effect waves-light btn-mantenimiento" id="btn-save-MovReserva"
        style="margin-left: 10px;">
        @if (isset($atender))
            <i class="material-icons right">print</i> Check-in
        @else
            <i class="material-icons right">save</i> GUARDAR
        @endif
    </button>
</div>

<!-- Modal Structure -->
<div id="modal-opcion" class="modal" style="max-width: 400px;">
    <div class="modal-content center-align">
        <!-- Botón de cierre (X) -->
        <a href="#!" class="modal-close btn-flat" style="position: absolute; right: 10px; top: 10px;">
            <i class="material-icons">close</i>
        </a>

        <!-- Contenido del modal -->
        <div class="swal2-icon swal2-question swal2-icon-show" style="display: flex;">
            <div class="swal2-icon-content">?</div>
        </div>
        <h4>Registrar personal</h4>
        <p>¿De qué manera desea registrar su nuevo empleado?</p>
        <div class="modal-footer center-align">
            <button id="opcion-doc" class="btn btn-large" data-sucursal-id="{{ $sucursal->id }}">Nro. de
                documento</button>
            <button id="opcion-nombre" class="btn btn-large" data-sucursal-id="{{ $sucursal->id }}">Nombre</button>

        </div>
    </div>
</div>

