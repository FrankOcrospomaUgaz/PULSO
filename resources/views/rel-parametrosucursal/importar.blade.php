<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title"><i class="fa-solid fa-plus left"></i>Parámetros por sucursal | Importar</span>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="form-action-url" value="{{ route('importarparametrosucursal') }}">
                    <div class="col s12">
                        <div class="row">
                            <div class="col s12 m12 l12 center">
                                <font>La configuración de la sucursal de destino se sobreescribirá y no podrá recuperarse</font>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <select name="sucursal_origen_id" id="sucursal_origen_id" class="validate @error('parametro_id') invalid @enderror">
                                    <option value="" disabled selected>Selecciona una sucursal</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}">
                                            {{ $sucursal->razonsocial }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="parametro_id">{{ __('Sucursal de origen') }}</label>
                                {!! $errors->first(
                                    'parametro_id',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>
                            <input id="sucursaldestino_id" name="sucursaldestino_id" type="hidden" value="{{$sucursal_id}}">
                        </div>
                    </div>
                    <!-- Botones de Acción -->
                    <div class="col s12 mt20 mt-2">
                        <div class="modal-footer">
                            <button type="submit" class="btn waves-effect waves-light"><i class="material-icons left">send</i>{{ __('Guardar') }}</button>
                            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>