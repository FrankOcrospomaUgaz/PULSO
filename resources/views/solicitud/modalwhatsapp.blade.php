<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title" style="display: flex; align-items: center;">
                    <i class="fa-brands fa-whatsapp left" style="margin-right: 8px;"></i>
                    <span style="flex: 1; text-align: center;">Comprobantes |
                        {{ $solicitud->serie . '-' . $solicitud->correlativo }} | Enviar por WhatsApp</span>
                </span>
            </div>

            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="form-action-url" value="{{ route('enviarWhatsApp.cliente') }}">
                    <input type="hidden" id="id" name="id" value="{{ $solicitud->id }}">
                    <input type="hidden" id="sucursal_id" name="sucursal_id" value="{{ $solicitud->sucursal_id }}">
                    <div class="col s12">
                        <div class="row">
                            <div class="input-field col s12 m6 l4 offset-l4 offset-m3">
                                <input autocomplete="off" class="inpt-cantidad submit-form" id="celular"
                                    name="celular" type="text" inputmode="numeric">
                                <label for="celular" class="">Celular</label>
                                <span class="helper-text" id="celular_spn"></span>
                            </div>

                        </div>
                    </div>
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
