<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="situacion_pago" class="form-label">{{ __('Situacion Pago') }}</label>
            <input type="text" name="situacion_pago" class="form-control @error('situacion_pago') is-invalid @enderror" value="{{ old('situacion_pago', $historialHabitacion?->situacion_pago) }}" id="situacion_pago" placeholder="Situacion Pago">
            {!! $errors->first('situacion_pago', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="situacion_checkout" class="form-label">{{ __('Situacion Checkout') }}</label>
            <input type="text" name="situacion_checkout" class="form-control @error('situacion_checkout') is-invalid @enderror" value="{{ old('situacion_checkout', $historialHabitacion?->situacion_checkout) }}" id="situacion_checkout" placeholder="Situacion Checkout">
            {!! $errors->first('situacion_checkout', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="movhabitacion_id" class="form-label">{{ __('Movhabitacion Id') }}</label>
            <input type="text" name="movhabitacion_id" class="form-control @error('movhabitacion_id') is-invalid @enderror" value="{{ old('movhabitacion_id', $historialHabitacion?->movhabitacion_id) }}" id="movhabitacion_id" placeholder="Movhabitacion Id">
            {!! $errors->first('movhabitacion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="relturnocaja_id" class="form-label">{{ __('Relturnocaja Id') }}</label>
            <input type="text" name="relturnocaja_id" class="form-control @error('relturnocaja_id') is-invalid @enderror" value="{{ old('relturnocaja_id', $historialHabitacion?->relturnocaja_id) }}" id="relturnocaja_id" placeholder="Relturnocaja Id">
            {!! $errors->first('relturnocaja_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $historialHabitacion?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>