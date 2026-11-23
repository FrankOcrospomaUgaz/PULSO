@extends('layouts.app')

@section('content')
    @php
        $esCobrar = $tipo === 'COBRAR';
        $titulo = $esCobrar ? 'Cuentas por cobrar' : 'Cuentas por pagar';
        $rutaBase = $esCobrar ? route('cuentas.cobrar.index') : route('cuentas.pagar.index');
        $rutaPdf = route('cuentas.pdf', strtolower($tipo));
        $rutaExcel = route('cuentas.excel', strtolower($tipo));
    @endphp

    <style>
        .cuentas-summary {
            align-items: center;
            display: grid;
            gap: 28px;
            grid-template-columns: minmax(260px, 1.25fr) 90px minmax(220px, .95fr) 90px minmax(260px, .95fr);
            margin: 18px 0 34px;
        }

        .cuentas-summary-card {
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .22);
            min-height: 190px;
            padding: 28px 32px;
        }

        .cuentas-summary-card h2 {
            font-size: 3.4rem;
            font-weight: 800;
            letter-spacing: 0;
            margin: 0 0 18px;
        }

        .cuentas-summary-card p {
            font-size: 1.15rem;
            margin: 4px 0;
        }

        .cuentas-symbol {
            color: #343a40;
            font-size: 5.5rem;
            font-weight: 700;
            text-align: center;
        }

        .cuentas-filter-row {
            display: grid;
            gap: 18px 28px;
            grid-template-columns: repeat(12, 1fr);
            margin-bottom: 10px;
        }

        .cuentas-filter-row .span-2 { grid-column: span 2; }
        .cuentas-filter-row .span-3 { grid-column: span 3; }
        .cuentas-filter-row .span-4 { grid-column: span 4; }

        .cuentas-actions {
            align-items: center;
            display: flex;
            gap: 18px;
            height: 100%;
        }

        .cuentas-actions .btn {
            border-radius: 20px;
            font-weight: 800;
        }

        .cuentas-pay-button {
            border-radius: 22px;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .cuentas-table-total {
            border-top: 3px double #aaa;
            font-size: 1.45rem;
            font-weight: 800;
        }

        .cuentas-plazo {
            font-size: 1.7rem;
            font-weight: 900;
        }

        .cuentas-date-dot {
            border-radius: 50%;
            display: inline-block;
            height: 12px;
            margin-right: 4px;
            width: 12px;
        }

        @media (max-width: 992px) {
            .cuentas-summary,
            .cuentas-filter-row {
                display: block;
            }

            .cuentas-summary-card,
            .cuentas-symbol,
            .cuentas-filter-row > div {
                margin-bottom: 14px;
            }
        }
    </style>

    <div class="main-body" data-cuentas-url="{{ $rutaBase }}">
        <div class="row" style="margin-top: 0;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="cuentas-summary">
                        <div class="cuentas-summary-card yellow darken-1 grey-text text-darken-3">
                            <h2>{{ $esCobrar ? 'Por cobrar' : 'Por pagar' }} <i class="fa-solid fa-hand-holding-dollar right"></i></h2>
                            <p><strong>PEN {{ number_format($resumen['total'], 2) }}</strong></p>
                            <p>{{ $resumen['documentos'] }} documentos</p>
                        </div>
                        <div class="cuentas-symbol">=</div>
                        <div class="cuentas-summary-card teal white-text">
                            <h2>A tiempo <i class="fa-solid fa-clock right"></i></h2>
                            <p><strong>PEN {{ number_format($resumen['atiempo'], 2) }}</strong></p>
                            <p>{{ $resumen['atiempo_documentos'] }} documentos</p>
                        </div>
                        <div class="cuentas-symbol">+</div>
                        <div class="cuentas-summary-card red lighten-1 white-text">
                            <h2>Vencidas <i class="fa-solid fa-triangle-exclamation right"></i></h2>
                            <p><strong>PEN {{ number_format($resumen['vencidas'], 2) }}</strong></p>
                            <p>{{ $resumen['vencidas_documentos'] }} documentos</p>
                        </div>
                    </div>

                    <button type="button" class="btn yellow darken-1 grey-text text-darken-4 cuentas-pay-button"
                        onclick="abrirPagoCuentaSeleccionada()">
                        {{ $esCobrar ? 'COBRAR' : 'PAGAR' }}
                        <i class="fa-solid fa-hand-holding-dollar right grey-text text-darken-4"></i>
                    </button>

                    <div class="cuentas-filter-row">
                        <div class="input-field span-2">
                            <select id="cuenta-caja">
                                <option value="">Seleccionar caja</option>
                                @foreach ($filtros['cajas'] as $caja)
                                    <option value="{{ $caja->id }}" {{ request('caja_id') == $caja->id ? 'selected' : '' }}>
                                        {{ $caja->numero }}
                                    </option>
                                @endforeach
                            </select>
                            <label>Caja</label>
                        </div>
                        <div class="input-field span-4">
                            <select id="cuenta-sucursal">
                                <option value="">Todas</option>
                                @foreach ($filtros['sucursales'] as $sucursal)
                                    <option value="{{ $sucursal->id }}" {{ request('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                                        {{ $sucursal->nombrecomercial }}
                                    </option>
                                @endforeach
                            </select>
                            <label>Sucursal</label>
                        </div>
                        <div class="input-field span-4">
                            <input id="cuenta-search" type="text" value="{{ request('search') }}">
                            <label for="cuenta-search" class="{{ request('search') ? 'active' : '' }}">Buscar</label>
                        </div>
                        <div class="input-field span-2">
                            <select id="cuenta-estado-cuotas">
                                <option value="">Todos</option>
                                <option value="ATIEMPO" {{ request('estado_cuotas') === 'ATIEMPO' ? 'selected' : '' }}>A tiempo</option>
                                <option value="VENCIDAS" {{ request('estado_cuotas') === 'VENCIDAS' ? 'selected' : '' }}>Vencidas</option>
                            </select>
                            <label>Estado de cuotas</label>
                        </div>
                    </div>

                    <div class="cuentas-filter-row">
                        <div class="input-field span-2">
                            <select id="cuenta-estado">
                                <option value="">Nuevo, En proceso</option>
                                <option value="NUEVO" {{ request('estado') === 'NUEVO' ? 'selected' : '' }}>Nuevo</option>
                                <option value="PAGANDO" {{ request('estado') === 'PAGANDO' ? 'selected' : '' }}>En proceso</option>
                                <option value="PAGADO" {{ request('estado') === 'PAGADO' ? 'selected' : '' }}>Pagado</option>
                            </select>
                            <label>Estado</label>
                        </div>
                        <div class="input-field span-2">
                            <select id="cuenta-situacion">
                                <option value="A" {{ request('situacion', 'A') === 'A' ? 'selected' : '' }}>Activado</option>
                                <option value="D" {{ request('situacion') === 'D' ? 'selected' : '' }}>Desactivado</option>
                            </select>
                            <label>Situacion</label>
                        </div>
                        <div class="input-field span-3">
                            <input id="cuenta-persona" type="text" value="{{ request('persona') }}">
                            <label for="cuenta-persona" class="{{ request('persona') ? 'active' : '' }}">Persona</label>
                        </div>
                        <div class="input-field span-3">
                            <select id="cuenta-concepto">
                                <option value="">Todos</option>
                                @foreach ($filtros['conceptos'] as $concepto)
                                    <option value="{{ $concepto->id }}" {{ request('conceptopago_id') == $concepto->id ? 'selected' : '' }}>
                                        {{ $concepto->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            <label>Concepto de pago</label>
                        </div>
                        <div class="input-field span-2">
                            <input id="cuenta-fecha-inicio" type="date" value="{{ request('fechaInicio') }}">
                            <label class="active">Fecha de inicio</label>
                        </div>
                    </div>

                    <div class="cuentas-filter-row">
                        <div class="input-field span-2">
                            <input id="cuenta-fecha-fin" type="date" value="{{ request('fechaFin') }}">
                            <label class="active">Fecha de fin</label>
                        </div>
                        <div class="input-field span-2">
                            <input id="cuenta-fecha-programada" type="date" value="{{ request('fechaProgramada') }}">
                            <label class="active">Fecha programada</label>
                        </div>
                        <div class="input-field span-3">
                            <input id="cuenta-salon" type="text" value="{{ request('salon') }}">
                            <label for="cuenta-salon" class="{{ request('salon') ? 'active' : '' }}">Salon o mesa</label>
                        </div>
                        <div class="span-5 cuentas-actions">
                            <button type="button" class="btn red lighten-1" onclick="filtrarCuentas()">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                            <a class="btn red" target="_blank" href="{{ $rutaPdf }}?{{ http_build_query(request()->all()) }}">
                                PDF <i class="fa-solid fa-file-pdf right"></i>
                            </a>
                            <a class="btn green" target="_blank" href="{{ $rutaExcel }}?{{ http_build_query(request()->all()) }}">
                                EXCEL <i class="fa-solid fa-file right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="row without-tb">
                        <div class="col s10">
                            <div class="valign-wrapper">
                                Registros por pagina:
                                <select id="records-per-page" class="browser-default" onchange="filtrarCuentas()">
                                    @foreach ([10, 25, 50, 100] as $size)
                                        <option value="{{ $size }}" {{ request('perPage', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col s2 right-align">
                            <button class="btn-flat btn-grid-options tooltipped" type="button" onclick="expandOrCompressChildRows();" data-tooltip="Expandir filas">
                                <i class="fa-solid fa-expand" style="color:#343a40;"></i>
                            </button>
                            <button class="btn-flat btn-grid-options tooltipped" type="button" data-tooltip="Filtros">
                                <i class="fa-solid fa-magnifying-glass-minus" style="color:#343a40;"></i>
                            </button>
                        </div>
                    </div>

                    <div id="tb-container" class="table-wrapper">
                        <div class="table-scroll" style="overflow-x: auto;">
                            <table id="data-table" class="highlight striped centered" style="width: max-content; min-width: 100%;">
                                <thead class="thead-custom">
                                    <tr>
                                        <th class="no-hide"><label><input type="checkbox" onclick="toggleCuentasChecks(this)"><span></span></label></th>
                                        <th class="no-hide">Numero</th>
                                        <th class="no-hide">Estado</th>
                                        <th class="no-hide">Fecha</th>
                                        <th>Cuotas</th>
                                        <th class="no-hide">Fecha de pago</th>
                                        <th class="no-hide">Plazo</th>
                                        <th class="no-hide">Origen</th>
                                        <th>Concepto</th>
                                        <th>Moneda</th>
                                        <th class="no-hide">Total</th>
                                        <th class="no-hide">Pagado</th>
                                        <th class="no-hide">Restante</th>
                                        <th>Usuario</th>
                                        <th class="no-hide">Persona</th>
                                        <th class="no-hide">Situacion</th>
                                        <th class="no-hide">Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($cuentas as $cuenta)
                                        @php
                                            $plazo = $cuenta->fechavencimiento ? now()->startOfDay()->diffInDays($cuenta->fechavencimiento->startOfDay(), false) : 0;
                                            $vencida = $plazo < 0 && $cuenta->estado !== 'PAGADO';
                                            $movimiento = $cuenta->movcaja?->movimiento;
                                            $personaCuenta = ($movimiento?->Persona?->personamaestro?->nrodoc ?? '0') . ' - ' . ($movimiento?->persona ?? 'CLIENTES VARIOS');
                                            $origen = trim(($movimiento?->tipodocumento?->tipomovimiento?->descripcion ?? $movimiento?->tipodocumento?->nombre ?? 'Caja') . ' ' . ($movimiento?->numero ?? $cuenta->movcaja?->caja ?? ''));
                                        @endphp
                                        <tr>
                                            <td>
                                                <label>
                                                    <input type="checkbox" class="cuenta-check" value="{{ $cuenta->id }}" data-pago-url="{{ route('cuentas.pago', $cuenta->id) }}">
                                                    <span></span>
                                                </label>
                                            </td>
                                            <td>{{ $cuenta->numero }}</td>
                                            <td>
                                                <span class="new badge {{ $cuenta->estado === 'PAGANDO' ? 'yellow black-text' : ($cuenta->estado === 'PAGADO' ? 'green' : 'blue') }}"
                                                    data-badge-caption="{{ $cuenta->estado === 'PAGANDO' ? 'En proceso' : ucfirst(strtolower($cuenta->estado)) }}"></span>
                                            </td>
                                            <td>{{ optional($cuenta->fecha)->format('Y-m-d') }}<br>{{ optional($cuenta->fecha)->format('h:i:s A') }}</td>
                                            <td>
                                                <span class="cuentas-date-dot {{ $vencida ? 'red' : 'green' }}"></span>{{ optional($cuenta->fechavencimiento)->format('Y-m-d H:i') }}
                                            </td>
                                            <td>{{ optional($cuenta->fechapago)->format('Y-m-d H:i') ?: '-' }}</td>
                                            <td><span class="cuentas-plazo">{{ $plazo }}</span></td>
                                            <td>{{ $origen }}</td>
                                            <td><span class="new badge orange" data-badge-caption="{{ $cuenta->movcaja?->conceptopago?->descripcion ?? 'Sin concepto' }}"></span></td>
                                            <td>{{ $cuenta->moneda }}</td>
                                            <td><strong style="font-size:1.5rem">{{ number_format($cuenta->total, 2) }}</strong></td>
                                            <td><strong style="font-size:1.5rem">{{ number_format((float) $cuenta->totalpagado, 2) }}</strong></td>
                                            <td><strong style="font-size:1.5rem">{{ number_format($cuenta->restante, 2) }}</strong></td>
                                            <td>{{ $movimiento?->usuario ?? 'N/A' }}</td>
                                            <td>{{ $personaCuenta }}</td>
                                            <td><span class="new badge green" data-badge-caption="{{ $cuenta->situacion_texto }}"></span></td>
                                            <td>
                                                @if ($cuenta->estado !== 'PAGADO')
                                                    <a href="{{ route('cuentas.pago', $cuenta->id) }}" class="btn-floating pink tooltipped btn-crear"
                                                        data-tooltip="{{ $esCobrar ? 'Cobrar' : 'Pagar' }}"
                                                        data-titulo="{{ $esCobrar ? 'Cobrar cuentas' : 'Pagar cuentas' }}"
                                                        data-icono="fa-solid fa-handshake">
                                                        <i class="fa-solid fa-cash-register"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('movcaja.verDetalles', $cuenta->movcaja_id) }}" class="btn-floating blue tooltipped btn-cargar-crud" data-tooltip="Detalles" data-titulo="Detalle de cuenta" data-icono="fa-solid fa-list">
                                                    <i class="fa-solid fa-list"></i>
                                                </a>
                                                <a href="{{ $rutaPdf }}?search={{ urlencode($cuenta->numero) }}" target="_blank" class="btn-floating purple tooltipped" data-tooltip="Imprimir">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                                <a href="{{ $rutaPdf }}?search={{ urlencode($cuenta->numero) }}" target="_blank" class="btn-floating red tooltipped" data-tooltip="PDF">
                                                    <i class="fa-solid fa-file-pdf"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="cuentas-table-total">
                                        <td colspan="9"></td>
                                        <td>PEN</td>
                                        <td>{{ number_format($cuentas->sum(fn($c) => $c->total), 2) }}</td>
                                        <td>{{ number_format($cuentas->sum(fn($c) => (float) $c->totalpagado), 2) }}</td>
                                        <td>{{ number_format($cuentas->sum(fn($c) => $c->restante), 2) }}</td>
                                        <td colspan="4"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $cuentas->firstItem() ?? 0 }} a {{ $cuentas->lastItem() ?? 0 }} de {{ $cuentas->total() }} registros</p>
                            </div>
                            <div class="col s12 m6 right-align">
                                {{ $cuentas->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filtrarCuentas(page = 1) {
            const params = new URLSearchParams({
                page,
                caja_id: document.getElementById('cuenta-caja').value,
                sucursal_id: document.getElementById('cuenta-sucursal').value,
                search: document.getElementById('cuenta-search').value,
                estado_cuotas: document.getElementById('cuenta-estado-cuotas').value,
                estado: document.getElementById('cuenta-estado').value,
                situacion: document.getElementById('cuenta-situacion').value,
                persona: document.getElementById('cuenta-persona').value,
                conceptopago_id: document.getElementById('cuenta-concepto').value,
                fechaInicio: document.getElementById('cuenta-fecha-inicio').value,
                fechaFin: document.getElementById('cuenta-fecha-fin').value,
                fechaProgramada: document.getElementById('cuenta-fecha-programada').value,
                salon: document.getElementById('cuenta-salon').value,
                perPage: document.getElementById('records-per-page').value,
            });

            cargarContenidoDinamicamente(`{{ $rutaBase }}?${params.toString()}`);
        }

        function toggleCuentasChecks(source) {
            document.querySelectorAll('.cuenta-check').forEach((check) => check.checked = source.checked);
        }

        function abrirPagoCuentaSeleccionada() {
            const check = document.querySelector('.cuenta-check:checked');
            if (!check) {
                mostrarMensajeError('Seleccione una cuenta para continuar.');
                return;
            }

            const link = document.querySelector(`.cuenta-check[value="${check.value}"]`)?.closest('tr')?.querySelector('.btn-crear');
            if (link) {
                link.click();
            }
        }
    </script>
@endsection
