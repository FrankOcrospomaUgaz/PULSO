<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="nombre" class="form-label">{{ __('Nombre') }}</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $amenity?->nombre) }}" id="nombre" placeholder="Nombre">
            {!! $errors->first('nombre', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="icon" class="form-label">{{ __('Icon') }}</label>
            <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon', $amenity?->icon) }}" id="icon" placeholder="Icon">
            {!! $errors->first('icon', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tipohabitacion_id" class="form-label">{{ __('Tipohabitacion Id') }}</label>
            <input type="text" name="tipohabitacion_id" class="form-control @error('tipohabitacion_id') is-invalid @enderror" value="{{ old('tipohabitacion_id', $amenity?->tipohabitacion_id) }}" id="tipohabitacion_id" placeholder="Tipohabitacion Id">
            {!! $errors->first('tipohabitacion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>