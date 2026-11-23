<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="situacion" class="form-label">{{ __('Situacion') }}</label>
            <input type="text" name="situacion" class="form-control @error('situacion') is-invalid @enderror" value="{{ old('situacion', $permisousuario?->situacion) }}" id="situacion" placeholder="Situacion">
            {!! $errors->first('situacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="menuopcion_id" class="form-label">{{ __('Menuopcion Id') }}</label>
            <input type="text" name="menuopcion_id" class="form-control @error('menuopcion_id') is-invalid @enderror" value="{{ old('menuopcion_id', $permisousuario?->menuopcion_id) }}" id="menuopcion_id" placeholder="Menuopcion Id">
            {!! $errors->first('menuopcion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="perfil_id" class="form-label">{{ __('Perfil Id') }}</label>
            <input type="text" name="perfil_id" class="form-control @error('perfil_id') is-invalid @enderror" value="{{ old('perfil_id', $permisousuario?->perfil_id) }}" id="perfil_id" placeholder="Perfil Id">
            {!! $errors->first('perfil_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $permisousuario?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>