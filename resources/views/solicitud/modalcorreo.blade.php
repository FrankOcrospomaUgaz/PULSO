<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title" style="display: flex; align-items: center;">
                    <i class="material-icons left" style="margin-right: 8px;">email</i>
                    <span style="flex: 1; text-align: center;">Comprobantes |
                        {{ $solicitud->serie . '-' . $solicitud->correlativo }} | Enviar por Correo</span>
                </span>
            </div>

            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="form-action-url" value="{{ route('enviarCorreo.cliente') }}">

                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col s12 m6 l4 offset-l4 offset-m3">
                                <input autocomplete="off" id="correo" name="correo" type="email" class="validate" required>
                                <label for="correo">Correo electrónico</label>
                                <span class="helper-text">Ingrese el correo electrónico del cliente</span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="venta_id" value="{{ $solicitud->movimiento->movventa->id }}">

                    <!-- Botones de Acción -->
                    <div class="col s12 mt20 mt-2">
                        <div class="modal-footer">
                            <button type="submit" class="btn waves-effect waves-light">
                                <i class="material-icons left">send</i>{{ __('Enviar') }}
                            </button>
                            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
