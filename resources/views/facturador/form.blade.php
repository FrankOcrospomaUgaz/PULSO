<div class="row">
    <div class="col s12">
        <!-- Campo de texto para 'empresa_facturador_id' -->
        <div class="input-field col s12 m3">
            <input type="number" name="empresa_facturador_id" id="empresa_facturador_id"
                class="validate @error('empresa_facturador_id') invalid @enderror"
                value="{{ old('empresa_facturador_id', $facturador?->empresa_facturador_id) }}">
            <label for="empresa_facturador_id" class="{{ $facturador?->empresa_facturador_id ? 'active' : '' }}">
                {{ __('Empresa ID') }}
            </label>
            {!! $errors->first('empresa_facturador_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'porcentaje_igv' -->
        <div class="input-field col s12 m2">
            <input type="number" step="0.01" name="porcentaje_igv" id="porcentaje_igv"
                class="validate @error('porcentaje_igv') invalid @enderror"
                value="{{ old('porcentaje_igv', $facturador?->porcentaje_igv) ?? 18 }}">
            <label for="porcentaje_igv" class="active">{{ __('Porcentaje IGV') }}</label>
            {!! $errors->first('porcentaje_igv', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'username_wsdl' -->
        <div class="input-field col s12 m3">
            <input type="text" name="username_wsdl" id="username_wsdl"
                class="validate @error('username_wsdl') invalid @enderror"
                value="{{ old('username_wsdl', $facturador?->username_wsdl) }}" autocomplete="off">
            <label for="username_wsdl" class="{{ $facturador?->username_wsdl ? 'active' : '' }}">
                {{ __('Usuario WSDL') }}
            </label>
            {!! $errors->first('username_wsdl', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'password_wsdl' -->
        <div class="input-field col s12 m3">
            <input type="password" name="password_wsdl" id="password_wsdl"
                class="validate @error('password_wsdl') invalid @enderror"
                value="{{ old('password_wsdl', $facturador?->password_wsdl) }}" autocomplete="off">
            <label for="password_wsdl" class="{{ $facturador?->password_wsdl ? 'active' : '' }}">
                {{ __('Contraseña WSDL') }}
            </label>
            {!! $errors->first('password_wsdl', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>
    </div>

    <div class="col s12">
        <!-- Campo para 'username_sunat' -->
        <div class="input-field col s12 m3">
            <input type="text" name="username_sunat" id="username_sunat"
                class="validate @error('username_sunat') invalid @enderror"
                value="{{ old('username_sunat', $facturador?->username_sunat) }}">
            <label for="username_sunat" class="{{ $facturador?->username_sunat ? 'active' : '' }}">
                {{ __('Usuario SUNAT') }}
            </label>
            {!! $errors->first('username_sunat', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo para 'password_sunat' -->
        <div class="input-field col s12 m3">
            <input type="password" name="password_sunat" id="password_sunat"
                class="validate @error('password_sunat') invalid @enderror"
                value="{{ old('password_sunat', $facturador?->password_sunat) }}">
            <label for="password_sunat" class="{{ $facturador?->password_sunat ? 'active' : '' }}">
                {{ __('Contraseña SUNAT') }}
            </label>
            {!! $errors->first('password_sunat', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo para 'modo_autenticacion' -->
        <div class="input-field col s12 m3">
            <select name="modo_autenticacion" id="modo_autenticacion"
                class="validate @error('modo_autenticacion') invalid @enderror">
                <option value="" disabled selected>Selecciona el modo</option>
                <option value="E" {{ old('modo_autenticacion', $facturador?->modo_autenticacion) == 'E' ? 'selected' : '' }}>E</option>
                <option value="P" {{ old('modo_autenticacion', $facturador?->modo_autenticacion) == 'P' ? 'selected' : '' }}>P</option>
            </select>
            <label for="modo_autenticacion">{{ __('Modo de Autenticación') }}</label>
            {!! $errors->first('modo_autenticacion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>
        <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">

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
</div>
