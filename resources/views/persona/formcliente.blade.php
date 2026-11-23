<div class="main-body">
    <div class="row without-bottom" style="margin-bottom: -20px;">
        <div class="col s12">
            <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-plus left"></i>Personas | Nuevo
                </p>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="form-url" value="{{ route('persona.store') }}">
                    <div class="col s12">
                        <div class="row without-bottom" style="margin-bottom: -20px;">
                            <!-- Condicionar la visibilidad de estos campos según la opción seleccionada -->
                            @if ($opcion == 'documento')
                                <div class="input-field col s12 m2">
                                    <select name="tipopersona" id="tipopersona"
                                        class="validate @error('tipopersona') invalid @enderror"
                                        onchange="cambiarId(this)">
<option value="Natural"
    {{ old('tipopersona', $persona->personamaestro?->tipopersona) == 'Natural' ? 'selected' : '' }}>
DNI
</option>
 <option value="Juridico"
 {{ old('tipopersona', $persona->personamaestro?->tipopersona) == 'Juridico' ? 'selected' : '' }}>
RUC
</option>
 <option value="Carnet de Extranjería"
 {{ old('tipopersona', $persona->personamaestro?->tipopersona) == 'Carnet de Extranjería' ? 'selected' : '' }}>
Carnet de Extranjería
 </option>
  <option value="Pasaporte"
  {{ old('tipopersona', $persona->personamaestro?->tipopersona) == 'Pasaporte' ? 'selected' : '' }}>
Pasaporte
</option>
                                    </select>

                                    <label for="tipopersona">{{ __('Tipo') }}</label>
                                    {!! $errors->first(
                                        'tipopersona',
                                        '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                    ) !!}
                                </div>
                                <div class="input-field col s12 m2" style="position: relative;">
                                    <input type="text" name="nrodoc" id="nrodoc"
                                        class="validate @error('nrodoc') invalid @enderror"
                                        value="{{ old('nrodoc', $persona->personamaestro?->nrodoc ?? '') }}"
                                        autocomplete="off">

                                    <label for="nrodoc"
                                        class="{{ $persona->personamaestro?->nrodoc ? 'active' : '' }}">
                                        {{ __('DNI') }}
                                    </label>



                                    {!! $errors->first('nrodoc', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                                </div>
                                <div class="input-field col s12 m1" style="position: relative;">

                                    <a href="#!" id="buscarDNI" class="btn pulse darken-2 white-text tooltipped "
                                        data-tooltip="Buscar en la web"
                                        style="padding: 0 1.2rem; height: 40px; width: 60px; line-height: 40px; position: absolute;  top: 10px;">
                                        <i class="material-icons">search</i>
                                    </a>
                                </div>
                                 <!-- Campo de texto para 'nombres' -->
                            <div class="input-field col s12 m4">
                                <input type="text" name="nombres" id="nombres"
                                    class="validate @error('nombres') invalid @enderror"
                                    value="{{ old('nombres', $persona?->nombres) }}">
                                <label for="nombres"
                                    class="{{ $persona?->nombres ? 'active' : '' }}">{{ __('Nombres') }}</label>
                                {!! $errors->first(
                                    'nombres',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>

                            <div class="col s12 m6 l2" id="divFechaIns" style="display: none">
                                <div class="input-field">
                                    <input class="submit-form validate @error('fechanac') invalid @enderror"
                                        id="fechains" name="fechains" type="date"
                                        value="{{ old('fechains', $persona->personamaestro?->fechanac) }}">
                                    <label for="fechains"
                                        class="{{ $persona->personamaestro?->fechains ? 'active' : '' }}">{{ __('Fecha de Inscrip.') }}</label>
                                    {!! $errors->first(
                                        'fechains',
                                        '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                    ) !!}
                                </div>
                            </div>

                            <!-- Campo de texto para 'apellidos' -->
                            <div class="input-field col s12 m3" id="apellidos-div">
                                <input type="text" name="apellidos" id="apellidos"
                                    value="{{ old('apellidos', $persona?->apellidos ?? ' ') }}">
                                <label for="apellidos"
                                    class="{{ $persona?->apellidos ? 'active' : '' }}">{{ __('Apellidos') }}</label>
                                {!! $errors->first(
                                    'apellidos',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>
                            @endif

                           
                            @if ($opcion !== 'documento')
                             <!-- Campo de texto para 'nombres' -->
                            <div class="input-field col s12 m4">
                                <input type="text" name="nombres" id="nombres"
                                    class="validate @error('nombres') invalid @enderror"
                                    value="{{ old('nombres', $persona?->nombres) }}">
                                <label for="nombres"
                                    class="{{ $persona?->nombres ? 'active' : '' }}">{{ __('Nombres') }}</label>
                                {!! $errors->first(
                                    'nombres',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>

                           

                            <!-- Campo de texto para 'apellidos' -->
                            <div class="input-field col s12 m4" id="apellidos-div">
                                <input type="text" name="apellidos" id="apellidos"
                                    value="{{ old('apellidos', $persona?->apellidos ?? ' ') }}">
                                <label for="apellidos"
                                    class="{{ $persona?->apellidos ? 'active' : '' }}">{{ __('Apellidos') }}</label>
                                {!! $errors->first(
                                    'apellidos',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>
                               <!-- Campo de texto para 'direccion' -->
                            <div class="input-field col s12 m4">
                                <input type="text" name="direccion" id="direccion"
                                    class="validate @error('direccion') invalid @enderror"
                                    value="{{ old('direccion', $persona?->direccion) }}">
                                <label for="direccion"
                                    class="{{ $persona?->direccion ? 'active' : '' }}">{{ __('Dirección') }}</label>
                                {!! $errors->first(
                                    'direccion',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>

                            <!-- Campo de texto para 'email' -->
                            <div class="input-field col s12 m4">
                                <input type="email" name="email" id="email"
                                    class="validate @error('email') invalid @enderror"
                                    value="{{ old('email', $persona?->email) }}">
                                <label for="email"
                                    class="{{ $persona?->email ? 'active' : '' }}">{{ __('E-mail') }}</label>
                                {!! $errors->first('email', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                            </div>
                                
                            @endif
                        </div>
                        <div class="row without-bottom" id="divDatosAdicionales"
                            style=" display: flex; flex-wrap: wrap; align-items: flex-end;">
                            @if ($opcion == 'documento')
                                <div class="col s12 m6 l2" id="divGenero">

                                    <p class="without-top">
                                        <label for="sexoM">
                                            <input id="sexoM" name="sexo" type="radio" value="M" checked
                                                {{ old('sexo', $persona->personamaestro?->sexo) == 'M' ? 'checked' : '' }}>
                                            <span>Masculino</span>
                                        </label>
                                    </p>
                                    <p class="without-top">
                                        <label for="sexoF">
                                            <input id="sexoF" name="sexo" type="radio" value="F"
                                                {{ old('sexo', $persona->personamaestro?->sexo) == 'F' ? 'checked' : '' }}>
                                            <span>Femenino</span>
                                        </label>
                                    </p>
                                </div>
                                <div class="col s12 m6 l2" id="divFechaNac">
                                    <div class="input-field">
                                        <input class="submit-form validate @error('fechanac') invalid @enderror"
                                            id="fechanac" name="fechanac" type="date"
                                            value="{{ old('fechanac', $persona->personamaestro?->fechanac) }}">
                                        <label for="fechanac"
                                            class="{{ $persona->personamaestro?->fechanac ? 'active' : '' }}">{{ __('Fecha nac.') }}</label>
                                        {!! $errors->first(
                                            'fechanac',
                                            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                        ) !!}
                                    </div>
                                </div>
                                 <!-- Campo de texto para 'direccion' -->
                            <div class="input-field col s12 m4">
                                <input type="text" name="direccion" id="direccion"
                                    class="validate @error('direccion') invalid @enderror"
                                    value="{{ old('direccion', $persona?->direccion) }}">
                                <label for="direccion"
                                    class="{{ $persona?->direccion ? 'active' : '' }}">{{ __('Dirección') }}</label>
                                {!! $errors->first(
                                    'direccion',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>

                            <!-- Campo de texto para 'email' -->
                            <div class="input-field col s12 m4">
                                <input type="email" name="email" id="email"
                                    class="validate @error('email') invalid @enderror"
                                    value="{{ old('email', $persona?->email) }}">
                                <label for="email"
                                    class="{{ $persona?->email ? 'active' : '' }}">{{ __('E-mail') }}</label>
                                {!! $errors->first('email', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                            </div>
                            @endif
                           

                        </div>
                     
                        <div class="row without-bottom" id="divOtrosDatos">
                            <!-- Campo de texto para 'telefonofijo' -->
                            <div class="input-field col s12 m2">
                                <input type="text" name="telefonofijo" id="telefonofijo"
                                    class="validate @error('telefonofijo') invalid @enderror"
                                    value="{{ old('telefonofijo', $persona?->telefonofijo) }}">
                                <label for="telefonofijo"
                                    class="{{ $persona?->telefonofijo ? 'active' : '' }}">{{ __('Tel. fijo') }}</label>
                                {!! $errors->first(
                                    'telefonofijo',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>

                            <!-- Campo de texto para 'telefonomovil' -->
                            <div class="input-field col s12 m2">
                                <input type="text" name="telefonomovil" id="telefonomovil"
                                    class="validate @error('telefonomovil') invalid @enderror"
                                    value="{{ old('telefonomovil', $persona?->telefonomovil) }}">
                                <label for="telefonomovil"
                                    class="{{ $persona?->telefonomovil ? 'active' : '' }}">{{ __('Tel. móvil') }}</label>
                                {!! $errors->first(
                                    'telefonomovil',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>


                            <input type="hidden" name="sucursal_id" id="sucursal_id" value="{{ $sucursal_id }}">

                           <input type="hidden" name="accion" id="accion" value="{{ $accion }}">
                            @if ($accion == 'CHECKIN')
                                <input type="hidden" name="habitacion_id" value="{{ $habitacion_id }}">
                            @endif
                            @if ($accion == 'EDITCHECKIN')
                                <input type="hidden" name="habitacion_id" value="{{ $habitacion_id }}">
                            @endif
                            @if ($accion == 'CHECKOUT')
                                <input type="hidden" name="habitacion_id" value="{{ $habitacion_id }}">
                            @endif
                            @if ($accion == 'CONVERTIR')
                                <input type="hidden" name="movventa_id" value="{{ $movventa_id }}">
                            @endif

                            <!-- Departamento -->
                            <div class="input-field col s12 m2">
                                <select name="departamento" id="departamento">
                                    @foreach ($departamentos as $dep)
                                        <option value="{{ $dep->id }}"
                                            {{ (old('ubigeo_id') !== null
                                                ? old('ubigeo_id')
                                                : ($persona?->distrito_id
                                                    ? $persona->ubigeo->ubigeo->ubigeo_id
                                                    : $sucursal?->ubigeo->ubigeo->ubigeo_id)) == $dep->id
                                                ? 'selected'
                                                : '' }}>
{{ $dep->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="departamento">{{ __('Departamento') }}</label>
                            </div>

                            <!-- Provincia -->
                            <div class="input-field col s12 m2">
                                <select name="provincia" id="provincia">
                                    @foreach ($provincias as $prov)
                                        <option value="{{ $prov->id }}"
                                            {{ (old('ubigeo_id') !== null
                                                ? old('ubigeo_id')
                                                : ($persona?->ubigeo?->ubigeo?->ubigeo_id
                                                    ? $persona->ubigeo->ubigeo->ubigeo_id
                                                    : $sucursal?->ubigeo->ubigeo_id)) == $prov->id
                                                ? 'selected'
                                                : '' }}>
{{ $prov->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="provincia">{{ __('Provincia') }}</label>
                            </div>
                            <!-- Distrito -->
                            <div class="input-field col s12 m2">
                                <select name="distrito_id" id="distrito"
                                    class="validate @error('distrito_id') invalid @enderror">
                                    @foreach ($distritos as $dist)
                                        <option value="{{ $dist->id }}"
                                            {{ (old('distrito_id') !== null
                                                ? old('distrito_id')
                                                : ($persona?->distrito_id
                                                    ? $persona->distrito_id
                                                    : $sucursal?->ubigeo_id)) == $dist->id
                                                ? 'selected'
                                                : '' }}>
{{ $dist->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="ubigeo_id">{{ __('Distrito') }}</label>
                                {!! $errors->first(
                                    'ubigeo_id',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>
                        </div>
                            {{-- BLOQUE DE ROLES Y PERSONA NO GRATA DEBAJO --}}
   
                        @isset($roles)
                            <div class="row">
                                    <!-- Mostrar solo el rol de Cliente (rol_id == 3) marcado -->
                                    @foreach ($roles as $rol)
                                        @if ($rol->id == 3)
                                            <div class="col s12 m6 l2">
                                                <p class="without-tb left-align">
                                                    <label class="truncate" for="rol_id{{ $rol->id }}">
                                                        <input class="filled-in" id="rol_id{{ $rol->id }}"
                                                            name="rol_id[]" type="checkbox" value="{{ $rol->id }}"
                                                            checked>

                                                        <span>{{ $rol->descripcion }}</span>
                                                    </label>
                                                </p>
                                            </div>
                                        @endif
                                    @endforeach
                                 <div class="col s12 m6 l4">
                        <p class="without-tb left-align">
                            <label class="truncate" for="no_grato">
                                <input class="filled-in red-text" id="no_grato" name="no_grato" type="checkbox"
                                    value="S" {{ old('no_grato', $persona->no_grato ?? false) ? 'checked' : '' }}>
                                <span class="red-text text-darken-2">Persona no grata</span>
                                <span class="helper-text" id="no_grato_spn" data-error="" data-success=""></span>
                            </label>
                        </p>
                    </div>
                            </div>
                        @endisset

                    </div>

    <input type="hidden" name="opcion" value="{{ $opcion }}">
    <input type="hidden" name="tipo" value="{{ $tipo }}">
                   
                </form>
                     <!-- Botones de Acción -->
                    <div class="col s12 mt20 mt-2">
                        <div class="modal-footer">
                            <button class="btn waves-effect waves-light" onclick="registrarPersona()">
                                <i class="material-icons left">send</i>{{ __('Guardar') }}
                            </button>
                            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
                        </div>
                    </div>
            </div>
        </div>
    </div>

</div>
</div>
