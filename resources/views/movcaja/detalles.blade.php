@extends('layouts.app')

@section('content')
    <div class="main-body">
        <div class="card main-content">
            <div class="row padding-1 p-1">
                <div class="col s12 m8">
                    <div class="input-field col s12 m2">
                        <input type="text" id="tipo" value="{{ $movcaja->movimiento->tipodocumento->nombre ?? 'N/A' }}"
                            readonly>
                        <label for="tipo" class="active">{{ __('Tipo') }}</label>
                        <span class="helper-text" id="tipodocumento_id_spn" data-error="" data-success=""></span>
                    </div>

                    <div class="input-field col s12 m2">
                        <input type="text" id="numero" value="{{ $movcaja->movimiento->numero ?? 'N/A' }}" readonly>
                        <label for="numero" class="active">{{ __('Número') }}</label>
                        <span class="helper-text" id="numero_spn" data-error="" data-success=""></span>
                    </div>

                    <div class="input-field col s12 m2">
                        <input type="text" id="fecha"
                            value="{{ $movcaja->created_at instanceof \Carbon\Carbon ? $movcaja->created_at->format('d/m/Y') : $movcaja->created_at ?? 'N/A' }}"
                            readonly>
                        <label for="fecha" class="active">{{ __('Fecha') }}</label>
                        <span class="helper-text" id="tipodocumento_id_spn" data-error=""
                            data-success="">{{ $movcaja->created_at instanceof \Carbon\Carbon ? $movcaja->created_at->format('h:i:s A') : $movcaja->movimiento->fecha ?? 'N/A' }}</span>
                    </div>

                    <div class="input-field col s12 m6">
                        <input type="text" id="concepto" value="{{ $movcaja->conceptopago->descripcion ?? 'N/A' }}"
                            readonly>
                        <label for="concepto" class="active">{{ __('Concepto') }}</label>
                        <span class="helper-text" id="conceptopago_id_spn" data-error="" data-success=""></span>
                    </div>

                    <div class="input-field col s12 m6">
                        <input type="text" id="persona" value="{{ $movcaja->movimiento->persona ?? 'N/A' }}" readonly>
                        <label for="persona" class="active">{{ __('Persona') }}</label>
                    </div>

                    <div class="input-field col s12 m6">
                        <i class="fa-solid fa-user-tie prefix"></i>
                        <input type="text" id="responsable" value="{{ $movcaja->movimiento->Responsable->personamaestro->nrodoc . ' - ' .   $movcaja->movimiento->responsable ?? 'N/A' }}"
                            readonly>
                        <label for="responsable" class="active">{{ __('Responsable') }}</label>
                    </div>

                    <div class="input-field col s12 m3">
                        <input type="text" id="caja" value="{{ $movcaja->caja ?? 'N/A' }}" readonly>
                        <label for="caja" class="active">{{ __('Caja') }}</label>
                    </div>

                    <div class="input-field col s12 m3">
                        <input type="number" id="total" value="{{ number_format($movcaja->total, 2) }}" readonly>
                        <label for="total" class="active">{{ __('Total') }}</label>
                    </div>

                    <div class="input-field col s12 m3">
                        <input type="number" id="total" value="{{ number_format($movcaja->total, 2) }}" readonly>
                        <label for="total" class="active">{{ __('Pagado') }}</label>
                    </div>


                </div>

                <div class="col s12 m4">
                    <!-- Tabla de detalles -->
                    <div class="row">
                        <div class="col s12">
                            <div id="tb-container" class="table-responsive">
                                <table id="data-table" class="highlight striped centered" style="width:100%">
                                    <thead class="thead-custom">
                                        <tr style="color: white">
                                            <th>{{ __('Tipo') }}</th>
                                            <th>{{ __('Monto') }}</th>
                                            <th>{{ __('F. Venc.') }}</th>
                                            <th>{{ __('Medio de Pago') }}</th>
                                            <th>{{ __('Detalles') }}</th>
                                            <th>{{ __('Notas') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($movcaja->detMovcaja as $detalle)
                                            <tr>
                                                <td><span
                                                        class="badge {{ $detalle->tipo === 'Pagado' ? 'green' : 'red' }} white-text">{{ $detalle->tipo }}</span>
                                                </td>
                                                <td>{{ number_format($detalle->monto, 2) }}</td>
                                                <td>-</td>
                                                <td>{{ $detalle->Mediopago->descripcion ?? 'N/A' }}</td>
                                                <td>{{ $detalle->numero ?? '-' }}</td>
                                                <td>{{ $detalle->comentario ?? 'Sin notas' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comentarios -->
                <div class="input-field col s6">
                    <i class="fa-solid fa-note-sticky prefix"></i>
                
                       <textarea name="comentario" id="comentario" class="materialize-textarea validate @error('comentario') invalid @enderror" readonly
                    rows="1" style="height: auto; overflow-y: hidden; resize: vertical;"
                    oninput="this.style.height='';this.style.height=this.scrollHeight+'px'">{{ old('comentario',    isset($movcaja) ? $movcaja->movimiento->comentario : '') }}</textarea>

                    <label for="comentario" class="{{ $movcaja->movimiento->comentario ? 'active' : '' }}">{{ __('Notas al movimiento de caja') }}</label>
                </div>
                    <!-- Moneda -->
                    <div class="input-field col s12 m4">
                        <label style="margin-top:20px;">
                            <input type="radio" name="moneda" id="moneda_pen" value="PEN"
                                {{ $movcaja->moneda == 'PEN' ? 'checked' : '' }} disabled />
                            <span>S/.</span>
                        </label>
                        <label style="margin-left: 100px; margin-top:20px;">
                            <input type="radio" name="moneda" id="moneda_usd" value="USD"
                                {{ $movcaja->moneda == 'USD' ? 'checked' : '' }} disabled />
                            <span>$</span>
                        </label>
                    </div>
            </div>
            <div class="card-footer right-align">
                <a href="{{ route('movcaja.index') }}"
                    class="modal-close waves-effect waves-green btn-flat btn-cargar-crud btn-retroceder"
                    data-icono="fa fa-solid fa-cash-register left" data-titulo="Caja chica">{{ __('Cancelar') }} <i
                        class="material-icons right">clear</i>
                </a>
            </div>
        </div>
    </div>
@endsection
