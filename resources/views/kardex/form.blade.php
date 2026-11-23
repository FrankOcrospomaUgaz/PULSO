<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="detalle_id" class="form-label">{{ __('Detalle Id') }}</label>
            <input type="text" name="detalle_id" class="form-control @error('detalle_id') is-invalid @enderror" value="{{ old('detalle_id', $kardex?->detalle_id) }}" id="detalle_id" placeholder="Detalle Id">
            {!! $errors->first('detalle_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="producto_id" class="form-label">{{ __('Producto Id') }}</label>
            <input type="text" name="producto_id" class="form-control @error('producto_id') is-invalid @enderror" value="{{ old('producto_id', $kardex?->producto_id) }}" id="producto_id" placeholder="Producto Id">
            {!! $errors->first('producto_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_producto" class="form-label">{{ __('Json Producto') }}</label>
            <input type="text" name="json_producto" class="form-control @error('json_producto') is-invalid @enderror" value="{{ old('json_producto', $kardex?->json_producto) }}" id="json_producto" placeholder="Json Producto">
            {!! $errors->first('json_producto', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="unidad_id" class="form-label">{{ __('Unidad Id') }}</label>
            <input type="text" name="unidad_id" class="form-control @error('unidad_id') is-invalid @enderror" value="{{ old('unidad_id', $kardex?->unidad_id) }}" id="unidad_id" placeholder="Unidad Id">
            {!! $errors->first('unidad_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_listaunidad" class="form-label">{{ __('Json Listaunidad') }}</label>
            <input type="text" name="json_listaunidad" class="form-control @error('json_listaunidad') is-invalid @enderror" value="{{ old('json_listaunidad', $kardex?->json_listaunidad) }}" id="json_listaunidad" placeholder="Json Listaunidad">
            {!! $errors->first('json_listaunidad', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="cantidad" class="form-label">{{ __('Cantidad') }}</label>
            <input type="text" name="cantidad" class="form-control @error('cantidad') is-invalid @enderror" value="{{ old('cantidad', $kardex?->cantidad) }}" id="cantidad" placeholder="Cantidad">
            {!! $errors->first('cantidad', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="unidadbase_id" class="form-label">{{ __('Unidadbase Id') }}</label>
            <input type="text" name="unidadbase_id" class="form-control @error('unidadbase_id') is-invalid @enderror" value="{{ old('unidadbase_id', $kardex?->unidadbase_id) }}" id="unidadbase_id" placeholder="Unidadbase Id">
            {!! $errors->first('unidadbase_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="cantidadbase" class="form-label">{{ __('Cantidadbase') }}</label>
            <input type="text" name="cantidadbase" class="form-control @error('cantidadbase') is-invalid @enderror" value="{{ old('cantidadbase', $kardex?->cantidadbase) }}" id="cantidadbase" placeholder="Cantidadbase">
            {!! $errors->first('cantidadbase', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="preciounitario" class="form-label">{{ __('Preciounitario') }}</label>
            <input type="text" name="preciounitario" class="form-control @error('preciounitario') is-invalid @enderror" value="{{ old('preciounitario', $kardex?->preciounitario) }}" id="preciounitario" placeholder="Preciounitario">
            {!! $errors->first('preciounitario', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="moneda" class="form-label">{{ __('Moneda') }}</label>
            <input type="text" name="moneda" class="form-control @error('moneda') is-invalid @enderror" value="{{ old('moneda', $kardex?->moneda) }}" id="moneda" placeholder="Moneda">
            {!! $errors->first('moneda', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tipocambio" class="form-label">{{ __('Tipocambio') }}</label>
            <input type="text" name="tipocambio" class="form-control @error('tipocambio') is-invalid @enderror" value="{{ old('tipocambio', $kardex?->tipocambio) }}" id="tipocambio" placeholder="Tipocambio">
            {!! $errors->first('tipocambio', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="total" class="form-label">{{ __('Total') }}</label>
            <input type="text" name="total" class="form-control @error('total') is-invalid @enderror" value="{{ old('total', $kardex?->total) }}" id="total" placeholder="Total">
            {!! $errors->first('total', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha" class="form-label">{{ __('Fecha') }}</label>
            <input type="text" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', $kardex?->fecha) }}" id="fecha" placeholder="Fecha">
            {!! $errors->first('fecha', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="situacion" class="form-label">{{ __('Situacion') }}</label>
            <input type="text" name="situacion" class="form-control @error('situacion') is-invalid @enderror" value="{{ old('situacion', $kardex?->situacion) }}" id="situacion" placeholder="Situacion">
            {!! $errors->first('situacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="usuario_id" class="form-label">{{ __('Usuario Id') }}</label>
            <input type="text" name="usuario_id" class="form-control @error('usuario_id') is-invalid @enderror" value="{{ old('usuario_id', $kardex?->usuario_id) }}" id="usuario_id" placeholder="Usuario Id">
            {!! $errors->first('usuario_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="usuario" class="form-label">{{ __('Usuario') }}</label>
            <input type="text" name="usuario" class="form-control @error('usuario') is-invalid @enderror" value="{{ old('usuario', $kardex?->usuario) }}" id="usuario" placeholder="Usuario">
            {!! $errors->first('usuario', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="movimiento_id" class="form-label">{{ __('Movimiento Id') }}</label>
            <input type="text" name="movimiento_id" class="form-control @error('movimiento_id') is-invalid @enderror" value="{{ old('movimiento_id', $kardex?->movimiento_id) }}" id="movimiento_id" placeholder="Movimiento Id">
            {!! $errors->first('movimiento_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tipomovimiento_id" class="form-label">{{ __('Tipomovimiento Id') }}</label>
            <input type="text" name="tipomovimiento_id" class="form-control @error('tipomovimiento_id') is-invalid @enderror" value="{{ old('tipomovimiento_id', $kardex?->tipomovimiento_id) }}" id="tipomovimiento_id" placeholder="Tipomovimiento Id">
            {!! $errors->first('tipomovimiento_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tipodocumento_id" class="form-label">{{ __('Tipodocumento Id') }}</label>
            <input type="text" name="tipodocumento_id" class="form-control @error('tipodocumento_id') is-invalid @enderror" value="{{ old('tipodocumento_id', $kardex?->tipodocumento_id) }}" id="tipodocumento_id" placeholder="Tipodocumento Id">
            {!! $errors->first('tipodocumento_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $kardex?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="stockanterior" class="form-label">{{ __('Stockanterior') }}</label>
            <input type="text" name="stockanterior" class="form-control @error('stockanterior') is-invalid @enderror" value="{{ old('stockanterior', $kardex?->stockanterior) }}" id="stockanterior" placeholder="Stockanterior">
            {!! $errors->first('stockanterior', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="stockactual" class="form-label">{{ __('Stockactual') }}</label>
            <input type="text" name="stockactual" class="form-control @error('stockactual') is-invalid @enderror" value="{{ old('stockactual', $kardex?->stockactual) }}" id="stockactual" placeholder="Stockactual">
            {!! $errors->first('stockactual', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>