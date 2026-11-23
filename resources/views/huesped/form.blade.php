<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="persona_id" class="form-label">{{ __('Persona Id') }}</label>
            <input type="text" name="persona_id" class="form-control @error('persona_id') is-invalid @enderror" value="{{ old('persona_id', $huesped?->persona_id) }}" id="persona_id" placeholder="Persona Id">
            {!! $errors->first('persona_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="movhabitacion_id" class="form-label">{{ __('Movhabitacion Id') }}</label>
            <input type="text" name="movhabitacion_id" class="form-control @error('movhabitacion_id') is-invalid @enderror" value="{{ old('movhabitacion_id', $huesped?->movhabitacion_id) }}" id="movhabitacion_id" placeholder="Movhabitacion Id">
            {!! $errors->first('movhabitacion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>