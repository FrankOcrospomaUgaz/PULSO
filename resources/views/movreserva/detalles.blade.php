@extends('layouts.app')
@section('content')

    @php
        $movreserva = $movreserva ?? null;
        $responsableId = $movreserva->responsable_id ?? null;
        $clasificacion = $movreserva->clasificacion ?? '';
        $fechaActual = now()->format('Y-m-d');
        $horaActual = now()->format('H:i');
    @endphp
    <div class="main-body">
        <div class="row">
            <div class="card main-content">

                <div class="card-body bg-white"></div>
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
                                    value="{{ old('fechavencimiento', $movreserva?->fechavencimiento ? \Carbon\Carbon::parse($movreserva->fechavencimiento)->format('Y-m-d') : $fechaSeleccionada ?? '') }}"
                                    readonly>
                                <label class="active" for="fechavencimiento">F. programada check-in</label>
                                <span class="helper-text" id="fechavencimiento_spn"></span>
                            </div>

                            <div class="col s12 m6 l5 input-field">
                                <i class="fa-solid fa-clock prefix"></i>
                                @php
                                    $horaVencimiento = $movreserva?->fechavencimiento
                                        ? \Carbon\Carbon::parse($movreserva->fechavencimiento)->format('H:i:s')
                                        : ($horaEntrada ?? $horaActual);
                                @endphp
                                <input id="horavencimiento" name="horavencimiento" type="time"
                                    value="{{ old('horavencimiento', $horaVencimiento) }}" readonly>
                                <label class="active" for="horavencimiento">H. programada</label>
                                <span class="helper-text" id="horavencimiento_spn"></span>
                            </div>

                            <div class="input-field col s12 m3">
                                <i class="fa-solid fa-calendar-days prefix"></i>
                                <input id="dias" name="dias" type="number" min="1" value="{{ $movreserva?->dias ?? 1 }}"
                                    readonly>
                                <label for="dias" class="active">Días</label>
                            </div>

                            <div class="input-field col s12 m4" id="divtarifaprincipal"
                                style="display: {{ isset($movreserva) && $movreserva->modo === 'G' ? 'none' : 'block' }};">
                                @php
                                    $habitacionId = $habitacionSeleccionada->id ?? 'default';
                                    $habitacionPrecio = $habitacionSeleccionada->precio ?? $movreserva->preciohabitacion ?? 0;
                                @endphp
                                <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                <input type="number" id="tarifa-habitacion" step="0.01" name="tarifa_habitacion_unico"
                                    value="{{ $habitacionPrecio }}" class="tarifa-input" readonly>
                                <label for="tarifa-{{ $habitacionId }}" class="active">Tarifa</label>
                            </div>

                            <div class="input-field col s12 m3">
                                <i class="fa-solid fa-users prefix"></i>
                                <input id="nropersonas" name="nropersonas" type="number"
                                    value="{{ $movreserva?->nropersonas ?? $habitacionSeleccionada?->tipohabitacion?->capacidad ?? 1 }}"
                                    readonly>
                                <span class="helper-text"></span>
                            </div>

                            <div class="input-field col s12 m4">
                                <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                <input id="early" type="number" name="early"
                                    value="{{ $movreserva_habitacion->early_checkin ?? 0 }}" readonly>
                                <label for="early" class="active">Early checkin</label>
                            </div>

                            <div class="input-field col s12 m4">
                                <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                <input id="late" type="number" name="late"
                                    value="{{ $movreserva_habitacion->late_checkout ?? 0 }}" readonly>
                                <label for="late" class="active">Late checkout</label>
                            </div>

                            <div class="col s12 m6 l2">
                                <p class="without-tb left-align">
                                    <label class="truncate">
                                        <input class="filled-in" name="dayuse" type="checkbox" id="dayuseCheckbox" value="0"
                                            disabled>
                                        <span>Day use</span>
                                    </label>
                                </p>
                            </div>

                            <div class="input-field col s12 m6">
                                <i class="fa-solid fa-clipboard-list prefix"></i>
                                <select name="motivo_id" id="motivo_id" class="validate" disabled>
                                    @foreach ($motivos_reserva as $motivo)
                                        <option value="{{ $motivo->id }}" {{ old('motivo_id', $movreserva->motivo_id ?? '') == $motivo->id ? 'selected' : '' }}>{{ $motivo->descripcion }}</option>
                                    @endforeach
                                </select>
                                <label for="motivo_id">{{ __('Motivo de Reserva') }}</label>
                            </div>

                            <div class="input-field col s12 m6">
                                <i class="fa-solid fa-globe prefix"></i>
                                <select name="origen_id" id="origen_id" class="validate" disabled>
                                    @foreach ($origenreserva as $origen)
                                        <option value="{{ $origen->id }}" {{ old('origen_id', $movreserva->origen_id ?? '') == $origen->id ? 'selected' : '' }}>{{ $origen->descripcion }}</option>
                                    @endforeach
                                </select>
                                <label for="origen_id">{{ __('Origen de Reserva') }}</label>
                            </div>

                            <!-- Cliente -->
                            <div class="input-field col s12 m5">
                                <input type="hidden" name="persona_id" id="persona_id"
                                    value="{{ old('persona_id', isset($personaCreada) ? $personaCreada->id : $movreserva->movimiento->persona_id ?? '26') }}">
                                <input type="text" id="persona_autocomplete" class="autocomplete validate"
                                    value="{{ isset($personaCreada) ? "{$personaCreada->personamaestro->nrodoc} - {$personaCreada->apellidos} {$personaCreada->nombres}" : ($movreserva->movimiento->Persona->personamaestro->nrodoc ?? '') . (isset($movreserva->movimiento->persona) ? ' - ' . $movreserva->movimiento->persona : '') }}"
                                    readonly>
                                <label for="persona_id" class="active">{{ __('Persona') }}</label>
                            </div>

                            <div class="input-field col s12 m1">
                                <button class="btn red left" id="btnQuitarCliente" style="display: none;">
                                    <i class="fa-solid fa-user-slash"></i>
                                </button>
                            </div>

                            <div class="input-field col s12 m5">
                                <i class="material-icons prefix">group_add</i>
                                <input type="hidden" name="acompanantes" id="acompanantes"
                                    value="{{ old('acompanantes', isset($acompanantes) ? json_encode($acompanantes, JSON_UNESCAPED_UNICODE) : '') }}">
                                <input type="text" id="huespedes_autocomplete" class="autocomplete"
                                    placeholder="Seleccione acompañantes" autocomplete="off"
                                    value="{{ old('acompanantes') }}" readonly>
                                <label for="acompanantes_autocomplete" class="active">Acompañantes</label>
                            </div>

                            <div class="input-field col s12 m1">
                                <button class="btn teal right tooltipped btn-crear-persona" type="button" disabled>
                                    <i class="material-icons">person_add</i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col s12 m12 l6">
                        <div class="row">
                            <div class="col s12 m12 l8 input-field">
                                <i class="fa-solid fa-bag-shopping prefix"></i>
                                <input id="buscarproducto" name="buscarproducto" type="text" readonly>
                                <label for="buscarproducto">Producto o servicio...</label>
                            </div>

                            <div class="input-field col s12 m6 l4">
                                <select id="clasificacion" disabled>
                                    <option value="bien" {{ $clasificacion == 'bien' ? 'selected' : '' }}>Bien</option>
                                    <option value="servicio" {{ $clasificacion == 'servicio' ? 'selected' : '' }}>Servicio
                                    </option>
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
                                                    <button class="btn red" disabled><i class="fa-solid fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col s12 m12 l12">
    <div class="row">
        <div class="col s12" id="habitacionesSeleccionadas">
            @if (isset($movreserva) && $movreserva->habitaciones->isNotEmpty())
                @foreach ($movreserva->habitaciones as $habitacion)
                    <div class="col s12 m4 l2 habitacion-item" id="habitacion-{{ $habitacion->id }}"
                        style="position: relative;">
                        <div class="card custom-card card-habitacion {{ strtolower($habitacion->situacion) }}"
                            data-habitacion-id="{{ $habitacion->id }}">

                            <!-- Botón eliminar -->
                            <button class="btn-remove-habitacion"
                                onclick="eliminarHabitacion({{ $habitacion->id }})" title="Eliminar habitación">
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
                                            {{ trim($habitacion->numero) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Input de Tarifa -->
                        <div class="input-field col s12">
                            <input type="number" id="tarifa-{{ $habitacion->id }}" name="tarifa_habitacion[]"
                                value="{{ old('tarifa_habitacion.' . $loop->index, $habitacion->precio) }}"
                                class="tarifa-input-grupal" step="0.01">
                            <label for="tarifa-{{ $habitacion->id }}" class="active">Tarifa</label>
                        </div>
                    </div>
                @endforeach
            @else
                <p>No hay habitaciones asignadas.</p>
            @endif
        </div>
    </div>
</div>

                    </div>
                </div>
                

                <div class="card-action right-align">
                    <button class="btn-flat waves-effect waves-dark btn-cancelar btn-mantenimiento grey-text text-darken-3"
                        id="btn-cancel-MovReserva" type="button" onclick="back();">
                        CANCELAR <i class="material-icons right">clear</i>
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection