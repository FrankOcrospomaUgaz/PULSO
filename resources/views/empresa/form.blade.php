
<div class="col s12">

    <!-- Campo de texto para 'ruc' -->
<div class="input-field col s12 m4" style="display: flex; align-items: center; gap: 8px;">
   
        <input type="text" 
            name="ruc" 
            id="ruc" 
            class="validate @error('ruc') invalid @enderror" 
            value="{{ old('ruc', $empresa?->ruc) }}" 
            style="flex: 1;"> {{-- el input ocupa todo el espacio disponible --}}

        <a href="#!" id="buscarRUC" 
            class="btn pulse darken-2 white-text tooltipped" 
            data-tooltip="Buscar en la web"
            style="min-width: 50px; height: 40px; line-height: 40px; padding: 0;">
            <i class="material-icons">search</i>
        </a>
  

    <label for="ruc" class="{{ $empresa?->ruc ? 'active' : '' }}">{{ __('RUC') }}</label>
    {!! $errors->first('ruc', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
</div>

    <!-- Campo de texto para 'razonsocial' -->
    <div class="input-field col s12 m4">
        <input type="text" name="razon_social" id="razonsocial" class="validate @error('razon_social') invalid @enderror" value="{{ old('razon_social', $empresa?->razon_social) }}">
        <label for="razon_social" class="{{ $empresa?->razon_social ? 'active' : '' }}">{{ __('Razón Social') }}</label>
        {!! $errors->first('razon_social', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>
    <!-- Campo de texto para 'direccion' -->
    <div class="input-field col s12 m4">
        <input type="text" name="direccion" id="direccion" class="validate @error('direccion') invalid @enderror" value="{{ old('direccion', $empresa?->direccion) }}">
        <label for="direccion" class="{{ $empresa?->direccion ? 'active' : '' }}">{{ __('Dirección') }}</label>
        {!! $errors->first('direccion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>


    <!-- Campo de texto para 'email' -->
    <div class="input-field col s12 m4">
        <input type="email" name="email" id="email" class="validate @error('email') invalid @enderror" value="{{ old('email', $empresa?->email) }}">
        <label for="email"class="{{ $empresa?->email ? 'active' : '' }}">{{ __('Email') }}</label>
        {!! $errors->first('email', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de texto para 'telefono_fijo' -->
    <div class="input-field col s12 m4">
        <input type="text" name="telefono_fijo" id="telefono_fijo" class="validate @error('telefono_fijo') invalid @enderror" value="{{ old('telefono_fijo', $empresa?->telefono_fijo) }}">
        <label for="telefono_fijo"class="{{$empresa?->telefono_fijo ? 'active' : '' }}">{{ __('Teléfono Fijo') }}</label>
        {!! $errors->first('telefono_fijo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de texto para 'telefono_movil' -->
    <div class="input-field col s12 m4">
        <input type="text" name="telefono_movil" id="telefono_movil" class="validate @error('telefono_movil') invalid @enderror" value="{{ old('telefono_movil', $empresa?->telefono_movil) }}">
        <label for="telefono_movil"class="{{ $empresa?->telefono_movil ? 'active' : '' }}">{{ __('Teléfono Móvil') }}</label>
        {!! $errors->first('telefono_movil', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de texto para 'logo' -->
    <div class="input-field col s12 m4">
        <input type="text" name="logo" id="logo" class="validate @error('logo') invalid @enderror" value="{{ old('logo', $empresa?->logo) }}">
        <label for="logo"class="{{  $empresa?->logo ? 'active' : '' }}">{{ __('Logo') }}</label>
        {!! $errors->first('logo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>
</div>

    <!-- Botones de Acción -->
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light"><i class="material-icons left">send</i>{{ __('Guardar') }}</button>

            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>
