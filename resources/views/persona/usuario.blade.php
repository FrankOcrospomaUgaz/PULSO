

<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">  
                    <i class="fa-solid fa-user-tie left"></i>Personal | Usuario
                </span>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="form-action-url" value="{{ route('persona.verUsuario.update', [$persona_id, $sucursal_id]) }}">
                    <input type="hidden" name="menuopcion_id_actual" value="{{ $permisousu->menuopcion_id ?? '' }}">
                <div class="row" id="divUsuario">
                    <div class="col s12 m6 l2">
                        <div class="input-field">
                            <input id="username" name="username" type="text" value="{{$usuario->name}}" readonly>
                            <label class="active" for="username">Nombre de usuario</label>
                            <span class="helper-text" id="username_spn" data-error="" data-success=""></span>
                        </div>
                    </div>
                    <!-- Select para Perfil -->
                    <div class="col s12 m6 l3">
                        <div class="input-field">
                            <select name="perfil_id" id="perfil_id">
                              <option value="" disabled>Seleccionar perfil</option>
@foreach ($perfiles as $perfil)
<option value="{{ $perfil->id }}" {{ (string) $usuario->perfil_id === (string) $perfil->id ? 'selected' : '' }}>
{{ $perfil->descripcion }}
</option>
@endforeach
                            </select>
                            <label for="perfil_id">Perfil</label>
                            <span class="helper-text" id="perfil_id_spn" data-error="" data-success=""></span>
                        </div>
                    </div>
                    <!-- Select para Opción de Menú -->
                    <div class="col s12 m6 l3">
                        <div class="input-field">
                            <select name="opcionmenu_id" id="opcionmenu_id" disabled>
                                @if ($permisousu)
<option value="{{ $permisousu->menuopcione->id }}" >
{{ $permisousu->menuopcione->descripcion }}
</option>
                                @endif
                            </select>
                            <label for="opcionmenu_id">Opción de menú</label>
                            <span class="helper-text" id="opcionmenu_id_spn" data-error="" data-success=""></span>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Botones de Acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" form="form" class="btn waves-effect waves-light"><i
                class="material-icons left">save</i>Guardar</button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
