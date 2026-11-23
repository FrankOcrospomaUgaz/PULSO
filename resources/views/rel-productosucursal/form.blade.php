<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="situacion" class="form-label">{{ __('Situacion') }}</label>
            <input type="text" name="situacion" class="form-control @error('situacion') is-invalid @enderror" value="{{ old('situacion', $relProductosucursal?->situacion) }}" id="situacion" placeholder="Situacion">
            {!! $errors->first('situacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechavencimiento" class="form-label">{{ __('Fechavencimiento') }}</label>
            <input type="text" name="fechavencimiento" class="form-control @error('fechavencimiento') is-invalid @enderror" value="{{ old('fechavencimiento', $relProductosucursal?->fechavencimiento) }}" id="fechavencimiento" placeholder="Fechavencimiento">
            {!! $errors->first('fechavencimiento', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="stockminimo" class="form-label">{{ __('Stockminimo') }}</label>
            <input type="text" name="stockminimo" class="form-control @error('stockminimo') is-invalid @enderror" value="{{ old('stockminimo', $relProductosucursal?->stockminimo) }}" id="stockminimo" placeholder="Stockminimo">
            {!! $errors->first('stockminimo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="stockmaximo" class="form-label">{{ __('Stockmaximo') }}</label>
            <input type="text" name="stockmaximo" class="form-control @error('stockmaximo') is-invalid @enderror" value="{{ old('stockmaximo', $relProductosucursal?->stockmaximo) }}" id="stockmaximo" placeholder="Stockmaximo">
            {!! $errors->first('stockmaximo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="stockoptimo" class="form-label">{{ __('Stockoptimo') }}</label>
            <input type="text" name="stockoptimo" class="form-control @error('stockoptimo') is-invalid @enderror" value="{{ old('stockoptimo', $relProductosucursal?->stockoptimo) }}" id="stockoptimo" placeholder="Stockoptimo">
            {!! $errors->first('stockoptimo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="minimovender" class="form-label">{{ __('Minimovender') }}</label>
            <input type="text" name="minimovender" class="form-control @error('minimovender') is-invalid @enderror" value="{{ old('minimovender', $relProductosucursal?->minimovender) }}" id="minimovender" placeholder="Minimovender">
            {!! $errors->first('minimovender', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="minimocomprar" class="form-label">{{ __('Minimocomprar') }}</label>
            <input type="text" name="minimocomprar" class="form-control @error('minimocomprar') is-invalid @enderror" value="{{ old('minimocomprar', $relProductosucursal?->minimocomprar) }}" id="minimocomprar" placeholder="Minimocomprar">
            {!! $errors->first('minimocomprar', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="ubicacion_id" class="form-label">{{ __('Ubicacion Id') }}</label>
            <input type="text" name="ubicacion_id" class="form-control @error('ubicacion_id') is-invalid @enderror" value="{{ old('ubicacion_id', $relProductosucursal?->ubicacion_id) }}" id="ubicacion_id" placeholder="Ubicacion Id">
            {!! $errors->first('ubicacion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fila" class="form-label">{{ __('Fila') }}</label>
            <input type="text" name="fila" class="form-control @error('fila') is-invalid @enderror" value="{{ old('fila', $relProductosucursal?->fila) }}" id="fila" placeholder="Fila">
            {!! $errors->first('fila', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="columna" class="form-label">{{ __('Columna') }}</label>
            <input type="text" name="columna" class="form-control @error('columna') is-invalid @enderror" value="{{ old('columna', $relProductosucursal?->columna) }}" id="columna" placeholder="Columna">
            {!! $errors->first('columna', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="favorito" class="form-label">{{ __('Favorito') }}</label>
            <input type="text" name="favorito" class="form-control @error('favorito') is-invalid @enderror" value="{{ old('favorito', $relProductosucursal?->favorito) }}" id="favorito" placeholder="Favorito">
            {!! $errors->first('favorito', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="subalmacen_id" class="form-label">{{ __('Subalmacen Id') }}</label>
            <input type="text" name="subalmacen_id" class="form-control @error('subalmacen_id') is-invalid @enderror" value="{{ old('subalmacen_id', $relProductosucursal?->subalmacen_id) }}" id="subalmacen_id" placeholder="Subalmacen Id">
            {!! $errors->first('subalmacen_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="igv_id" class="form-label">{{ __('Igv Id') }}</label>
            <input type="text" name="igv_id" class="form-control @error('igv_id') is-invalid @enderror" value="{{ old('igv_id', $relProductosucursal?->igv_id) }}" id="igv_id" placeholder="Igv Id">
            {!! $errors->first('igv_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="venta_unitaria" class="form-label">{{ __('Venta Unitaria') }}</label>
            <input type="text" name="venta_unitaria" class="form-control @error('venta_unitaria') is-invalid @enderror" value="{{ old('venta_unitaria', $relProductosucursal?->venta_unitaria) }}" id="venta_unitaria" placeholder="Venta Unitaria">
            {!! $errors->first('venta_unitaria', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="producto_id" class="form-label">{{ __('Producto Id') }}</label>
            <input type="text" name="producto_id" class="form-control @error('producto_id') is-invalid @enderror" value="{{ old('producto_id', $relProductosucursal?->producto_id) }}" id="producto_id" placeholder="Producto Id">
            {!! $errors->first('producto_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $relProductosucursal?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="duracion" class="form-label">{{ __('Duracion') }}</label>
            <input type="text" name="duracion" class="form-control @error('duracion') is-invalid @enderror" value="{{ old('duracion', $relProductosucursal?->duracion) }}" id="duracion" placeholder="Duracion">
            {!! $errors->first('duracion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="proveedor_id" class="form-label">{{ __('Proveedor Id') }}</label>
            <input type="text" name="proveedor_id" class="form-control @error('proveedor_id') is-invalid @enderror" value="{{ old('proveedor_id', $relProductosucursal?->proveedor_id) }}" id="proveedor_id" placeholder="Proveedor Id">
            {!! $errors->first('proveedor_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>