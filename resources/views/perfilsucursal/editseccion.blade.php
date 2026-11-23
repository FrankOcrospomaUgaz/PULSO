<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title"> <i class="fa-solid fa-plus left"></i>Secciones por perfil por sucursal |
                    Editar</span>
            </div>
            <div class="card-body bg-white">
                <!-- Formulario sin action definido -->
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') <!-- Agrega este campo para el método correcto -->

                    <!-- Campo oculto para la URL de acción -->
                    <input type="hidden" id="form-action-url"
                        value="{{ route('rel_seccionsucursalperfil.update', $relseccionsucursalperfil->id) }}">
                    <div class="col s12">
                        <!-- Campo de selección para 'opcion_id' -->
                        <div class="input-field col s12 m6">
                            <select name="opcion_id" id="parametro_id"
                                class="validate @error('opcion_id') invalid @enderror">
                                <option value=""  selected>Ninguna</option>
                                @foreach ($opciones as $opcion)
                                    <option value="{{ $opcion->id }}"
                                        {{ old('opcion_id', $relseccionsucursalperfil?->menuopcionesdefecto_id) == $opcion->id ? 'selected' : '' }}>
                                        {{   $opcion->tipoopcione->descripcion . ' - ' . $opcion->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="parametro_id">{{ __('Opción por defecto') }}</label>
                            {!! $errors->first(
                                'opcion_id',
                                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                            ) !!}
                        </div>


                        <!-- Botones de Acción -->
                        <div class="col s12 mt20 mt-2">
                            <div class="modal-footer">
                                <button type="submit" class="btn waves-effect waves-light"><i
                                        class="material-icons left">send</i>{{ __('Guardar') }}</button>
                                <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
