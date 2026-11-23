<!-- FORMULARIO -->
<div class="row padding-1 p-1">
    <div class="col s12">
        <input type="hidden" name="sucursal_id" id="sucursal_id"
            value="{{ old('sucursal_id', $plantillaContrato?->sucursal_id ?? $sucursal_id ?? '') }}">
        <input type="hidden" name="variables" id="variables" value="{{ $variables }}">
        <div class="input-field col s12 m6">
            <input type="text" name="titulo" id="titulo" class="validate @error('titulo') invalid @enderror"
                value="{{ old('titulo', $plantillaContrato?->titulo) }}">
            <label for="titulo"
                class="{{ old('titulo', $plantillaContrato?->titulo) ? 'active' : '' }}">{{ __('Título de Plantilla') }}</label>
            {!! $errors->first('titulo', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>

        <div class="input-field col s12 m6">
            <input type="text" name="descripcion" id="descripcion"
                class="validate @error('descripcion') invalid @enderror"
                value="{{ old('descripcion', $plantillaContrato?->descripcion) }}">
            <label for="descripcion"
                class="{{ old('descripcion', $plantillaContrato?->descripcion) ? 'active' : '' }}">{{ __('Descripción') }}</label>
            {!! $errors->first('descripcion', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>

        <div class="input-field col s12 m6">
            <select name="tipo_ambiente_id" id="tipo_ambiente_id"
                class="validate @error('tipo_ambiente_id') invalid @enderror">
                <option value="" disabled selected>{{ __('Seleccione un tipo de ambiente') }}</option>
                @foreach ($tiposAmbiente as $tipo)
                    <option value="{{ $tipo->id }}" {{ old('tipo_ambiente_id', $plantillaContrato?->tipo_ambiente_id) == $tipo->id ? 'selected' : '' }}>
{{ $tipo->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="tipo_ambiente_id">{{ __('Tipo de Ambiente') }}</label>
            {!! $errors->first('tipo_ambiente_id', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>

        <div class="input-field col s12 m6">
            <div class="file-field input-field">
                <div class="btn">
                    <span>Subir Documento</span>
                    <input type="file" name="documento" id="file-upload" accept=".docx,.pdf,.txt">
                </div>
                <div class="file-path-wrapper">
                    <input class="file-path validate" type="text" placeholder="Sube .docx, .pdf o .txt">
                </div>
            </div>
        </div>


        <div class="input-field col s12">
            <label for="contenido_html" class="active">{{ __('Contenido HTML de la Plantilla') }}</label>
            <textarea name="contenido_html" id="contenido_html"
                class="materialize-textarea validate tinymce-editor @error('contenido_html') invalid @enderror"
                rows="10">{{ old('contenido_html', $plantillaContrato?->contenido_html) }}</textarea>
            {!! $errors->first('contenido_html', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>
    </div>
</div>

<!-- BOTONES -->
<div class="row padding-1 p-1">
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer right-align">
            <button type="submit" class="btn waves-effect waves-light">
                <i class="material-icons left">save</i>{{ __('Guardar') }}
            </button>
            <a href="#!"  onclick="back();" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>
</div>