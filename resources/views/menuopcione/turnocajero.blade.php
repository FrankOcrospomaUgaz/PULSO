<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-edit left"></i>Asignación de turnos | CAJERO
                </p>
            </div>
            <div class="card-body bg-white">
                <form id="frmMantAsignacionTurno">
                    <div class="col s12">
                        <div class="row">
                            <div class="col s12 m6 l4 offset-l4 offset-m3">
                                <select id="caja_id" name="caja_id">
                                    @foreach ($cajas as $caja)
<option value="{{ $caja->id }}" {{ old('caja_id') == $caja->id ? 'selected' : '' }}>
{{ $caja->numero }}
</option>
                                    @endforeach
                                </select>
                                <label for="caja_id">Caja</label>
                            </div>
                        </div>
                    </div>
                    <!-- Botones de acción -->
                    <div class="col s12 mt20 mt-2">
                        <div class="modal-footer">
                            <button type="submit" class="btn waves-effect waves-light">
                                <i class="material-icons left">send</i>{{ __('Guardar') }}
                            </button>
                            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>