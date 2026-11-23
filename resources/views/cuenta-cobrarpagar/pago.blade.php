@php
    $esCobrar = $cuenta->tipo === 'COBRAR';
    $accion = $esCobrar ? 'Cobrar' : 'Pagar';
    $titulo = $accion . ' cuentas';
    $movimiento = $cuenta->movcaja?->movimiento;
    $persona = $movimiento?->Persona;
    $documento = $persona?->personamaestro?->nrodoc ?? '0';
    $nombrePersona = $movimiento?->persona ?: 'CLIENTES VARIOS';
    $clienteLabel = trim($documento . ' - ' . $nombrePersona);
    $responsableNombre = $responsable ? trim(($responsable->nombres ?? '') . ' ' . ($responsable->apellidos ?? '')) : (Auth::user()->name ?? 'CAJERO');
    $responsableDocumento = $responsable?->personamaestro?->nrodoc ?? '0';
    $responsableLabel = trim($responsableDocumento . ' - ' . ($responsableNombre ?: 'CAJERO'));
    $conceptoDefault = $conceptos->first();
@endphp

<style>
    .cuenta-pago-modal {
        color: #2f3439;
        margin: 0 auto;
        max-width: 1320px;
        padding: 24px 34px 26px;
    }

    .cuenta-pago-title {
        font-size: 2rem;
        font-weight: 800;
        margin: 0 0 30px;
        text-align: center;
    }

    .cuenta-pago-title i {
        color: #00897b;
        margin-right: 10px;
    }

    .cuenta-pago-grid {
        display: grid;
        gap: 22px 30px;
        grid-template-columns: repeat(12, 1fr);
        min-height: 220px;
    }

    .cuenta-pago-grid .span-2 { grid-column: span 2; }
    .cuenta-pago-grid .span-3 { grid-column: span 3; }
    .cuenta-pago-grid .span-4 { grid-column: span 4; }
    .cuenta-pago-grid .span-6 { grid-column: span 6; }
    .cuenta-pago-grid .span-8 { grid-column: span 8; }

    .cuenta-pago-persona {
        align-items: center;
        display: grid;
        gap: 12px;
        grid-template-columns: 1fr 56px 1fr;
    }

    .cuenta-pago-persona .btn-floating {
        background-color: #00897b !important;
        height: 48px;
        line-height: 48px;
        width: 48px;
    }

    .cuenta-pago-total {
        color: #263238;
        font-size: 2.4rem !important;
        font-weight: 800;
        text-align: right;
    }

    .cuenta-pago-moneda {
        align-items: center;
        display: flex;
        gap: 28px;
        min-height: 64px;
    }

    .cuenta-pago-actions {
        align-items: center;
        display: flex;
        gap: 14px;
        justify-content: flex-end;
        margin-top: 24px;
        padding-top: 8px;
    }

    .cuenta-pago-actions .btn {
        border-radius: 24px;
        font-weight: 800;
        min-width: 160px;
    }

    .cuenta-pago-actions .btn-save {
        background-color: #00897b !important;
    }

    .cuenta-pago-modal input[readonly] {
        color: #111;
    }

    .cuenta-pago-modal [type="radio"]:checked + span:after,
    .cuenta-pago-modal [type="radio"].with-gap:checked + span:after {
        background-color: #00897b;
        border-color: #00897b;
    }

    .cuenta-pago-modal [type="radio"]:checked + span:before {
        border-color: #00897b;
    }

    @media (max-width: 992px) {
        .cuenta-pago-grid,
        .cuenta-pago-persona {
            display: block;
        }

        .cuenta-pago-modal {
            padding: 18px 20px 22px;
        }

        .cuenta-pago-grid > div,
        .cuenta-pago-persona > div,
        .cuenta-pago-persona > a {
            margin-bottom: 16px;
        }
    }
</style>

<div class="cuenta-pago-modal">
    <h5 class="cuenta-pago-title">
        <i class="fa-solid fa-handshake"></i>{{ $titulo }}
    </h5>

    <form id="form" method="POST">
        @csrf
        <input type="hidden" id="form-action-url" value="{{ route('cuentas.guardarPago', $cuenta->id) }}">
        <input type="hidden" name="conceptopago_id" value="{{ $conceptoDefault?->id }}">
        <input type="hidden" name="numero" value="">

        <div class="cuenta-pago-grid">
            <div class="span-6 cuenta-pago-persona">
                <div class="input-field">
                    <input id="cuenta_pago_cliente" type="text" value="{{ $clienteLabel }}" readonly>
                    <label for="cuenta_pago_cliente" class="active">Cliente</label>
                </div>
                <a class="btn-floating waves-effect" title="Cliente">
                    <i class="fa-solid fa-user-check"></i>
                </a>
                <div class="input-field">
                    <input id="cuenta_pago_responsable" type="text" value="{{ $responsableLabel }}" readonly>
                    <label for="cuenta_pago_responsable" class="active">Responsable</label>
                </div>
            </div>

            <div class="input-field span-2">
                <select name="caja_id" id="cuenta_pago_caja" required>
                    @foreach ($cajas as $caja)
                        <option value="{{ $caja->id }}">{{ $caja->numero }}</option>
                    @endforeach
                </select>
                <label for="cuenta_pago_caja">Caja</label>
            </div>

            <div class="input-field span-4">
                <textarea name="comentario" id="cuenta_pago_comentario" class="materialize-textarea"></textarea>
                <label for="cuenta_pago_comentario">Notas al movimiento de caja</label>
            </div>

            <div class="input-field span-2">
                <select name="mediopago_id" id="cuenta_pago_medio" required>
                    @foreach ($medioPagos as $medioPago)
                        <option value="{{ $medioPago->id }}">{{ $medioPago->descripcion }}</option>
                    @endforeach
                </select>
                <label for="cuenta_pago_medio">Medio de pago</label>
            </div>

            <div class="input-field span-2">
                <input class="cuenta-pago-total" type="number" step="0.01" min="0.01"
                    max="{{ $cuenta->restante }}" name="monto" id="cuenta_pago_monto"
                    value="{{ number_format($cuenta->restante, 2, '.', '') }}" required>
                <label for="cuenta_pago_monto" class="active">Total a {{ $esCobrar ? 'cobrar' : 'pagar' }}</label>
            </div>

            <div class="span-8"></div>

            <div class="span-3 cuenta-pago-moneda">
                <label>
                    <input name="moneda_visual" type="radio" value="PEN" {{ $cuenta->moneda === 'PEN' ? 'checked' : '' }} disabled>
                    <span>S/.</span>
                </label>
                <label>
                    <input name="moneda_visual" type="radio" value="USD" {{ $cuenta->moneda === 'USD' ? 'checked' : '' }} disabled>
                    <span>$</span>
                </label>
            </div>
        </div>

        <div class="cuenta-pago-actions">
            <button type="button" class="btn-flat modal-close">CANCELAR</button>
            <button type="button" class="btn-flat modal-close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <button type="submit" class="btn btn-save white-text">
                GUARDAR
                <i class="fa-solid fa-floppy-disk right"></i>
            </button>
        </div>
    </form>
</div>
