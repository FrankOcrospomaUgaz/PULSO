<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="descuento" class="form-label">{{ __('Descuento') }}</label>
            <input type="text" name="descuento" class="form-control @error('descuento') is-invalid @enderror" value="{{ old('descuento', $movhabitacion?->descuento) }}" id="descuento" placeholder="Descuento">
            {!! $errors->first('descuento', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechaingreso" class="form-label">{{ __('Fechaingreso') }}</label>
            <input type="text" name="fechaingreso" class="form-control @error('fechaingreso') is-invalid @enderror" value="{{ old('fechaingreso', $movhabitacion?->fechaingreso) }}" id="fechaingreso" placeholder="Fechaingreso">
            {!! $errors->first('fechaingreso', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechasalida" class="form-label">{{ __('Fechasalida') }}</label>
            <input type="text" name="fechasalida" class="form-control @error('fechasalida') is-invalid @enderror" value="{{ old('fechasalida', $movhabitacion?->fechasalida) }}" id="fechasalida" placeholder="Fechasalida">
            {!! $errors->first('fechasalida', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="dias" class="form-label">{{ __('Dias') }}</label>
            <input type="text" name="dias" class="form-control @error('dias') is-invalid @enderror" value="{{ old('dias', $movhabitacion?->dias) }}" id="dias" placeholder="Dias">
            {!! $errors->first('dias', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="total" class="form-label">{{ __('Total') }}</label>
            <input type="text" name="total" class="form-control @error('total') is-invalid @enderror" value="{{ old('total', $movhabitacion?->total) }}" id="total" placeholder="Total">
            {!! $errors->first('total', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="preciohabitacion" class="form-label">{{ __('Preciohabitacion') }}</label>
            <input type="text" name="preciohabitacion" class="form-control @error('preciohabitacion') is-invalid @enderror" value="{{ old('preciohabitacion', $movhabitacion?->preciohabitacion) }}" id="preciohabitacion" placeholder="Preciohabitacion">
            {!! $errors->first('preciohabitacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="situacion" class="form-label">{{ __('Situacion') }}</label>
            <input type="text" name="situacion" class="form-control @error('situacion') is-invalid @enderror" value="{{ old('situacion', $movhabitacion?->situacion) }}" id="situacion" placeholder="Situacion">
            {!! $errors->first('situacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="comentario" class="form-label">{{ __('Comentario') }}</label>
            <input type="text" name="comentario" class="form-control @error('comentario') is-invalid @enderror" value="{{ old('comentario', $movhabitacion?->comentario) }}" id="comentario" placeholder="Comentario">
            {!! $errors->first('comentario', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="moneda" class="form-label">{{ __('Moneda') }}</label>
            <input type="text" name="moneda" class="form-control @error('moneda') is-invalid @enderror" value="{{ old('moneda', $movhabitacion?->moneda) }}" id="moneda" placeholder="Moneda">
            {!! $errors->first('moneda', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="habitacion_id" class="form-label">{{ __('Habitacion Id') }}</label>
            <input type="text" name="habitacion_id" class="form-control @error('habitacion_id') is-invalid @enderror" value="{{ old('habitacion_id', $movhabitacion?->habitacion_id) }}" id="habitacion_id" placeholder="Habitacion Id">
            {!! $errors->first('habitacion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="reserva_id" class="form-label">{{ __('Reserva Id') }}</label>
            <input type="text" name="reserva_id" class="form-control @error('reserva_id') is-invalid @enderror" value="{{ old('reserva_id', $movhabitacion?->reserva_id) }}" id="reserva_id" placeholder="Reserva Id">
            {!! $errors->first('reserva_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="movimiento_id" class="form-label">{{ __('Movimiento Id') }}</label>
            <input type="text" name="movimiento_id" class="form-control @error('movimiento_id') is-invalid @enderror" value="{{ old('movimiento_id', $movhabitacion?->movimiento_id) }}" id="movimiento_id" placeholder="Movimiento Id">
            {!! $errors->first('movimiento_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="early_checkin" class="form-label">{{ __('Early Checkin') }}</label>
            <input type="text" name="early_checkin" class="form-control @error('early_checkin') is-invalid @enderror" value="{{ old('early_checkin', $movhabitacion?->early_checkin) }}" id="early_checkin" placeholder="Early Checkin">
            {!! $errors->first('early_checkin', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="late_checkout" class="form-label">{{ __('Late Checkout') }}</label>
            <input type="text" name="late_checkout" class="form-control @error('late_checkout') is-invalid @enderror" value="{{ old('late_checkout', $movhabitacion?->late_checkout) }}" id="late_checkout" placeholder="Late Checkout">
            {!! $errors->first('late_checkout', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="day_use" class="form-label">{{ __('Day Use') }}</label>
            <input type="text" name="day_use" class="form-control @error('day_use') is-invalid @enderror" value="{{ old('day_use', $movhabitacion?->day_use) }}" id="day_use" placeholder="Day Use">
            {!! $errors->first('day_use', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $movhabitacion?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>