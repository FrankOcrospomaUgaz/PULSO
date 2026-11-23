<div class="col s12">
    <!-- Fecha programada -->
    @php
    $fechaActual = now()->format('Y-m-d\TH:i');
@endphp

<div class="input-field col s12 m3">
    <i class="material-icons prefix">event</i>
    <input type="datetime-local" id="fecha_programada" name="fecha_programada" value="{{ $fechaActual }}" required>
    <label for="fecha_programada" class="active">Fecha y Hora Programada</label>
</div>

    
    <!-- Observaciones -->
    <div class="input-field col s12 m9">
        <i class="material-icons prefix">note</i>
        <textarea id="observaciones" name="observaciones" class="materialize-textarea"></textarea>
        <label for="observaciones">Observaciones</label>
    </div>

    <!-- Asignación de habitaciones por bloques (pisos) -->
    <div class="col s12">

        @foreach ($pisos as $piso)
            <div class="card hoverable">
                <div class="card-content">
                    <h6>
                        <label>
                            <input type="checkbox" class="filled-in piso-checkbox" data-piso="{{ $piso->id }}" onclick="togglePisoSelection(this)">
                            <span><strong>{{ $piso->nombre }}</strong></span>
                        </label>
                    </h6>
                    <div class="input-field">
                        <select class="select2 empleados piso-empleado" name="empleados_piso[{{ $piso->id }}]">
                            <option value="" disabled {{ !isset($empleadoPorPiso[$piso->id]) ? 'selected' : '' }}>Asignar a un empleado</option>
                            @foreach ($empleados as $empleado)
                                <option value="{{ $empleado->id }}"
                                    {{ isset($empleadoPorPiso[$piso->id]) && $empleadoPorPiso[$piso->id] == $empleado->id ? 'selected' : '' }}>
{{ $empleado->nombres }}
                                </option>
                            @endforeach
                        </select>
                        
                    </div>
                </div>

                <div class="card-action row" style="margin: 20px">
                    @foreach ($habitaciones->where('piso_id', $piso->id) as $habitacion)
                        <div class="col s12 m4 l2 habitacion-item">
                            <div class="card custom-card card-habitacion {{ strtolower($habitacion->situacion) }}"
                                data-habitacion-id="{{ $habitacion->id }}">
                                <div class="card-body" style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
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
                                            onclick="toggleHabitacionSelectionHouse(this)"
                                            {{ in_array($habitacion->id, $habitacionesSeleccionadas ?? []) ? 'checked' : '' }}>
                                        

                                            <span style="color: white">Asignar</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
<input type="hidden" name="habitaciones" id="habitaciones_seleccionadas"
       value='@json($habitacionesSeleccionadas ?? [])'>


<!-- Botón de enviar -->
<div class="row center-align">
    <button type="submit" class="btn-large waves-effect waves-light teal form-retroceder">
        <i class="material-icons left">save</i> Guardar Jornada
    </button>
</div>
