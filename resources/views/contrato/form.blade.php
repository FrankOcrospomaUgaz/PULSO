<div class="row padding-1 p-1">
    <div class="col s12">

        <input type="hidden" name="sucursal_id" id="sucursal_id" value="{{ old('sucursal_id', $sucursal_id ?? '') }}">
        <input type="hidden" name="pagoanticipado" id="pagoanticipado" value="{{  $pagoanticipado ?? '' }}">

        <div class="input-field col s12 m4">
            <input type="text" name="numero_contrato" id="numero_contrato"
                class="validate @error('numero_contrato') invalid @enderror"
                value="{{ old('numero_contrato', $contrato->numero_contrato ?? ($numeroContratoGenerado ?? '')) }}">
            <label for="numero_contrato"
                class="{{ old('numero_contrato', $contrato->numero_contrato ?? ($numeroContratoGenerado ?? '')) ? 'active' : '' }}">{{ __('Número Contrato') }}</label>
            {!! $errors->first('numero_contrato', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}

        </div>

        @php
            $hoy = \Carbon\Carbon::now()->format('Y-m-d');
            $unMesDespues = \Carbon\Carbon::now()->addMonth()->format('Y-m-d');
            $diaActual = \Carbon\Carbon::now()->day;
        @endphp

        <div class="input-field col s12 m4">
            <input type="date" name="fecha_inicio" id="fecha_inicio"
                class="validate @error('fecha_inicio') invalid @enderror"
                value="{{ old('fecha_inicio', $contrato->fecha_inicio ?? $hoy) }}"
                oninput="verificarCamposCompromisos()">
            <label for="fecha_inicio"
                class="{{ old('fecha_inicio', $contrato->fecha_inicio ?? $hoy) ? 'active' : '' }}">{{ __('Fecha Inicio') }}</label>
            {!! $errors->first('fecha_inicio', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>

        <div class="input-field col s12 m4">
            <input type="date" name="fecha_fin" id="fecha_fin" class="validate @error('fecha_fin') invalid @enderror"
                value="{{ old('fecha_fin', $contrato->fecha_fin ?? $unMesDespues) }}"
                oninput="verificarCamposCompromisos()">
            <label for="fecha_fin"
                class="{{ old('fecha_fin', $contrato->fecha_fin ?? $unMesDespues) ? 'active' : '' }}">{{ __('Fecha Fin') }}</label>
            {!! $errors->first('fecha_fin', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>




        <!-- Select de edificio -->
        <div class="input-field col s12 m6">
            <select name="edificio_id" id="edificio_id" class="validate @error('edificio_id') invalid @enderror"
                onchange="filtrarAmbientesPorEdificio()">
                <option value="" disabled selected>Selecciona un edificio</option>
                @foreach ($edificios as $edificio)
                    <option value="{{ $edificio->id }}"
                        {{ old('edificio_id', $contrato->edificio_id ?? '') == $edificio->id ? 'selected' : '' }}>
{{ $edificio->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="edificio_id">{{ __('Edificio') }}</label>
            {!! $errors->first('edificio_id', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>
        <!-- Select de ambiente -->
        <div class="input-field col s12 m6">
            <select name="ambiente_id" id="ambiente_id" class="validate @error('ambiente_id') invalid @enderror"
                onchange="obtenerDatosAmbiente()">

                <option value="" disabled selected>Selecciona un ambiente</option>
                @foreach ($ambientes as $ambiente)
                    <option value="{{ $ambiente->id }}"
                        {{ old('ambiente_id', $contrato->ambiente_id ?? '') == $ambiente->id ? 'selected' : '' }}>
{{ $ambiente->numero }} - {{ $ambiente->piso->nombre ?? '' }}
                    </option>
                @endforeach
            </select>
            <label for="ambiente_id">{{ __('Ambiente') }}</label>
            {!! $errors->first('ambiente_id', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>
<!-- Campo de Arrendador con Autocomplete -->
<div class="input-field col s12 m5">
    <i class="material-icons prefix">person</i>
    <input type="hidden" name="arrendador_id" id="arrendador_id"
        value="{{ old('arrendador_id', $contrato->arrendador_id ?? '') }}">
    <input type="text" id="arrendador_autocomplete"
        class="autocomplete validate @error('arrendador_id') invalid @enderror"
        placeholder="Seleccione un arrendador"
        data-autocomplete-url="{{ route('autocomplete.personas') }}"
        value="{{ isset($contrato->arrendador_id) ? "{$contrato->arrendador->personamaestro->nrodoc} - {$contrato->arrendador->apellidos} {$contrato->arrendador->nombres}" : '' }}"
        autocomplete="off" required>
    <label for="arrendador_autocomplete" class="active">Arrendador</label>
    {!! $errors->first(
        'arrendador_id',
        '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
    ) !!}
</div>

<div class="input-field col s12 m1">
    <!-- Botón para quitar arrendador -->
    <button class="btn red left tooltipped" id="btnQuitarArrendador"
        data-tooltip="Deseleccionar arrendador" type="button" onclick="eliminarArrendador();"
        style="border-radius: 99em; {{ isset($arrendadorSeleccionado) ? 'display: block;' : 'display: none;' }}">
        <i class="fa-solid fa-user-slash"></i>
    </button>

    <!-- Botón para agregar nuevo arrendador -->
    <button
        href="{{ route('cliente.create', [$sucursal->id ?? 1, 'CONTRATO']) }}?es_principal=true&tipo=ARRENDADOR"
        class="btn teal right tooltipped btn-crear" type="button"
        data-tooltip="Agregar Nuevo Arrendador" id="btnNuevoArrendador"
        data-target="modal-nuevo-arrendador"
        style="border-radius: 99em;">
        <i class="material-icons">person_add</i>
    </button>
</div>

<!-- Campo de Inquilino con Autocomplete -->
<div class="input-field col s12 m5">
    <i class="material-icons prefix">person</i>
    <input type="hidden" name="persona_id" id="persona_id"
        value="{{ old('persona_id', $contrato->persona_id ?? '') }}">
    <input type="text" id="persona_autocomplete"
        class="autocomplete validate @error('persona_id') invalid @enderror"
        placeholder="Seleccione un arrendatario"
        data-autocomplete-url="{{ route('autocomplete.personas') }}"
        value="{{ isset($contrato->persona_id) ? "{$contrato->arrendatario->personamaestro->nrodoc} - {$contrato->arrendatario->apellidos} {$contrato->arrendatario->nombres}" : '' }}"
        autocomplete="off" required>
    <label for="persona_autocomplete" class="active">Arrendatario</label>
    {!! $errors->first(
        'persona_id',
        '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
    ) !!}
</div>

<div class="input-field col s12 m1">
    <!-- Botón para quitar inquilino -->
    <button class="btn red left tooltipped" id="btnQuitarCliente"
        data-tooltip="Deseleccionar inquilino" type="button" onclick="eliminarPersona();"
        style="border-radius: 99em; {{ isset($personaSeleccionada) ? 'display: block;' : 'display: none;' }}">
        <i class="fa-solid fa-user-slash"></i>
    </button>

    <!-- Botón para agregar nuevo inquilino -->
    <button
        href="{{ route('cliente.create', [$sucursal->id ?? 1, 'CONTRATO']) }}?es_principal=true"
        class="btn teal right tooltipped btn-crear" type="button"
        data-tooltip="Agregar Nuevo Inquilino" id="btnNuevaPersona"
        data-target="modal-nueva-persona"
        style="border-radius: 99em;">
        <i class="material-icons">person_add</i>
    </button>
</div>



{{-- Modal para vista previa PDF --}}
<div id="modalVistaPreviaPDF" class="modal modal-fixed-footer">
    <div class="modal-content">
       
        <iframe id="iframePDF" src="" style="width:100%; height:80vh;" frameborder="0"></iframe>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close btn-flat">Cerrar</a>
    </div>
</div>

        <div class="input-field col s12 m3">
            <input type="number" name="dia_pago" id="dia_pago" class="validate @error('dia_pago') invalid @enderror"
                value="{{ old('dia_pago', $contrato->dia_pago ?? $diaActual) }}"
                oninput="verificarCamposCompromisos()">
            <label for="dia_pago"
                class="{{ old('dia_pago', $contrato->dia_pago ?? $diaActual) ? 'active' : '' }}">{{ __('Día de Pago') }}</label>
            {!! $errors->first('dia_pago', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>
        <div class="input-field col s12 m3">
            <input type="number" step="0.01" name="monto" id="monto"
                class="validate @error('monto') invalid @enderror" value="{{ old('monto', $contrato->monto ?? '') }}"
                oninput="verificarCamposCompromisos()">
            <label for="monto"
                class="{{ old('monto', $contrato->monto ?? '') ? 'active' : '' }}">{{ __('Monto') }}</label>
            {!! $errors->first('monto', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>

        <div class="input-field col s12 m3">
            <input type="number" step="0.01" name="garantia" id="garantia"
                class="validate @error('garantia') invalid @enderror"
                value="{{ old('garantia', $contrato->garantia ?? '') }}" oninput="verificarCamposCompromisos()">
            <label for="garantia"
                class="{{ old('garantia', $contrato->garantia ?? '') ? 'active' : '' }}">{{ __('Garantía') }}</label>
            {!! $errors->first('garantia', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>
<div class="input-field col s12 m3">
            <button type="button" class="btn blue darken-2" id="btn-generar-compromisos"
                onclick="generarCompromisos()" disabled>
                <i class="material-icons left">add</i> Generar Compromisos de Pago
            </button>
        </div>

        
       
        <input type="hidden" name="compromisos_pago" id="compromisos_pago">

        <div class="col s12 mt-2" id="tabla-compromisos-container" style="display: none;">
            <table class="striped responsive-table">
                <thead>
                    <tr>
                        <th>N° Cuota</th>
                        <th>Fecha Vencimiento</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody id="tabla-compromisos-body"></tbody>
            </table>
        </div>


        <div class="input-field col s12 m6">
            <input type="text" name="descripcion" id="descripcion"
                class="validate @error('descripcion') invalid @enderror"
                value="{{ old('descripcion', $contrato->descripcion ?? '') }}">
            <label for="descripcion"
                class="{{ old('descripcion', $contrato->descripcion ?? '') ? 'active' : '' }}">{{ __('Descripción') }}</label>
            {!! $errors->first('descripcion', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>

        <div class="input-field col s12 m6">
            <input type="text" name="informacion_adicional" id="informacion_adicional"
                class="validate @error('informacion_adicional') invalid @enderror"
                value="{{ old('informacion_adicional', $contrato->informacion_adicional ?? '') }}">
            <label for="informacion_adicional"
                class="{{ old('informacion_adicional', $contrato->informacion_adicional ?? '') ? 'active' : '' }}">{{ __('Información Adicional') }}</label>
            {!! $errors->first(
                'informacion_adicional',
                '<span class="helper-text red-text"><strong>:message</strong></span>',
            ) !!}
        </div>
        <div class="input-field col s12 m3">
            <label>
                <input type="hidden" name="cubre_luz" value="0">
                <input type="checkbox" name="cubre_luz" id="cubre_luz" value="1"
                    {{ old('cubre_luz', $contrato->cubre_luz ?? false) ? 'checked' : '' }}>
                <span>{{ __('Cubre Luz') }}</span>
            </label>
        </div>

        <div class="input-field col s12 m2">
            <label>
                <input type="hidden" name="cubre_agua" value="0">
                <input type="checkbox" name="cubre_agua" id="cubre_agua" value="1"
                    {{ old('cubre_agua', $contrato->cubre_agua ?? false) ? 'checked' : '' }}>
                <span>{{ __('Cubre Agua') }}</span>
            </label>
        </div>


    </div>
</div>

<!-- Botones -->
<div class="row padding-1 p-1">
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer right-align">
            {{-- Botón para abrir el modal --}}

    <button type="button" class="btn red" onclick="mostrarVistaPreviaPDF()">
        <i class="material-icons left">picture_as_pdf</i>Vista previa
    </button>

            <button type="submit" class="btn waves-effect waves-light">
                <i class="material-icons left">send</i>{{ __('Guardar') }}
            </button>
            <a href="#!"  onclick="back();" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>
</div>

