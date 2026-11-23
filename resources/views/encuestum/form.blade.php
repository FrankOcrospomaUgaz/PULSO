<div class="modal-content">
    <div class="row without-tb" id="modal-form">
        <div class="col s12">
            <div class="row">
                <!-- Fecha -->
                <div class="col s12 m6 l2 input-field">
                    <input id="fecha" name="fecha" type="text"
                        value="{{ old('fecha', isset($encuestum) ? \Carbon\Carbon::parse($encuestum->fecha)->format('d/m/Y') : date('d/m/Y')) }}"
                        readonly>
                    <label class="active" for="fecha">Fecha</label>
                    <span class="helper-text" id="fecha_spn"></span>
                </div>
                <!-- Fecha de fin -->
                <div class="col s12 m6 l3 input-field">
                    <i class="fa-solid fa-calendar-day prefix"></i>
                    <input id="fechavencimiento" name="fechavencimiento" type="date"
                        value="{{ old('fechavencimiento', isset($encuestum) ? \Carbon\Carbon::parse($encuestum->fechavencimiento)->format('Y-m-d') : (isset($encuestum) ? '' : now()->format('Y-m-d'))) }}">
                    <label for="fechavencimiento">Fecha de fin</label>
                    <span class="helper-text" id="fechavencimiento_spn"></span>
                </div>

                <!-- Hora de fin -->
                <div class="col s12 m6 l3 input-field">
                    <i class="fa-solid fa-clock prefix"></i>
                    <input id="horavencimiento" name="horavencimiento" type="time"
                        value="{{ old('horavencimiento', isset($encuestum) ? \Carbon\Carbon::parse($encuestum->fechavencimiento)->format('H:i') : (isset($encuestum) ? '' : '00:00')) }}">
                    <label for="horavencimiento">Hora de fin</label>
                    <span class="helper-text" id="horavencimiento_spn"></span>
                </div>


                <!-- Nombre -->
                <div class="col s12 m6 l4 input-field">
                    @php
                        $nombreValue = old('nombre', $encuestum->nombre ?? '');
                    @endphp
                    <input class="submit-form" id="nombre" name="nombre" type="text" value="{{ $nombreValue }}">
                    <label for="nombre" class="{{ !empty($nombreValue) ? 'active' : '' }}">Nombre</label>
                    <span class="helper-text" id="nombre_spn"></span>
                </div>

                <!-- Descripción -->
                <div class="col s12 m6 l6 input-field">
                    @php
                        $descripcionValue = old('descripcion', $encuestum->descripcion ?? '');
                    @endphp
                    <textarea class="materialize-textarea" id="descripcion" name="descripcion" rows="1" oninput="autoResize(this);">{{ $descripcionValue }}</textarea>
                    <label for="descripcion" class="{{ !empty($descripcionValue) ? 'active' : '' }}">Descripción</label>
                    <span class="helper-text" id="descripcion_spn"></span>
                </div>

                <!-- Mensaje final -->
                <div class="col s12 m6 l6 input-field">
                    @php
                        $mensajeFinalValue = old('mensaje_finalizacion', $encuestum->mensaje_finalizacion ?? '');
                    @endphp
                    <textarea class="materialize-textarea" id="mensaje_finalizacion" name="mensaje_finalizacion" rows="1"
                        oninput="autoResize(this);">{{ $mensajeFinalValue }}</textarea>
                    <label for="mensaje_finalizacion" class="{{ !empty($mensajeFinalValue) ? 'active' : '' }}">Mensaje
                        final</label>
                    <span class="helper-text" id="mensaje_finalizacion_spn"></span>
                </div>

    <!-- Checkbox Validacion -->
    <div class="col s12 m6 l6">
        <p class="left-align p-checkbox" style="margin-block-start:0 !important;">
            <label for="validacion">
                <input class="filled-in" id="validacion" name="validacion" type="checkbox" value="S"
                    {{ old('validacion', isset($encuestum) && $encuestum->validacion == 'S' ? 'checked' : '') }}>
                <span>Validación de cliente</span>
                <span class="helper-text" id="validacion_spn"></span>
            </label>
        </p>
    </div>

    <!-- Checkbox Imprimible -->
    <div class="col s12 m6 l6">
        <p class="left-align p-checkbox" style="margin-block-start:0 !important;">
            <label for="imprimible">
                <input class="filled-in" id="imprimible" name="imprimible" type="checkbox" value="S"
                    {{ old('imprimible', isset($encuestum) && $encuestum->imprimible == 'S' ? 'checked' : '') }}>
                <span>Imprimible</span>
                <span class="helper-text" id="imprimible_spn"></span>
            </label>
        </p>
    </div>


            </div>
        </div>
    </div>
</div>

<!-- Botones de Acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light">
            <i class="material-icons left">send</i>{{ __('Guardar') }}
        </button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
