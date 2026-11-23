<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="persona_id" class="form-label">{{ __('Persona Id') }}</label>
            <input type="text" name="persona_id" class="form-control @error('persona_id') is-invalid @enderror" value="{{ old('persona_id', $rolpersona?->persona_id) }}" id="persona_id" placeholder="Persona Id">
            {!! $errors->first('persona_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="rol_id" class="form-label">{{ __('Rol Id') }}</label>
            <input type="text" name="rol_id" class="form-control @error('rol_id') is-invalid @enderror" value="{{ old('rol_id', $rolpersona?->rol_id) }}" id="rol_id" placeholder="Rol Id">
            {!! $errors->first('rol_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $rolpersona?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>