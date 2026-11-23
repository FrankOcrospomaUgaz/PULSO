    <div class="col s12">
        <div class="input-field col s12 m4">
            <input type="text" name="ruc" id="ruc" class="validate @error('ruc') invalid @enderror" value="{{ old('ruc', $sucursal?->ruc) }}">
            <label for="ruc" class="{{ $sucursal?->ruc ? 'active' : '' }}">{{ __('RUC') }}</label>
            <a href="#!" id="buscarRUC" class="btn-floating btn-small waves-effect waves-light green" style="position: absolute; right: 10px; top: 20px;">
                <i class="material-icons">search</i>
            </a>
            {!! $errors->first('ruc', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'razonsocial' -->
        <div class="input-field col s12 m4">
            <input type="text" name="razonsocial" id="razonsocial" class="validate @error('razonsocial') invalid @enderror" value="{{ old('razonsocial', $sucursal?->razonsocial) }}">
            <label for="razonsocial" class="{{ $sucursal?->razonsocial ? 'active' : '' }}">{{ __('Razón Social') }}</label>
            {!! $errors->first('razonsocial', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>
        <!-- Campo de texto para 'nombrecomercial' -->
        <div class="input-field col s12 m4">
            <input type="text" name="nombrecomercial" id="nombrecomercial" class="validate @error('nombrecomercial') invalid @enderror" value="{{ old('nombrecomercial', $sucursal?->nombrecomercial) }}">
            <label for="nombrecomercial" class="{{ $sucursal?->nombrecomercial ? 'active' : '' }}">{{ __('Nombre Comercial') }}</label>
            {!! $errors->first('nombrecomercial', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'direccion' -->
        <div class="input-field col s12 m4">
            <input type="text" name="direccion" id="direccion" class="validate @error('direccion') invalid @enderror" value="{{ old('direccion', $sucursal?->direccion) }}">
            <label for="direccion" class="{{ $sucursal?->direccion ? 'active' : '' }}">{{ __('Dirección') }}</label>
            {!! $errors->first('direccion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>




        <!-- Campo de texto para 'email' -->
        <div class="input-field col s12 m4">
            <input type="email" name="email" id="email" class="validate @error('email') invalid @enderror" value="{{ old('email', $sucursal?->email) }}">
            <label for="email" class="{{ $sucursal?->email ? 'active' : '' }}">{{ __('Email') }}</label>
            {!! $errors->first('email', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'telefonofijo' -->
        <div class="input-field col s12 m2">
            <input type="text" name="telefonofijo" id="telefonofijo" class="validate @error('telefonofijo') invalid @enderror" value="{{ old('telefonofijo', $sucursal?->telefonofijo) }}">
            <label for="telefonofijo" class="{{ $sucursal?->telefonofijo ? 'active' : '' }}">{{ __('Teléfono Fijo') }}</label>
            {!! $errors->first('telefonofijo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'telefonomovil' -->
        <div class="input-field col s12 m2">
            <input type="text" name="telefonomovil" id="telefonomovil" class="validate @error('telefonomovil') invalid @enderror" value="{{ old('telefonomovil', $sucursal?->telefonomovil) }}">
            <label for="telefonomovil" class="{{ $sucursal?->telefonomovil ? 'active' : '' }}">{{ __('Teléfono Móvil') }}</label>
            {!! $errors->first('telefonomovil', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>


        @php
        $selectedUbigeoId = old('ubigeo_id') ?? ($sucursal->ubigeo->ubigeo->ubigeo_id ?? null);
    @endphp
    
    <div class="input-field col s12 m3">
        <select name="departamento" id="departamento">
            @foreach ($departamentos as $dep)
                <option value="{{ $dep->id }}"
                    {{ ($selectedUbigeoId == $dep->id) ? 'selected' : '' }}>
                    {{ $dep->descripcion }}
                </option>
            @endforeach
        </select>
        <label for="departamento">{{ __('Departamento') }}</label>
    </div>
    
        <!-- Provincia -->
        @php
        $selectedProvinciaId = old('ubigeo_id') ?? ($sucursal->ubigeo->ubigeo_id ?? null);
    @endphp
    
    <div class="input-field col s12 m3">
        <select name="provincia" id="provincia">
            @foreach ($provincias as $prov)
                <option value="{{ $prov->id }}"
                    {{ ($selectedProvinciaId == $prov->id) ? 'selected' : '' }}>
                    {{ $prov->descripcion }}
                </option>
            @endforeach
        </select>
        <label for="provincia">{{ __('Provincia') }}</label>
    </div>
    

        <!-- Distrito -->
        <div class="input-field col s12 m3">
            <select name="ubigeo_id" id="distrito" class="validate @error('ubigeo_id') invalid @enderror">
                @foreach ($distritos as $dist)
                <option value="{{ $dist->id }}" 
    {{ (old('ubigeo_id') !== null ? old('ubigeo_id') : ($sucursal?->ubigeo_id)) == $dist->id ? 'selected' : '' }}>
    {{ $dist->descripcion }}
</option>
                @endforeach
            </select>
            <label for="ubigeo_id">{{ __('Distrito') }} *</label>
            {!! $errors->first('ubigeo_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- empresa_id -->

        <input type="hidden" name="empresa_id" value="{{$empresa_id}}">
        <div class="col s12 m6 l3">
            <div class="file-field input-field input-group-field">
                <div class="btn left">
                    <span><i class="fa-solid fa-circle-up"></i></span>
                    <input id="logo" name="logo" type="file" accept=".png,.jpg,.jpeg">
                </div>
                <div class="file-path-wrapper left-wrapper input-field">
                    <input id="logo_txt" class="file-path validate" type="text" placeholder="No se ha seleccionado ningún archivo">
                    <span class="helper-text" id="logo_spn" data-error="" data-success=""></span>
                </div>
            </div>
        </div>


        <!-- Campo de texto para 'tipo' -->
        <div class="input-field col s12 m3">
            <select name="tipo" id="tipo">
                <option value="Venta" {{ old('tipo', $sucursal?->tipo) == 'Venta' ? 'selected' : '' }}>Venta</option>
                <option value="Compra" {{ old('tipo', $sucursal?->tipo) == 'Compra' ? 'selected' : '' }}>Compra</option>
            </select>
            <label for="tipo">{{ __('Tipo') }}</label>
        </div>
        
        <!-- Facturación electrónica y Venta mayorista -->
        <div class="input-field col s12 m3">
            <label>
                <input type="checkbox" name="facturacion" id="facturacion" {{ old('facturacion', $sucursal?->facturacion) == 'Si' ? 'checked' : '' }} />
                <span>{{ __('Facturación Electr ónica') }}</span>
            </label>
        </div>
        <div class="input-field col s12 m3">

            <label>
                <input type="checkbox" name="venta_mayorista" id="venta_mayorista" {{ old('venta_mayorista', $sucursal?->venta_mayorista) == 'S' ? 'checked' : '' }} />
                <span>{{ __('Venta Mayorista') }}</span>
            </label>
        </div>




        <!-- Campo de texto para 'business_intelligence_url' -->
        <div class="input-field col s12 m3">
            <input type="text" name="business_intelligence_url" id="business_intelligence_url" class="validate @error('business_intelligence_url') invalid @enderror" value="{{ old('business_intelligence_url', $sucursal?->business_intelligence_url) }}">
            <label for="business_intelligence_url" class="{{ $sucursal?->business_intelligence_url ? 'active' : '' }}">{{ __('Enlace Business Intelligence URL') }}</label>
            {!! $errors->first('business_intelligence_url', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer">
            <button type="submit" class="btn waves-effect waves-light"><i class="material-icons left">send</i>{{ __('Guardar') }}</button>
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>
