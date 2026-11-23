<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="fecha" class="form-label">{{ __('Fecha') }}</label>
            <input type="text" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', $reserva?->fecha) }}" id="fecha" placeholder="Fecha">
            {!! $errors->first('fecha', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechasalida" class="form-label">{{ __('Fechasalida') }}</label>
            <input type="text" name="fechasalida" class="form-control @error('fechasalida') is-invalid @enderror" value="{{ old('fechasalida', $reserva?->fechasalida) }}" id="fechasalida" placeholder="Fechasalida">
            {!! $errors->first('fechasalida', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="observacion" class="form-label">{{ __('Observacion') }}</label>
            <input type="text" name="observacion" class="form-control @error('observacion') is-invalid @enderror" value="{{ old('observacion', $reserva?->observacion) }}" id="observacion" placeholder="Observacion">
            {!! $errors->first('observacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="situacion" class="form-label">{{ __('Situacion') }}</label>
            <input type="text" name="situacion" class="form-control @error('situacion') is-invalid @enderror" value="{{ old('situacion', $reserva?->situacion) }}" id="situacion" placeholder="Situacion">
            {!! $errors->first('situacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="persona_id" class="form-label">{{ __('Persona Id') }}</label>
            <input type="text" name="persona_id" class="form-control @error('persona_id') is-invalid @enderror" value="{{ old('persona_id', $reserva?->persona_id) }}" id="persona_id" placeholder="Persona Id">
            {!! $errors->first('persona_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $reserva?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="habitacion_id" class="form-label">{{ __('Habitacion Id') }}</label>
            <input type="text" name="habitacion_id" class="form-control @error('habitacion_id') is-invalid @enderror" value="{{ old('habitacion_id', $reserva?->habitacion_id) }}" id="habitacion_id" placeholder="Habitacion Id">
            {!! $errors->first('habitacion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>