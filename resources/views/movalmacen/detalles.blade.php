@extends('layouts.app')

@section('content')
<div class="card main-content">

    <div class="row padding-1 p-1">
        <div class="col s12 m6">
        <!-- Select para 'tipo de movimiento' -->
        <div class="input-field col s12 m3">
            <select name="tipo_documento" id="tipo_documento" disabled>
                @foreach ($tipos_documento as $tipo_documento)
                    <option value="{{ $tipo_documento->id }}"
                        {{ $movalmacen->movimiento->tipodocumento_id == $tipo_documento->id ? 'selected' : '' }}>
                        {{ $tipo_documento->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="tipo_documento">{{ __('Tipo') }}</label>
        </div>

        <!-- Campo de fecha para 'fecha' -->
        <div class="input-field col s12 m3">
            <i class="fa-solid fa-calendar-day prefix"></i>
            <input type="text" id="fecha" value="{{ $movalmacen->movimiento->fecha }}" disabled>
            <label for="fecha" class="active">{{ __('Fecha') }}</label>
        </div>

        <!-- Subalmacén -->
        <div class="input-field col s12 m6">
            <i class="fa-solid fa-warehouse prefix"></i>
            <select name="subalmacen_id" id="subalmacen_id" disabled>
                <option value="">Ninguno</option>
                @foreach ($subalmacenes as $subalmacen)
                    <option value="{{ $subalmacen->id }}"
                        {{ $movalmacen->sucursal_id == $subalmacen->id ? 'selected' : '' }}>
                        {{ $subalmacen->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="subalmacen_id">{{ __('Subalmacén') }}</label>
        </div>

        <!-- Persona -->
        <div class="input-field col s12 m5">
            <input type="text" id="persona_autocomplete"
                value="{{ $movalmacen->movimiento->persona ?? '0 - VARIOS CLIENTES' }}" disabled>
            <label for="persona_autocomplete" class="active">{{ __('Persona') }}</label>
        </div>

        <!-- Responsable -->
        <div class="input-field col s12 m6">
            <i class="fa-solid fa-user-tie prefix"></i>
            <select name="responsable_id" id="responsable_id" disabled>
                @foreach ($responsables as $responsable)
                    <option value="{{ $responsable->id }}"
                        {{ $movalmacen->movimiento->responsable_id == $responsable->id ? 'selected' : '' }}>
                        {{ $responsable->personamaestro->nrodoc }} - {{ $responsable->apellidos }} {{ $responsable->nombres }}
                    </option>
                @endforeach
            </select>
            <label for="responsable_id">{{ __('Responsable') }}</label>
        </div>

        <!-- Comentarios -->
        <div class="input-field col s12">
            <i class="fa-solid fa-note-sticky prefix"></i>
            <textarea id="comentario" class="materialize-textarea" disabled>{{ $movalmacen->movimiento->comentario }}</textarea>
            <label for="comentario" class="active">{{ __('Notas al movimiento de almacén') }}</label>
        </div>
    </div>  

    <!-- Tabla de detalle de productos -->
    <div class="col s12 m6">
        <div class="table-responsive">
            <table class="highlight striped centered">
                <thead class="bg-primary">
                    <tr style="color: white;">
                        <th>Producto</th>
                        <th>Unidad</th>
                        <th>Cantidad</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->producto->codigo }} - {{ $detalle->producto->descripcion }}</td>
                            <td>{{ $detalle->unidad->descripcion }}</td>
                            <td><strong>{{ $detalle->cantidad }}</strong></td>
                            <td>{{ $detalle->comentario ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12 right-align">
        <a href="#" class="btn-flat waves-effect">{{ __('CANCELAR') }}</a>
    </div>
</div>
</div>
<!-- Botón de Cancelar -->

@endsection
