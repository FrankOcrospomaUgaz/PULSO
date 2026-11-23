<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="movreserva_id" class="form-label">{{ __('Movreserva Id') }}</label>
            <input type="text" name="movreserva_id" class="form-control @error('movreserva_id') is-invalid @enderror" value="{{ old('movreserva_id', $movreservaHabitacion?->movreserva_id) }}" id="movreserva_id" placeholder="Movreserva Id">
            {!! $errors->first('movreserva_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="habitacion_id" class="form-label">{{ __('Habitacion Id') }}</label>
            <input type="text" name="habitacion_id" class="form-control @error('habitacion_id') is-invalid @enderror" value="{{ old('habitacion_id', $movreservaHabitacion?->habitacion_id) }}" id="habitacion_id" placeholder="Habitacion Id">
            {!! $errors->first('habitacion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="principal" class="form-label">{{ __('Principal') }}</label>
            <input type="text" name="principal" class="form-control @error('principal') is-invalid @enderror" value="{{ old('principal', $movreservaHabitacion?->principal) }}" id="principal" placeholder="Principal">
            {!! $errors->first('principal', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tarifa" class="form-label">{{ __('Tarifa') }}</label>
            <input type="text" name="tarifa" class="form-control @error('tarifa') is-invalid @enderror" value="{{ old('tarifa', $movreservaHabitacion?->tarifa) }}" id="tarifa" placeholder="Tarifa">
            {!! $errors->first('tarifa', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_habitacion" class="form-label">{{ __('Json Habitacion') }}</label>
            <input type="text" name="json_habitacion" class="form-control @error('json_habitacion') is-invalid @enderror" value="{{ old('json_habitacion', $movreservaHabitacion?->json_habitacion) }}" id="json_habitacion" placeholder="Json Habitacion">
            {!! $errors->first('json_habitacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>