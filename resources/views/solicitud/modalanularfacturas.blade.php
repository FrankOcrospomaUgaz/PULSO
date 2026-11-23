
<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content" style="padding-left: 12px">
                <span class="card-title" style="display: flex; align-items: center; padding: -12px">
                    <i class="fa-solid fa-ban left"></i>
                    <span style="flex: 1; text-align: center;">
                        Comprobantes | Anular facturas</span>
                </span>
            </div>

            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" id="form-action-url" value="{{ route('cancell_facturas.store') }}">

                    <div class="col s12">
                        <div class="row">
                            <div class="col s12 m12 l4">
                                <div class="row without-bottom">
                                    <div class="col s12 m12 l12">
                                        <div class="input-field">
                                            <input id="fecref" name="fecref" type="date" value="{{ date('Y-m-d') }}">
                                            <label for="fecref">Fecha de emisión de comprobante</label>
                                        </div>
                                    </div>
                                    <div class="col s12 m12 l12">
                                        <p>
                                            <label for="monedaSoles">
                                                <input id="monedaSoles" name="moneda" type="radio" value="PEN" checked>
                                                <span>PEN</span>
                                            </label>
                                            <label for="monedaDolar">
                                                <input id="monedaDolar" name="moneda" type="radio" value="USD">
                                                <span>USD</span>
                                            </label>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col s12 m12 l8">
                                <div class="input-field col s12">
                                    <i class="fa-solid fa-plus prefix"></i>
                                    <input class="autocomplete validate" autocomplete="off"
                                           id="agregarOpcionmenu"
                                           type="text"
                                           data-autocomplete-url="{{ route('autocomplete.solicitudes') }}"
                                           data-autocomplete-context="solicitud"
                                           data-tipo-documento="F">
                                    <label for="agregarOpcionmenu">Comprobante</label>
                                </div>

                                <div class="col s12">
                                    <table class="bordered display responsive nowrap data-table" id="opcionmenusTable">
                                        <thead class="bg-primary">
                                            <tr>
                                                <th class="center">Comprobante</th>
                                                <th class="center">Motivo</th>
                                                <th class="center"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="opcionmenusBody">
                                            <!-- Llenado dinámico por JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col s12 mt20 mt-2">
                        <div class="modal-footer">
                            <button type="submit" class="btn waves-effect waves-light">
                                <i class="material-icons left">send</i>{{ __('Anular') }}
                            </button>
                            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
