<div class="col s12">
    <!-- Campo de texto para 'descripcion' -->
    <div class="input-field col s12 m6">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror"
            value="{{ old('descripcion', $pasarelapago?->descripcion) }}">
        <label for="descripcion" class="{{ $pasarelapago?->descripcion ? 'active' : '' }}">{{ __('Descripción') }}</label>
        {!! $errors->first(
            'descripcion',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>

    <!-- Campo de texto para 'orden' -->
    <div class="input-field col s12 m6">
        <input type="number" name="orden" id="orden" class="validate @error('orden') invalid @enderror"
        value="{{ old('orden', $pasarelapago?->orden) ?? 0 }}">
        <label for="orden" class="active">{{ __('Orden') }}</label>
        {!! $errors->first('orden', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>
    <input type="hidden" id="id_pasarela" value="{{ isset($pasarelapago->id) ? $pasarelapago->id : 0 }}">

    <div class="col s12 m12 l6">
        <div class="input-field col s12">
            <i class="fa-solid fa-plus prefix"></i>
            <input class="autocomplete" id="agregarMedioPago" type="text"
            data-autocomplete-url="{{ route('autocomplete.mediopagos') }}" autocomplete="off">
            <label for="agregarMedioPago">Medios de pago</label>
        </div>

        <div class="col s12">
            <table class="bordered display responsive nowrap" id="mediopagoTable">
                <thead class="thead-custom">
                    <tr>
                        <th class="center">Medios de pago</th>
                        <th class="center"></th>
                    </tr>
                </thead>
                <tbody id="mediopagoBody">
                    <!-- Mostrar los medios de pago relacionados -->
                    @isset($medioPagoRelacionados)
                        @foreach ($mediopagos as $mediopago)
                            @if (in_array($mediopago->id, $medioPagoRelacionados))
                                <tr data-medio-id="{{ $mediopago->id }}">
                                    <td class="center">{{ $mediopago->descripcion }}</td>
                                    <td class="center">
                                        <a href="#" class="btn red btn-small remove-medio-pago">
                                            <i class="material-icons">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach

                        <!-- Mensaje de que no hay medios de pago relacionados -->
                        @if (empty($medioPagoRelacionados))
                            <tr id="mediopagoComentario">
                                <td class="center" colspan="2">Vincular un nuevo medio de pago</td>
                            </tr>
                        @endif
                    @else
                        <tr id="mediopagoComentario">
                            <td class="center" colspan="2">Vincular un nuevo medio de pago</td>
                        </tr>
                    @endisset

                </tbody>
            </table>
        </div>
        <!-- Campo oculto para los IDs de los medios de pago seleccionados -->
        <input type="hidden" name="medio_pago_ids" id="medio_pago_ids">
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
