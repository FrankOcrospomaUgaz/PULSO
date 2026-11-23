<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="det_encuesta_id" class="form-label">{{ __('Det Encuesta Id') }}</label>
            <input type="text" name="det_encuesta_id" class="form-control @error('det_encuesta_id') is-invalid @enderror" value="{{ old('det_encuesta_id', $detRespuestum?->det_encuesta_id) }}" id="det_encuesta_id" placeholder="Det Encuesta Id">
            {!! $errors->first('det_encuesta_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="respuesta_id" class="form-label">{{ __('Respuesta Id') }}</label>
            <input type="text" name="respuesta_id" class="form-control @error('respuesta_id') is-invalid @enderror" value="{{ old('respuesta_id', $detRespuestum?->respuesta_id) }}" id="respuesta_id" placeholder="Respuesta Id">
            {!! $errors->first('respuesta_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $detRespuestum?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>