@extends('layouts.app')

@section('content')

    <div class="main-body">
        <div class="row" style="margin-top: 0;">
            <div class="col s12">
                <div class="card main-content">
                    <form id="form" class="form-retroceder" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="form-action-url" value="{{ route('movhabitacion.store') }}">
                        @php
                            // Lógica para determinar la modalidad de tarifa
                            $mostrarSwitch = $modalidadtarifario === 'Ambos';
                            $mostrarDias = $modalidadtarifario === 'Dias' || $modalidadtarifario === 'Ambos';
                            $mostrarHoras = $modalidadtarifario === 'Horas';

                            // Definir el valor del switch_state según la modalidad
                            $switchState = $modalidadtarifario === 'Horas' ? 'horas' : 'dias';
                        @endphp

                        <input type="hidden" id="switchState" name="switch_state" value="{{ $switchState }}">
                        <input type="hidden" id="sucursal_id" name="sucursal_id" value="{{ $sucursal->id }}">
                        <input type="hidden" id="modificar-precios-checkin" value="{{ ($permiteModificarTarifaCheckin ?? false) ? 'Si' : 'No' }}">


                        @if ($mostrarSwitch || $mostrarHoras)
                            <div class="row">
                                <div class="col s12 center-align">
                                    <div class="switch">
                                        <label>
                                            Por días
                                            <input type="checkbox" id="switchHoras" onchange="toggleTiempoHabitacion()"
                                                {{ $modalidadtarifario === 'Horas' ? 'checked' : '' }}>
                                            <span class="lever"></span>
                                            Por horas
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row padding-1 p-1">
                            <div class="col s12 m12 l8">
                                <!-- Información de Habitación -->


                                {{-- <div class="input-field col s12 m3">
                                    <i class="material-icons prefix">meeting_room</i>
                                    <input class="right-align" id="habitacion_display"
                                        style="font-size: 28px !important; font-weight: bold;" type="text"
                                        value="{{ $habitacion->numero }} - {{ $habitacion->tipohabitacion->nombre }}" readonly>
                                    <label for="habitacion_display" class="active">Habitación</label>
                                    <input type="hidden" id="habitacion" name="habitacion_id" value="{{ $habitacion->id }}">
                                </div> --}}
                                <input type="hidden" id="habitacion_id" value="{{ $habitacion->id }}">
                                <div class="row" style="margin-bottom: 0px" >
                                    <div class="input-field col s12 m3" id="diastotales"
                                        style="{{ $mostrarDias ? '' : 'display: none;' }}">
                                        <i class="material-icons prefix">calendar_today</i>
                                        <input id="dias" type="number" min='0' name="dias_totales"
                                            value="1">
                                        <label for="dias" class="active">Noches</label>
                                    </div>
                                    @php
                                        $jsonPrecioHoras = json_decode(
                                            $habitacion->tipohabitacion->json_precio_horas,
                                            true,
                                        );

                                        $precioHabitacion = $habitacion->precio; // Precio por días por defecto
                                        $horasPrimeraOpcion = 1; // Valor por defecto de horas si no hay tarifas por horas

                                        if (
                                            $modalidadtarifario === 'Horas' &&
                                            isset($jsonPrecioHoras) &&
                                            count($jsonPrecioHoras) > 0
                                        ) {
                                            $precioHabitacion = $jsonPrecioHoras[0]['precio']; // Tomamos el precio de la primera opción por horas

                                            // También tomamos las horas de la primera opción
                                            preg_match('/\d+/', $jsonPrecioHoras[0]['hora'], $match);
                                            $horasPrimeraOpcion = intval($match[0] ?? 1); // Si no encuentra número, default 1
                                        }
                                    @endphp



                                    <div class="input-field col s12 m3" id="horastotales"
                                        style="{{ $mostrarHoras ? '' : 'display: none;' }}">
                                        <i class="material-icons prefix">access_time</i>
                                        <input id="horas" type="number" min="1" name="horas_totales"
                                            value="{{ $horasPrimeraOpcion }}">
                                        <label for="horas" class="active">Horas</label>
                                    </div>

                                    <div class="input-field col s12 m1"id="calcularboton" style="{{ $mostrarDias ? '' : 'display: none;' }}">
                                        <button class="btn green left tooltipped" type="button" style="margin-left:-5px;"
                                            data-tooltip="Calcular" onclick="calcularTotalCheckin()">
                                            <i class="material-icons">calculate</i>
                                        </button>


                                    </div>

                                    <div class="input-field col s12 m4" id="fecha-ingreso-div"
                                        style="{{ $mostrarDias ? '' : 'display: none;' }}">
                                        <i class="material-icons prefix">event_available</i>
                                        <input id="fechaingreso" name="fechaingreso" type="datetime-local"
                                            value="{{ $initialDate }}">
                                        <label for="fechaingreso" class="active">Fecha Ingreso</label>
                                    </div>

                                    <div class="input-field col s12 m4" id="fecha-salida-div"
                                        style="{{ $mostrarDias ? '' : 'display: none;' }}">
                                        <i class="material-icons prefix">event_busy</i>
                                        <input id="fechasalida" name="fechasalida" type="datetime-local"
                                            value="{{ $defaultSalidaDate }}" required>
                                        <label for="fechasalida" class="active">Fecha Salida</label>
                                    </div>

                                    <div class="input-field col s12 m5" id="hora-ingreso-div"
                                        style="{{ $mostrarHoras ? '' : 'display: none;' }}">
                                        <i class="material-icons prefix">access_time</i>
                                        <input id="horaingreso" name="horaingreso" type="time"
                                            value="{{ $initialTime }}">
                                        <label for="horaingreso" class="active">Hora Ingreso</label>
                                    </div>


                                    @php
                                        $horaIngreso = $initialTime ?? '00:00'; // Default si no existe
                                        $horaSalidaCalculada = $horaIngreso; // Inicialmente igual

                                        if (
                                            $modalidadtarifario === 'Horas' &&
                                            $horaIngreso &&
                                            $horasPrimeraOpcion > 0
                                        ) {
                                            $horaIngresoDateTime = \Carbon\Carbon::createFromFormat(
                                                'H:i',
                                                $horaIngreso,
                                            );
                                            $horaSalidaDateTime = $horaIngresoDateTime
                                                ->copy()
                                                ->addHours($horasPrimeraOpcion);
                                            $horaSalidaCalculada = $horaSalidaDateTime->format('H:i');
                                        }
                                    @endphp
                                    <div class="input-field col s12 m4" id="hora-salida-div"
                                        style="{{ $mostrarHoras ? '' : 'display: none;' }}">
                                        <i class="material-icons prefix">access_time</i>
                                        <input id="horasalida" name="horasalida" type="time"
                                            value="{{ $horaSalidaCalculada }}">
                                        <label for="horasalida" class="active">Hora Salida</label>
                                    </div>

                                    @php
                                        $jsonPrecioHoras = json_decode(
                                            $habitacion->tipohabitacion->json_precio_horas,
                                            true,
                                        );
                                    @endphp


                                    @isset($jsonPrecioHoras)
                                        <div class="input-field col s12 m12" id="hora-card"
                                            style="margin-button:0px;{{ $modalidadtarifario === 'Horas' ? '' : 'display: none;' }} ">
                                            @foreach ($jsonPrecioHoras as $precio)
                                                @php
                                                    // Extraer el número de horas (ej. de "12 h" saca 12)
                                                    preg_match('/\d+/', $precio['hora'], $match);
                                                    $horas = intval($match[0] ?? 0);
                                                @endphp
                                                <div class="col s12 m2">
                                                    <div class="card-panel teal white-text"
                                                        onclick="setHoraSalida({{ $horas }})"
                                                        style="padding: 5px 10px; border-radius: 20px; cursor: pointer;">
                                                        <span style="display: block; text-align: center;">
                                                            {{ $precio['hora'] }} - S/ {{ $precio['precio'] }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endisset








                                </div>

                                <div id="precios-data" data-precios='{{ json_encode($jsonPrecioHoras) }}'></div>
                                <input id="preciohabitaciondia" name="preciohabitaciondia" type="hidden"
                                    value="{{ $habitacion->precio }}" readonly>

                                <!-- Precio y Capacidad -->
                                <div class="row">
                                    @php
                                        $precioHabitacion = $habitacion->precio; // Precio por días por defecto

                                        if (
                                            $modalidadtarifario === 'Horas' &&
                                            isset($jsonPrecioHoras) &&
                                            count($jsonPrecioHoras) > 0
                                        ) {
                                            $precioHabitacion = $jsonPrecioHoras[0]['precio']; // Tomamos el precio de la primera opción por horas
                                        }
                                    @endphp

                                    <div class="input-field col s12 m6">
                                           <i class="fa-solid fa-user-tie prefix"></i>
                                        <select name="recepcionista_id" id="recepcionista_id"
                                            class="validate @error('recepcionista_id') invalid @enderror">

@foreach ($recepcionistas as $recepcionista)
    <option 
        value="{{ $recepcionista->id }}"
{{ $recepcionista->id == auth()->user()->persona_id? 'selected' : '' }}>{{ $recepcionista->personamaestro->nrodoc }} - {{ $recepcionista->apellidos}} {{ $recepcionista->nombres }}
    </option>
@endforeach
                                        </select>
                                        <label for="recepcionista_id" >{{ __('Recepcionista') }}</label>
                                        {!! $errors->first(
                                            'recepcionista_id',
                                            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                        ) !!}
                                    </div>
                                               

                                 <div class="input-field col s12 m3">
    <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
    <input id="preciototal" 
           name="total" 
           type="number" 
           step="0.01" 
           value="{{ number_format($precioHabitacion, 2, '.', '') }}" 
           readonly>
    <label for="preciototal" class="active">Total</label>
</div>


                                    <div class="input-field col s12 m3">
                                        <i class="material-icons prefix">group</i>
                                        <input id="capacidad" type="number"
                                            value="{{ $habitacion->tipohabitacion->capacidad }}" readonly>
                                        <label for="capacidad" class="active">Capacidad</label>
                                    </div>
                                </div>

                                <!-- Pasajeros -->
                                <div class="row">
                                    <div class="input-field col s12 m6">
                                        <i class="material-icons prefix">person</i>
                                        <input type="hidden" name="persona_principal" id="persona_id"
                                            value="{{ old('persona_principal', $principalId ?? '26') }}">
                                        <input type="text" id="persona_autocomplete"
                                            class="autocomplete @error('persona_principal') invalid @enderror"
                                            placeholder="Seleccione un huesped"
                                            data-autocomplete-url="{{ route('autocomplete.personas') }}"
                                            value="{{ isset($personaCreada) ? "{$personaCreada->personamaestro->nrodoc} - {$personaCreada->apellidos} {$personaCreada->nombres}" : '0 - CLIENTES VARIOS' }}"
                                            autocomplete="off" required disabled>
                                        <label for="persona_principal_autocomplete" class="active">Huesped
                                            Principal</label>
                                        {!! $errors->first(
                                            'persona_principal',
                                            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                        ) !!}
                                    </div>

                                    <div class="input-field col s12 m1">
                                        <!-- Botón para quitar cliente -->
                                        <button class="btn red left tooltipped" id="btnQuitarCliente"
                                            data-tooltip="Deseleccionar huesped" type="button"
                                            onclick="eliminarPersona();"
                                            style="border-radius: 99em; display: block;">
                                            <i class="fa-solid fa-user-slash"></i>
                                        </button>

                                        <!-- Botón para agregar nuevo cliente -->
                                        <button
                                            href="{{ route('cliente.create', [$sucursal->id, 'CHECKIN', $habitacion->id ?? null]) }}?es_principal=true"
                                            class="btn teal right tooltipped btn-crear" type="button"
                                            data-tooltip="Agregar Nuevo Huesped" id="btnNuevaPersona"
                                            data-target="modal-nuevo-cliente"
                                            style="border-radius: 99em; display: none;">
                                            <i class="material-icons">person_add</i>
                                        </button>
                                    </div>





                                    <div class="input-field col s12 m4">
                                        <i class="material-icons prefix">group_add</i>
                                        <input type="hidden" name="acompanantes" id="acompanantes"
                                            value="{{ old('acompanantes', json_encode($acompanantesIds ?? [])) }}">
                                        <input type="text" id="acompanantes_autocomplete"
                                            class="autocomplete validate @error('acompanantes') invalid @enderror"
                                            placeholder="Seleccione acompañantes" autocomplete="off"
                                            data-autocomplete-url="{{ route('autocomplete.personas') }}">

                                        <label for="acompanantes_autocomplete" class="active">Acompañantes</label>
                                        {!! $errors->first(
                                            'acompanantes',
                                            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                        ) !!}
                                    </div>
                                    <div class="input-field col s12 m1">

                                        <button
                                            href="{{ route('cliente.create', [$sucursal->id, 'CHECKIN', $habitacion->id ?? null]) }}?es_acompanante=true"
                                            class="btn teal right tooltipped btn-crear-cliente"
                                            style="border-radius: 99em" data-tooltip="Agregar Nuevo Acompañante"
                                            data-target="modal-nuevo-cliente"
                                            onclick="abrirModalRegistro('acompanante');">
                                            <i class="material-icons">person_add</i>
                                        </button>


                                    </div>
                                    <div id="acompanantes-row" class="row">
                                        <div class="col s12">
                                            <table id="acompanantes-table" class="highlight centered data-table">
                                                <thead class="thead-custom">
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
                                                        @endphp
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>


                                </div>

                                <!-- Comentario -->
                                <div class="row">
                                    <div class="input-field col s12">
                                        <i class="material-icons prefix">edit</i>
                                        <textarea id="comentario" name="comentario" class="materialize-textarea"></textarea>
                                        <label for="comentario">Comentario</label>
                                    </div>
                                </div>
                            </div>
                                <input type="hidden" name="detalles_habitaciones_json"
                                                id="detalles_habitaciones_json" value='{!! old(
                                                    'detalles_habitaciones_json',
                                                    json_encode(
                                                        $habitacion
                                                            ? [
                                                                [
                                                                    'id' => $habitacion->id,
                                                                    'numero' => $habitacion->numero,
                                                                    'tipo' => $habitacion->tipohabitacion->nombre,
                                                                    'situacion' => $habitacion->situacion,
                                                                    'precio' => $habitacion->precio,
                                                                ],
                                                            ]
                                                            : [],
                                                    ),
                                                ) !!}' />
                            <div class="col s12 m12 l4" style="justify-content: center;  ">
                                <div id="habitacionesSeleccionadas">
                                    <div class="col s12 m4 l6 habitacion-item" style=" min-width: 140px;"
                                        id="habitacion-{{ $habitacion->id }}" style="position: relative;">
                                        
                                        <div class="card custom-card card-habitacion disponible"
                                            data-habitacion-id="{{ $habitacion->id }}">

                                            <!-- Botón eliminar -->
                                            <a class="btn-remove-habitacion tooltipped"
                                                onclick="eliminarHabitacion({{ $habitacion->id }})"
                                                data-tooltip="Eliminar habitación">
                                                <i class="fa-solid fa-times"></i>
                                            </a>
                                            <a class="btn-change-habitacion tooltipped"
                                                onclick="cambiarHabitacion({{ $habitacion->id }})"
                                                data-tooltip="Cambiar habitación">
                                                <i class="fa-solid fa-right-left"></i>
                                            </a>


                                            <div class="card-body"
                                                style="min-height: 90px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                <div class="card-item-title"
                                                    style="text-align: center; display: flex; justify-content: center; align-items: center;">
                                                    <span>{{ $habitacion->tipohabitacion->nombre }}</span>
                                                </div>

                                                <div class="card-item-body">

                                                    <p style="font-size:30px; text-align: center; display: flex; justify-content: center; align-items: center; margin: 10px"
                                                        class="font-weight-bold text-dark text-light numerohabitacion">
                                                        {{ trim($habitacion->numero) }}</p>

                                                </div>
                                            </div>
                                        </div>

                                        <!-- Input de Tarifa -->
                                        <div class="input-field col s12" id="tarifa-grupo" style="min-width: 137px;">
                                            <input type="number" id="tarifa-{{ $habitacion->id }}"
                                                name="tarifa_habitacion[]" value="{{ $precioHabitacion?? 0 }}"
                                                class="tarifa-input-grupal" step="0.01" @unless($permiteModificarTarifaCheckin ?? false) readonly @endunless>
                                            <label for="tarifa-{{ $habitacion->id }}" class="active">Tarifa</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col s12 m4 l6 habitacion-item " id="botonmodalhabitacion" style=" min-width: 140px;">
                                        <div class="card custom-card card-habitacion disponible"
                                            onclick="abrirModalHabitaciones()">
                                            <div class="card-body" 
                                                style="min-height: 90px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                <i class="fa-solid fa-plus fa-2x" ></i>
                                               
                                            </div>
                                        </div>
                                    </div>
                                
                                </div>
                                
                            </div>
                        </div>
                        <!-- Botones de Acción -->
                        <div class="row center-align">
                            <!-- Botón de Check-In -->
                            <button class="btn-large waves-effect waves-light teal darken-1 form-retroceder"
                                style="margin-right: 20px;">
                                <i class="material-icons left">login</i> Confirmar Check-In
                            </button>

                            <!-- Botón de Pagar -->
                            <button class="btn-large waves-effect waves-light blue darken-1 form-cobrar"
                                style="margin-left: 20px;">
                                <i class="material-icons left">attach_money</i> Check-In y Cobrar
                            </button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
        <div id="modalHabitaciones" class="modal">
            <div class="modal-content">
                <h5>Seleccionar Habitaciones</h5>
                @foreach ($pisos as $piso)
                    <h6 class="blue-text"> {{ $piso->nombre }}</h6>
                    <div class="card-action row" style="margin: 20px">
                        @foreach ($habitaciones->where('piso_id', $piso->id) as $hab)
                            <div class="col s12 m4 l2 habitacion-item">
                                <div class="card custom-card card-habitacion {{ strtolower($hab->situacion) }}"
                                    data-habitacion-id="{{ $hab->id }}">
                                    <div class="card-body"
                                        style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                        <div class="card-item-title">
                                            <span>{{ $hab->tipohabitacion->nombre }}</span>
                                        </div>
                                        <div class="card-item-body">
                                            <div class="habitacion-detalles d-flex flex-column">
                                                <h4 class="font-weight-bold text-dark text-light numerohabitacion">
                                                    {{ $hab->numero }}
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="card-item-footer">
                                            <label>
                                                <input type="checkbox" class="filled-in habitacion-checkbox"
                                                    data-id="{{ $hab->id }}" data-piso="{{ $piso->id }}"
                                                    data-tipo="{{ $hab->tipohabitacion->nombre }}"
                                                    data-situacion="{{ strtolower($hab->situacion) }}"
                                                    data-precio="{{ $hab->precio }}"
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

@endsection

