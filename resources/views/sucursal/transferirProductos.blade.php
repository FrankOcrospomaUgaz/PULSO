<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">
                    <i class="fa-solid fa-right-left left"></i>Importar productos
                </span>
                <p style="margin: 8px 0 0;">
                    Destino: <strong>{{ $sucursalDestino->nombrecomercial }}</strong>
                    @if ($sucursalDestino->empresa)
                        <small>({{ $sucursalDestino->empresa->razon_social }})</small>
                    @endif
                </p>
            </div>

            <div class="card-body bg-white">
                <form id="form" method="POST" data-form-transferencia-productos="1">
                    @csrf
                    <input type="hidden" id="form-action-url"
                        value="{{ route('sucursal.guardarTransferenciaProductos', ['sucursal_id' => $sucursalDestino->id]) }}">
                    <input type="hidden" id="productos-transferencia-url"
                        value="{{ url('/sucursal/productosTransferencia') }}">

                    @if ($sucursalesOrigen->isEmpty())
                        <div class="card-panel amber lighten-4" style="margin-top: 0;">
                            No hay otras sucursales disponibles para importar productos.
                        </div>
                    @else
                        <div class="row" style="margin-bottom: 0;">
                            <div class="input-field col s12">
                                <select name="sucursal_origen_id" id="sucursal_origen_id" class="validate" required>
                                    <option value="" disabled selected>Selecciona sucursal origen</option>
                                    @php
                                        $sucursalesAgrupadas = $sucursalesOrigen->groupBy('empresa_id');
                                    @endphp
                                    @foreach ($sucursalesAgrupadas as $sucursalesGrupo)
                                        <optgroup label="{{ $sucursalesGrupo->first()->empresa->razon_social ?? 'Empresa' }}">
                                            @foreach ($sucursalesGrupo as $sucursalOrigen)
                                                <option value="{{ $sucursalOrigen->id }}">
                                                    {{ $sucursalOrigen->nombrecomercial }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <label for="sucursal_origen_id">Sucursal origen</label>
                            </div>
                        </div>

                        <p id="resumen-origen-transferencia" style="margin-top: 0; display: none;">
                            Productos a importar desde: <strong id="nombre-origen-transferencia"></strong>
                        </p>

                        <div class="row" style="margin-bottom: 0;">
                            <div class="input-field col s12 m8">
                                <input type="text" id="buscar-producto-transferencia">
                                <label for="buscar-producto-transferencia">Buscar producto</label>
                            </div>
                            <div class="col s12 m4" style="padding-top: 18px;">
                                <button type="button" class="btn-flat" id="btn-marcar-productos-transferencia">
                                    Marcar visibles
                                </button>
                                <button type="button" class="btn-flat" id="btn-limpiar-productos-transferencia">
                                    Limpiar
                                </button>
                            </div>
                        </div>

                        <p>
                            <label>
                                <input type="checkbox" name="transferir_todos" id="transferir_todos" value="1" />
                                <span>Todos los productos de la sucursal origen</span>
                            </label>
                        </p>

                        <div id="lista-productos-transferencia"
                            style="border: 1px solid #ddd; max-height: 320px; overflow-y: auto; padding: 8px 12px;">
                            <p>Selecciona una sucursal origen para ver los productos disponibles.</p>
                        </div>

                        <p>
                            <label>
                                <input type="checkbox" name="mover_productos" value="1" />
                                <span>Quitar los productos del origen al finalizar</span>
                            </label>
                        </p>
                    @endif

                    <div class="col s12 mt20 mt-2">
                        <div class="modal-footer right-align">
                            <button type="button" class="btn-flat modal-close">Cancelar</button>
                            @if (!$sucursalesOrigen->isEmpty())
                                <button type="submit" id="btn-guardar-transferencia" class="btn darken-2" disabled>Guardar</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
