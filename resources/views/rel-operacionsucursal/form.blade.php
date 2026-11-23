<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="orden" class="form-label">{{ __('Orden') }}</label>
            <input type="text" name="orden" class="form-control @error('orden') is-invalid @enderror" value="{{ old('orden', $relOperacionsucursal?->orden) }}" id="orden" placeholder="Orden">
            {!! $errors->first('orden', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="situacion" class="form-label">{{ __('Situacion') }}</label>
            <input type="text" name="situacion" class="form-control @error('situacion') is-invalid @enderror" value="{{ old('situacion', $relOperacionsucursal?->situacion) }}" id="situacion" placeholder="Situacion">
            {!! $errors->first('situacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="operacion_id" class="form-label">{{ __('Operacion Id') }}</label>
            <input type="text" name="operacion_id" class="form-control @error('operacion_id') is-invalid @enderror" value="{{ old('operacion_id', $relOperacionsucursal?->operacion_id) }}" id="operacion_id" placeholder="Operacion Id">
            {!! $errors->first('operacion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tabla_id" class="form-label">{{ __('Tabla Id') }}</label>
            <input type="text" name="tabla_id" class="form-control @error('tabla_id') is-invalid @enderror" value="{{ old('tabla_id', $relOperacionsucursal?->tabla_id) }}" id="tabla_id" placeholder="Tabla Id">
            {!! $errors->first('tabla_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $relOperacionsucursal?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>