<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content" style="padding-left: 12px">
                <span class="card-title" style="display: flex; align-items: center;">
                    <i class="fa-solid fa-file-circle-check left"></i>
                    <span style="flex: 1; text-align: center;">
                        Convertir a comprobante de venta electrónico
                    </span>
                </span>
            </div>

            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf

                    <!-- Campo oculto para la URL de acción dinámica -->
                    <input type="hidden" id="form-action-url" value="{{ route('movventa.convertirstore', $movventa->id) }}">
                    <div class="col s12">
                        <div class="row">

                            <!-- Advertencia -->
                            <div class="col s12">
                                <p id="movventa-info" class="center red-text semibold">
                                    ADVERTENCIA: El ticket de venta se anulará y se generará un nuevo comprobante
                                    electrónico tributable con los mismos datos.
                                </p>
                            </div>

                            <!-- Tipo de documento -->
                            <div class="col s12 m6 l4 offset-l1">
                                <div class="input-field">
                                    <i class="fa-solid fa-receipt prefix"></i>
                                    <select name="tipodocumento_id" id="tipodocumento_id">
                                        <option value="4">Boleta de venta</option>
                                        <option value="10">Factura de venta</option>
                                    </select>
                                    <label for="tipodocumento_id">Tipo</label>
                                    <span class="helper-text" id="tipodocumento_id_spn" data-error=""
                                        data-success=""></span>
                                </div>
                            </div>

                            <!-- Cliente -->
                            <div class="input-field col s12 m4">
                                <input type="hidden" name="persona_id" id="persona_id"
                                    value="{{ old('persona_id', isset($personaCreada) ? $personaCreada->id : $movventa->movimiento->persona_id ?? '26') }}">

                                <input type="text" id="persona_autocomplete"
                                    class="autocomplete validate @error('persona_id') invalid @enderror"
                                    placeholder="Seleccionar Persona" data-autocomplete-url="{{ route('autocomplete.personas') }}"
                                    value="{{ isset($personaCreada)
                                        ? "{$personaCreada->personamaestro->nrodoc} - {$personaCreada->apellidos} {$personaCreada->nombres}"
                                        : (old('persona_id') !== null
                                            ? $personaSeleccionada ?? ''
                                            : (isset($movventa) && isset($movventa->movimiento->persona)
                                                ? "{$movventa->movimiento->Persona->personamaestro->nrodoc} - {$movventa->movimiento->persona}"
                                                : '0 - VARIOS CLIENTES')) }}"
                                    {{ isset($personaCreada) ? '' : 'disabled' }}>

                                <label for="persona_autocomplete" class="active">{{ __('Persona') }}</label>

                                {!! $errors->first(
                                    'persona_id',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>


                            <!-- Botón para quitar cliente -->
                            <div class="input-field col s12 m1">
                                <button class="btn red left tooltipped" id="btnQuitarCliente"
                                    data-tooltip="Deseleccionar cliente" type="button" onclick="eliminarPersona();"
                                    style="border-radius: 99em; {{ isset($personaCreada) ? '' : 'display: block;' }}"><i class="fa-solid fa-user-slash"></i></button>
                              <!-- Botón para agregar nuevo cliente directo (por documento) -->
                                        <button
                                        type="button"
                                        class="btn teal right tooltipped btn-crear-cliente-directo"
                                        data-tooltip="Agregar Nuevo Cliente" id="btnNuevaPersona"
                                        data-sucursal-id="{{ $sucursal->id }}"
                                        data-accion="CONVERTIR"
                                        data-movventa-id="{{ $movventa->id }}"
                                        style="border-radius: 99em; {{ isset($personaCreada) ? 'display: none;' : 'display: none;' }}">
                                        <i class="material-icons">person_add</i>
                                        </button>

                            </div>

                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="col s12 mt20 mt-2">
                        <div class="modal-footer">
                            <button type="submit" class="btn waves-effect waves-light">
                                <i class="material-icons left">check_circle</i>{{ __('Convertir') }}
                            </button>
                            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

