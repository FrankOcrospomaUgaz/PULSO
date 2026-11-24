@extends('layouts.app')

@section('template_title', 'Emitir nota de crédito')

@section('content')
<section class="content container-fluid">
    <div class="row">
        <div class="col s12 l8 offset-l2">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Emitir nota de crédito electrónica</span>
                    <p>Documento afectado: <strong>{{ $comprobante->tipo_documento === 'F' ? 'Factura' : 'Boleta' }} {{ $comprobante->serie }}-{{ str_pad((string) $comprobante->correlativo, 8, '0', STR_PAD_LEFT) }}</strong></p>
                    <p>Total afectado: <strong>{{ $comprobante->moneda }} {{ number_format((float) $comprobante->total, 2) }}</strong></p>

                    @if ($errors->any())
                        <div class="card-panel red lighten-5 red-text text-darken-3">
                            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('solicitud.notaCredito.store', $comprobante->id) }}">
                        @csrf
                        <div class="input-field">
                            <select name="tipo_nota" required>
                                @foreach ($tiposNota as $codigo => $descripcion)
                                    <option value="{{ $codigo }}" @selected(old('tipo_nota') === $codigo)>{{ $codigo }} — {{ $descripcion }}</option>
                                @endforeach
                            </select>
                            <label>Motivo tributario</label>
                        </div>
                        <div class="input-field">
                            <textarea name="motivo" class="materialize-textarea" minlength="5" maxlength="500" required>{{ old('motivo') }}</textarea>
                            <label class="active">Sustento de la nota</label>
                        </div>
                        <p class="grey-text text-darken-1">Este flujo emite notas por el total del comprobante. Confirma el motivo y revisa el documento antes de enviarlo al facturador.</p>
                        <button class="btn waves-effect waves-light" type="submit"><i class="material-icons left">send</i>Enviar nota al facturador</button>
                        <a class="btn-flat" href="{{ route('solicitud.index') }}">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>document.addEventListener('DOMContentLoaded',function(){var elems=document.querySelectorAll('select');if(window.M)M.FormSelect.init(elems);});</script>
@endpush
