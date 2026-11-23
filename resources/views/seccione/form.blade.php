<div class="col s12">
    <!-- Campo de entrada para 'Descripción' -->
    <div class="input-field col s12 m6">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror"
            value="{{ old('descripcion', $seccione?->descripcion) }}">
        <label for="descripcion" class="{{ $seccione?->descripcion ? 'active' : '' }}">{{ __('Nombre') }}</label>
        {!! $errors->first(
            'descripcion',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>

    <!-- Campo de entrada para 'Abreviatura' -->
    <div class="input-field col s12 m6">
        <input type="text" name="abreviatura" id="abreviatura"
            class="validate @error('abreviatura') invalid @enderror"
            value="{{ old('abreviatura', $seccione?->abreviatura) }}">
        <label for="abreviatura" class="{{ $seccione?->abreviatura ? 'active' : '' }}">{{ __('Abreviatura') }}</label>
        {!! $errors->first(
            'abreviatura',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>

    <!-- Campo de entrada para 'Orden' -->
    <div class="input-field col s12 m6">
        <input type="number" name="orden" id="orden" class="validate @error('orden') invalid @enderror"
            value="{{ old('orden', $seccione?->orden) }}" min="0">
        <label for="orden" class="{{ $seccione?->orden ? 'active' : '' }}">{{ __('Orden') }}</label>
        {!! $errors->first('orden', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de entrada para 'Icono' -->
    <div class="input-field col s12 m6">
        <input type="text" name="icono" id="icono" class="validate @error('icono') invalid @enderror"
            value="{{ old('icono', $seccione?->icono) }}">
        <label for="icono" class="{{ $seccione?->icono ? 'active' : '' }}">{{ __('Icono') }}</label>
        {!! $errors->first('icono', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de entrada para 'Color' -->
    <div class="input-field col s12 m6">
        <input type="text" name="color" id="color" class="validate @error('color') invalid @enderror"
            value="{{ old('color', $seccione?->color) }}">
        <label for="color" class="{{ $seccione?->color ? 'active' : '' }}">{{ __('Color') }}</label>
        {!! $errors->first('color', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de entrada para 'Subtítulo' -->
    <div class="input-field col s12 m6">
        <input type="text" name="subtitulo" id="subtitulo" class="validate @error('subtitulo') invalid @enderror"
            value="{{ old('subtitulo', $seccione?->subtitulo) }}">
        <label for="subtitulo" class="{{ $seccione?->subtitulo ? 'active' : '' }}">{{ __('Subtitulo') }}</label>
        {!! $errors->first(
            'subtitulo',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>

    <!-- Campo de entrada para 'Comentario' -->
    <div class="input-field col s12 m6">
        <input type="text" name="comentario" id="comentario" class="validate @error('comentario') invalid @enderror"
            value="{{ old('comentario', $seccione?->comentario) }}">
        <label for="comentario" class="{{ $seccione?->comentario ? 'active' : '' }}">{{ __('Comentario') }}</label>
        {!! $errors->first(
            'comentario',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>

    <div class="col s12 m12 l6">
        <div class="input-field col s12">
            <i class="fa-solid fa-plus prefix"></i>
            <input class="autocomplete" autocomplete="off" id="agregarOpcionmenu" type="text"
                data-autocomplete-url="{{ route('autocomplete.menuopciones') }}"
                data-autocomplete-context="menu" onfocus="activarAutocompleteMenu()">
            <label for="agregarOpcionmenu">Opciones de menú</label>
        </div>
    
        <!-- Campo JSON para Detalles de Menús -->
        <input type="hidden" name="opciones_menu_json" id="opciones_menu_json" 
        value='{{ old('opciones_menu_json', isset($RelSeccionopcionmenu) ? json_encode($RelSeccionopcionmenu->pluck("opcionmenu_id")->toArray()) : "[]") }}'>
        
        <!-- Detalle de Opciones de Menú -->
        <div class="row without-top">
            <div class="col s12 m12">
                <table id="seccion-opciones-menu-table" class="highlight striped centered detalle-menus data-table">
                    <thead class="thead-custom">
                        <tr style="color: white">
                            <th>Opciones de menú</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($RelSeccionopcionmenu)
                            
                       
                        @foreach ($RelSeccionopcionmenu as $rel)
                            @php
                                $menu = $menuOpciones->firstWhere('id', $rel->opcionmenu_id);
                            @endphp
                            @if ($menu)
                                <tr data-menu-id="{{ $menu->id }}">
                                    <td> {{ $menu->tipoopcione->descripcion }} - {{ $menu->descripcion }}</td>
                                    <td>
                                        <button type="button" class="btn btn-floating btn-small red white-text tooltipped"
                                            data-tooltip="Quitar"
                                            onclick="quitarMenuDeTabla({{ $menu->id }});">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        @endisset
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>

<!-- Botones de acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light">
            <i class="material-icons left">send</i>{{ __('Guardar') }}
        </button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
