<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="movhabitacion_id" class="form-label">{{ __('Movhabitacion Id') }}</label>
            <input type="text" name="movhabitacion_id" class="form-control @error('movhabitacion_id') is-invalid @enderror" value="{{ old('movhabitacion_id', $movhabitacionMovimiento?->movhabitacion_id) }}" id="movhabitacion_id" placeholder="Movhabitacion Id">
            {!! $errors->first('movhabitacion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="movimiento_id" class="form-label">{{ __('Movimiento Id') }}</label>
            <input type="text" name="movimiento_id" class="form-control @error('movimiento_id') is-invalid @enderror" value="{{ old('movimiento_id', $movhabitacionMovimiento?->movimiento_id) }}" id="movimiento_id" placeholder="Movimiento Id">
            {!! $errors->first('movimiento_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="dias_pagados" class="form-label">{{ __('Dias Pagados') }}</label>
            <input type="text" name="dias_pagados" class="form-control @error('dias_pagados') is-invalid @enderror" value="{{ old('dias_pagados', $movhabitacionMovimiento?->dias_pagados) }}" id="dias_pagados" placeholder="Dias Pagados">
            {!! $errors->first('dias_pagados', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="horaspagadas" class="form-label">{{ __('Horaspagadas') }}</label>
            <input type="text" name="horaspagadas" class="form-control @error('horaspagadas') is-invalid @enderror" value="{{ old('horaspagadas', $movhabitacionMovimiento?->horaspagadas) }}" id="horaspagadas" placeholder="Horaspagadas">
            {!! $errors->first('horaspagadas', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>