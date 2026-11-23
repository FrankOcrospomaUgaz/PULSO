<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title"> <i class="fa-solid fa-plus left"></i>Reestablecer | contraseña</span>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <input type="hidden" id="form-action-url" value="{{ route('nuevacontraseñaUsuarioLogueado') }}">
                    <div class="input-field col s12 m4">
                        <input type="password" name="antiguacontraseña" id="antiguacontraseña"
                            class="validate @error('antiguacontraseña') invalid @enderror">
                        <label for="antiguacontraseña" >{{ __('Antigua Contraseña') }}</label>
                        {!! $errors->first(
                            'antiguacontraseña',
                            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                        ) !!}
                    </div>
                    <div class="input-field col s12 m4">
                        <input type="password" name="nuevacontraseña" id="nuevacontraseña"
                            class="validate @error('nuevacontraseña') invalid @enderror">
                        <label for="nuevacontraseña" >{{ __('Nueva Contraseña') }}</label>
                        {!! $errors->first(
                            'nuevacontraseña',
                            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                        ) !!}
                    </div>

                    <div class="input-field col s12 m4">
                        <input type="password" name="nuevacontraseña_confirmation" id="confirmarcontraseña"
                            class="validate @error('nuevacontraseña') invalid @enderror">
                        <label for="confirmarcontraseña" >{{ __('Confirmar Contraseña') }}</label>
                        {!! $errors->first(
                            'nuevacontraseña_confirmation',
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

                </form>
            </div>
        </div>
    </div>
</div>
