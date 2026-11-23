<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="preciocompra" class="form-label">{{ __('Preciocompra') }}</label>
            <input type="text" name="preciocompra" class="form-control @error('preciocompra') is-invalid @enderror" value="{{ old('preciocompra', $relListaunidadsucursal?->preciocompra) }}" id="preciocompra" placeholder="Preciocompra">
            {!! $errors->first('preciocompra', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="preciomanoobra" class="form-label">{{ __('Preciomanoobra') }}</label>
            <input type="text" name="preciomanoobra" class="form-control @error('preciomanoobra') is-invalid @enderror" value="{{ old('preciomanoobra', $relListaunidadsucursal?->preciomanoobra) }}" id="preciomanoobra" placeholder="Preciomanoobra">
            {!! $errors->first('preciomanoobra', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="precioventa" class="form-label">{{ __('Precioventa') }}</label>
            <input type="text" name="precioventa" class="form-control @error('precioventa') is-invalid @enderror" value="{{ old('precioventa', $relListaunidadsucursal?->precioventa) }}" id="precioventa" placeholder="Precioventa">
            {!! $errors->first('precioventa', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="moneda" class="form-label">{{ __('Moneda') }}</label>
            <input type="text" name="moneda" class="form-control @error('moneda') is-invalid @enderror" value="{{ old('moneda', $relListaunidadsucursal?->moneda) }}" id="moneda" placeholder="Moneda">
            {!! $errors->first('moneda', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="listaunidad_id" class="form-label">{{ __('Listaunidad Id') }}</label>
            <input type="text" name="listaunidad_id" class="form-control @error('listaunidad_id') is-invalid @enderror" value="{{ old('listaunidad_id', $relListaunidadsucursal?->listaunidad_id) }}" id="listaunidad_id" placeholder="Listaunidad Id">
            {!! $errors->first('listaunidad_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $relListaunidadsucursal?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>