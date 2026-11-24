@php($editing = (bool) $row)
    <div class="main-body">
        <div class="row" style="margin-top:0">
            <div class="col s12">
                <div class="card-content"><span class="card-title"><i class="fa-solid {{ $editing ? 'fa-pen-to-square' : 'fa-plus' }} left"></i>{{ $editing ? 'Editar' : 'Nuevo' }} {{ strtolower($title) }}</span></div>
                <div class="card-body bg-white">

                        @if($errors->any())
                            <div class="card-panel red lighten-5 red-text text-darken-3">Revisa los campos indicados antes de guardar.</div>
                        @endif

                        <form id="form" method="POST">
                            <input type="hidden" id="form-action-url" value="{{ $editing ? route($type.'.update',$row->id) : route($type.'.store') }}">
                            @csrf @if($editing) @method('PUT') @endif

                            @if($type==='servicios-clinicos')
                                <div class="row">
                                    <div class="input-field col s12 m8"><input id="nombre" name="nombre" required maxlength="150" value="{{ old('nombre',$row->nombre??'') }}"><label for="nombre" class="{{ old('nombre',$row->nombre??'') ? 'active' : '' }}">Nombre del servicio</label>@error('nombre')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m4"><input id="duracion_minutos" type="number" min="5" max="480" name="duracion_minutos" required value="{{ old('duracion_minutos',$row->duracion_minutos??30) }}"><label for="duracion_minutos" class="active">Duración (minutos)</label>@error('duracion_minutos')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12"><textarea id="descripcion" name="descripcion" class="materialize-textarea">{{ old('descripcion',$row->descripcion??'') }}</textarea><label for="descripcion" class="{{ old('descripcion',$row->descripcion??'') ? 'active' : '' }}">Descripción</label>@error('descripcion')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m4"><input id="precio" type="number" min="0" step="0.01" name="precio" required value="{{ old('precio',$row->precio??'0.00') }}"><label for="precio" class="active">Precio (S/)</label>@error('precio')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="col s12 m8" style="padding-top:24px"><label><input type="checkbox" name="activo" value="1" @checked(old('activo',$row->activo??true))><span>Servicio activo</span></label></div>
                                </div>
                            @elseif($type==='profesionales')
                                <div class="row">
                                    <div class="input-field col s12 m4"><select id="tipo_documento" name="tipo_documento">@foreach(['DNI'=>'DNI','CE'=>'Carné de extranjería','PASAPORTE'=>'Pasaporte'] as $value=>$label)<option value="{{ $value }}" @selected(old('tipo_documento',$row->tipo_documento??'DNI')===$value)>{{ $label }}</option>@endforeach</select><label for="tipo_documento">Tipo de documento</label></div>
                                    <div class="input-field col s12 m4"><input id="numero_documento" name="numero_documento" maxlength="30" value="{{ old('numero_documento',$row->numero_documento??'') }}"><label for="numero_documento" class="{{ old('numero_documento',$row->numero_documento??'') ? 'active' : '' }}">Número de documento</label>@error('numero_documento')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m4"><input id="registro_profesional" name="registro_profesional" maxlength="60" value="{{ old('registro_profesional',$row->registro_profesional??'') }}"><label for="registro_profesional" class="{{ old('registro_profesional',$row->registro_profesional??'') ? 'active' : '' }}">Registro profesional</label>@error('registro_profesional')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m6"><input id="nombres" name="nombres" required maxlength="120" value="{{ old('nombres',$row->nombres??'') }}"><label for="nombres" class="{{ old('nombres',$row->nombres??'') ? 'active' : '' }}">Nombres</label>@error('nombres')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m6"><input id="apellidos" name="apellidos" required maxlength="120" value="{{ old('apellidos',$row->apellidos??'') }}"><label for="apellidos" class="{{ old('apellidos',$row->apellidos??'') ? 'active' : '' }}">Apellidos</label>@error('apellidos')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m6"><input id="especialidad" name="especialidad" required maxlength="150" value="{{ old('especialidad',$row->especialidad??'') }}"><label for="especialidad" class="{{ old('especialidad',$row->especialidad??'') ? 'active' : '' }}">Especialidad o función</label>@error('especialidad')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m3"><input id="telefono" name="telefono" maxlength="40" value="{{ old('telefono',$row->telefono??'') }}"><label for="telefono" class="{{ old('telefono',$row->telefono??'') ? 'active' : '' }}">Teléfono</label>@error('telefono')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m3"><input id="email" type="email" name="email" maxlength="150" value="{{ old('email',$row->email??'') }}"><label for="email" class="{{ old('email',$row->email??'') ? 'active' : '' }}">Correo</label>@error('email')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="col s12" style="padding-top:8px"><label><input type="checkbox" name="activo" value="1" @checked(old('activo',$row->activo??true))><span>Profesional activo</span></label></div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="input-field col s12 m3"><select id="tipo_documento" name="tipo_documento">@foreach(['DNI'=>'DNI','CE'=>'Carné de extranjería','PASAPORTE'=>'Pasaporte','SIN_DOCUMENTO'=>'Sin documento'] as $value=>$label)<option value="{{ $value }}" @selected(old('tipo_documento',$row->tipo_documento??'DNI')===$value)>{{ $label }}</option>@endforeach</select><label for="tipo_documento">Tipo de documento</label></div>
                                    <div class="input-field col s12 m3"><input id="numero_documento" name="numero_documento" maxlength="30" value="{{ old('numero_documento',$row->numero_documento??'') }}"><label for="numero_documento" class="{{ old('numero_documento',$row->numero_documento??'') ? 'active' : '' }}">Número de documento</label>@error('numero_documento')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m3"><input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento',isset($row->fecha_nacimiento) ? $row->fecha_nacimiento->format('Y-m-d') : '') }}"><label for="fecha_nacimiento" class="active">Fecha de nacimiento</label>@error('fecha_nacimiento')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m3"><select id="sexo" name="sexo">@foreach([''=>'No especificado','F'=>'Femenino','M'=>'Masculino','OTRO'=>'Otro'] as $value=>$label)<option value="{{ $value }}" @selected(old('sexo',$row->sexo??'')===$value)>{{ $label }}</option>@endforeach</select><label for="sexo">Sexo</label>@error('sexo')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m6"><input id="nombres" name="nombres" required maxlength="120" value="{{ old('nombres',$row->nombres??'') }}"><label for="nombres" class="{{ old('nombres',$row->nombres??'') ? 'active' : '' }}">Nombres</label>@error('nombres')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m6"><input id="apellidos" name="apellidos" required maxlength="120" value="{{ old('apellidos',$row->apellidos??'') }}"><label for="apellidos" class="{{ old('apellidos',$row->apellidos??'') ? 'active' : '' }}">Apellidos</label>@error('apellidos')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m4"><input id="telefono" name="telefono" maxlength="40" value="{{ old('telefono',$row->telefono??'') }}"><label for="telefono" class="{{ old('telefono',$row->telefono??'') ? 'active' : '' }}">Teléfono</label>@error('telefono')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m4"><input id="email" type="email" name="email" maxlength="150" value="{{ old('email',$row->email??'') }}"><label for="email" class="{{ old('email',$row->email??'') ? 'active' : '' }}">Correo</label>@error('email')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m4"><input id="direccion" name="direccion" maxlength="255" value="{{ old('direccion',$row->direccion??'') }}"><label for="direccion" class="{{ old('direccion',$row->direccion??'') ? 'active' : '' }}">Dirección</label>@error('direccion')<small class="red-text">{{ $message }}</small>@enderror</div>
                                </div>
                                <div class="divider"></div>
                                <div class="row" style="padding-top:14px">
                                    <div class="col s12"><span class="card-title">Datos del padre, madre o tutor</span></div>
                                    <div class="input-field col s12 m6"><input id="tutor_nombres" name="tutor_nombres" maxlength="180" value="{{ old('tutor_nombres',$row->tutor_nombres??'') }}"><label for="tutor_nombres" class="{{ old('tutor_nombres',$row->tutor_nombres??'') ? 'active' : '' }}">Nombre completo</label>@error('tutor_nombres')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m6"><input id="tutor_documento" name="tutor_documento" maxlength="30" value="{{ old('tutor_documento',$row->tutor_documento??'') }}"><label for="tutor_documento" class="{{ old('tutor_documento',$row->tutor_documento??'') ? 'active' : '' }}">Documento</label>@error('tutor_documento')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m6"><input id="tutor_telefono" name="tutor_telefono" maxlength="40" value="{{ old('tutor_telefono',$row->tutor_telefono??'') }}"><label for="tutor_telefono" class="{{ old('tutor_telefono',$row->tutor_telefono??'') ? 'active' : '' }}">Teléfono</label>@error('tutor_telefono')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12 m6"><input id="tutor_email" type="email" name="tutor_email" maxlength="150" value="{{ old('tutor_email',$row->tutor_email??'') }}"><label for="tutor_email" class="{{ old('tutor_email',$row->tutor_email??'') ? 'active' : '' }}">Correo</label>@error('tutor_email')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="col s12"><input type="hidden" name="acepta_notificaciones" value="0"><label><input type="checkbox" name="acepta_notificaciones" value="1" @checked(old('acepta_notificaciones',$row->acepta_notificaciones??false))><span>Autoriza recibir recordatorios de citas y saludos de cumple-mes</span></label>@error('acepta_notificaciones')<small class="red-text">{{ $message }}</small>@enderror</div>
                                    <div class="input-field col s12" style="margin-top:24px"><textarea id="observaciones" name="observaciones" class="materialize-textarea">{{ old('observaciones',$row->observaciones??'') }}</textarea><label for="observaciones" class="{{ old('observaciones',$row->observaciones??'') ? 'active' : '' }}">Observaciones</label>@error('observaciones')<small class="red-text">{{ $message }}</small>@enderror</div>
                                </div>
                            @endif

                            <div class="mt20 mt-2"><div class="modal-footer">
                                <button type="submit" class="btn waves-effect waves-light"><i class="fa-solid fa-floppy-disk left"></i>{{ $editing ? 'Guardar cambios' : 'Guardar' }}</button>
                                <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cancelar</a>
                            </div></div>
                        </form>
                </div>
            </div>
        </div>
    </div>
