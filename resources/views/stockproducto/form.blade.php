<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="stockbase" class="form-label">{{ __('Stockbase') }}</label>
            <input type="text" name="stockbase" class="form-control @error('stockbase') is-invalid @enderror" value="{{ old('stockbase', $stockproducto?->stockbase) }}" id="stockbase" placeholder="Stockbase">
            {!! $errors->first('stockbase', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="producto_id" class="form-label">{{ __('Producto Id') }}</label>
            <input type="text" name="producto_id" class="form-control @error('producto_id') is-invalid @enderror" value="{{ old('producto_id', $stockproducto?->producto_id) }}" id="producto_id" placeholder="Producto Id">
            {!! $errors->first('producto_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="unidadbase_id" class="form-label">{{ __('Unidadbase Id') }}</label>
            <input type="text" name="unidadbase_id" class="form-control @error('unidadbase_id') is-invalid @enderror" value="{{ old('unidadbase_id', $stockproducto?->unidadbase_id) }}" id="unidadbase_id" placeholder="Unidadbase Id">
            {!! $errors->first('unidadbase_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $stockproducto?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>