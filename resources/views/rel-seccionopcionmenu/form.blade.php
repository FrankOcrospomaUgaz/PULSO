<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="seccion_id" class="form-label">{{ __('Seccion Id') }}</label>
            <input type="text" name="seccion_id" class="form-control @error('seccion_id') is-invalid @enderror" value="{{ old('seccion_id', $relSeccionopcionmenu?->seccion_id) }}" id="seccion_id" placeholder="Seccion Id">
            {!! $errors->first('seccion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="opcionmenu_id" class="form-label">{{ __('Opcionmenu Id') }}</label>
            <input type="text" name="opcionmenu_id" class="form-control @error('opcionmenu_id') is-invalid @enderror" value="{{ old('opcionmenu_id', $relSeccionopcionmenu?->opcionmenu_id) }}" id="opcionmenu_id" placeholder="Opcionmenu Id">
            {!! $errors->first('opcionmenu_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>