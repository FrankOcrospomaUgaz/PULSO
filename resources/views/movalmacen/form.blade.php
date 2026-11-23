<div class="row padding-1 p-1">
    <div class="col s12 m6">
<!-- Select para 'tipo de movimiento' -->
<div class="input-field col s12 m4">
    <select name="tipo_documento_visible" id="tipo_documento"
        class="validate @error('tipo_documento') invalid @enderror" {{ isset($tipo) ? 'disabled' : '' }}>
        
        @php
            // Obtener el ID del primer tipo de documento
            $primerTipoDocumentoId = $tipos_documento->first()->id ?? '';
        @endphp

        @foreach ($tipos_documento as $tipo_documento)
            <option value="{{ $tipo_documento->id }}"
{{ old('tipo_documento', $movalmacen->movimiento->tipodocumento_id ?? $tipodocumento_id ?? $primerTipoDocumentoId) == $tipo_documento->id ? 'selected' : '' }}>
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

<!-- Campo oculto para enviar el valor seleccionado de 'tipo_documento' -->
<input type="hidden" name="tipo_documento"
    value="{{ old('tipo_documento', $movalmacen->movimiento->tipodocumento_id ?? $tipodocumento_id ?? $primerTipoDocumentoId) }}">



        <!-- Campo de fecha para 'fecha' deshabilitado en el formulario -->
        <div class="input-field col s12 m3">
            <i class="fa-solid fa-calendar-day prefix"></i>
            <input type="text" class="datepicker" name="fecha_visible" id="fecha"
                value="{{ old('fecha', optional($movalmacen->movimiento->fecha ?? '')->format('d/m/Y') ?? now()->format('d/m/Y')) }}"
                disabled>
            <label for="fecha" class="active">{{ __('Fecha') }}</label>
            {!! $errors->first('fecha', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo oculto para enviar la fecha -->
        <input type="hidden" name="fecha" id="fecha_oculta"
            value="{{ old('fecha', optional($movalmacen->movimiento->fecha ?? '')->format('Y-m-d') ?? now()->format('Y-m-d')) }}">

        <!-- Select para 'subalmacén' -->
        <div class="input-field col s12 m5">
            <i class="fa-solid fa-warehouse prefix"></i>
            <select name="subalmacen_id" id="subalmacen_id" class="validate @error('subalmacen_id') invalid @enderror">
                <option value="" {{ old('subalmacen_id', $movalmacen->subalmacen_id) == '' ? 'selected' : '' }}>
                    Ninguno</option>
                @foreach ($subalmacenes as $subalmacen)
                    <option value="{{ $subalmacen->id }}"
{{ old('subalmacen_id', $movalmacen->subalmacen_id ?? '') == $subalmacen->id ? 'selected' : '' }}>
{{ $subalmacen->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="subalmacen_id">{{ __('Subalmacén') }}</label>
            {!! $errors->first(
                'subalmacen_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

<!-- Campo de autocompletado para persona -->
<div class="input-field col s12 m5">
    <input type="text" id="persona_autocomplete"
        class="autocomplete validate @error('persona_id') invalid @enderror" 
        placeholder="Seleccionar Persona"
        data-autocomplete-url="{{ route('autocomplete.personas') }}"
        value="{{ old('persona_id') === null ? '0 - VARIOS CLIENTES' : ($personaSeleccionada ?? '') }}" disabled>
    
    <input type="hidden" name="persona_id" id="persona_id"
        value="{{ old('persona_id', $movalmacen->movimiento->persona_id ?? '26') }}">
    
    <label for="persona_autocomplete" class="active">{{ __('Persona') }}</label>

    {!! $errors->first('persona_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
</div>

        <div class="input-field col s12 m1">
            <button class="btn red left tooltipped" id="btnQuitarPersona" data-tooltip="Deseleccionar persona"
                type="button" onclick="eliminarPersona();" style="border-radius: 99em"><i
                    class="fa-solid fa-user-slash"></i></button>
        </div>

        <!-- Select para 'responsable' -->
        <div class="input-field col s12 m6">
            <i class="fa-solid fa-user-tie prefix"></i>
            <select name="responsable_id" id="responsable_id"
                class="validate @error('responsable_id') invalid @enderror">
                @foreach ($responsables as $responsable)
<option value="{{ $responsable->id }}"
{{ old('responsable_id', $movalmacen->movimiento->responsable_id ?? '') == $responsable->id ? 'selected' : '' }}>
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

        <!-- Campo de texto para 'comentario' -->
        <div class="input-field col s12">
            <i class="fa-solid fa-note-sticky prefix"></i>
            <textarea name="comentario" id="comentario"
                class="materialize-textarea validate @error('comentario') invalid @enderror">{{ old('comentario', $movalmacen->movimiento->comentario ?? '') }}</textarea>
            <label for="comentario">{{ __('Notas al movimiento de almacén') }}</label>
            {!! $errors->first(
                'comentario',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>
    </div>

    <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">

    <!-- Tabla para detalle de productos -->
    <div class="col s12 m6">
        <div class="input-field col s10">
            <i class="fa-solid fa-bag-shopping prefix"></i>
            <input type="text" name="buscar_producto" id="producto_autocomplete"
                class="autocomplete validate"
                data-autocomplete-url="{{ route('autocomplete.productos') }}" onfocus=""
                autocomplete="off" />
            <input type="hidden" name="producto_id" id="producto_id">
            <label for="producto_autocomplete" class="active">{{ __('Productos') }}</label>
{{-- 
            <i class="fa-solid fa-bag-shopping prefix"></i>
            <input type="text" name="buscar_producto" id="producto_autocomplete" 
                class="autocomplete validate" placeholder="Seleccionar Producto"
                data-autocomplete='@json($productosAutocomplete)'>
            <input type="hidden" name="producto_id" id="producto_id">
            <label for="producto_autocomplete" class="active">{{ __('Productos') }}</label> --}}
        </div>

        <div class="input-field col s2">
            <button type="button" class="btn darken-2 btn-small" onclick="agregarProducto()"
                style="border-radius: 99em">
                <i class="material-icons">add</i>
            </button>
        </div>

        <input type="hidden" name="detalles_productos_json" id="detalles_productos_json">

        <!-- Tabla con detalles de productos ya agregados -->
        <div class="table-responsive">
            <table class="highlight striped centered " id="data-table">
                <thead class="thead-custom">
                    <tr style="color: white">
                        <th>Producto</th>
                        <th>Unidad</th>
                        <th>Cantidad</th>
                        <th>Notas</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="detalle-productos">
                    @foreach ($detalles ?? [] as $detalle)
                        <tr data-producto-id="{{ $detalle->producto_id }}">
                            <td class="producto-nombre"> {{ $detalle->producto->codigo }} -
                                {{ $detalle->producto->descripcion }}</td>
                            <td class="unidad-id" data-unidad-id="{{ $detalle->unidad_id }}">
                                {{ $detalle->unidad->descripcion }}</td>
                            <td class="cantidad">{{ $detalle->cantidad }}</td>
                            <td class="notas">{{ $detalle->comentario }}</td>
                            <td>
                                <button type="button"
                                    onclick="eliminarProducto(this, '{{ $detalle->producto_id }}')"
                                    class="btn btn-small red">
                                    <i class="material-icons">delete</i>
                                </button>
                            </td>
                        </tr>
                  
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
</div>


<!-- Botones de Acción -->
<div class="row padding-1 p-1">
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer right-align">
            <button type="submit" class="btn darken-2"><i
                    class="material-icons left">save</i>{{ __('Guardar') }}</button>
            <a href="{{ route('movalmacen.index') }}" class="modal-close waves-effect waves-green btn-flat btn-retroceder">{{ __('Cancelar') }}</a>
        </div>
    </div>
</div>

<!-- Modal para Detalle del Producto -->
<div id="producto-modal" class="modal modal-bottom-sheet" style="width: 100%; border-radius: 0%; bottom: 0;">
    <div class="modal-content">
        <!-- Título del producto -->
        <h5 id="producto-nombre" class="center-align"></h5>

        <div class="row">
            <!-- Controles de cantidad -->
            <div class="col s12 m12 l3">
                <div class="row valign-wrapper">
                    <div class="col s4 center">
                        <button type="button" class="btn-floating btn-small darken-2"
                            onclick="ajustarCantidad(-1)">-</button>
                    </div>
                    <div class="col s4 center">
                        <input type="number" id="cantidad_producto" value="1" min="1"
                            class="center-align">
                    </div>
                    <div class="col s4 center">
                        <button type="button" class="btn-floating btn-small darken-2"
                            onclick="ajustarCantidad(1)">+</button>
                    </div>
                </div>
            </div>

            <!-- Select para unidad -->
            <div class="input-field col s12 m2">
                <label for="unidad_producto" class="active">Unidad</label>
                <select id="unidad_producto">
                    @foreach ($unidades as $unidad)
                        <option value="{{ $unidad->id }}"
                            {{ old('unidad_id', $movalmacen?->unidad_id) == $unidad->id ? 'selected' : '' }}>
                            {{ $unidad->descripcion }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Campo de notas y lote en una sola fila -->
            <div class="input-field col s12 m2">
                <textarea id="notas_producto" class="materialize-textarea"></textarea>
                <label for="notas_producto">Notas</label>
            </div>
            <div class="input-field col s12 m2">
                <input type="text" id="lote_producto">
                <label for="lote_producto">Lote</label>
            </div>
            <!-- Campo de fecha de vencimiento -->
            <div class="input-field col s12 m2">
                <input type="date" class="datepicker" id="fecha_vencimiento_producto">
                <label for="fecha_vencimiento_producto">Fecha de vencimiento</label>
            </div>
        </div>
    </div>

    <!-- Footer de Modal con botones de acción alineados a la derecha -->
    <div class="modal-footer right-align">
        <a class="modal-close btn darken-2" onclick="agregarProductoAlDetalle()">Guardar</a>
        <a href="#!" class="modal-close btn-flat">{{ __('Cancelar') }}</a>
    </div>
</div>

