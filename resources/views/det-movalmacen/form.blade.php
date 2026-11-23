<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="movalmacen_id" class="form-label">{{ __('Movalmacen Id') }}</label>
            <input type="text" name="movalmacen_id" class="form-control @error('movalmacen_id') is-invalid @enderror" value="{{ old('movalmacen_id', $detMovalmacen?->movalmacen_id) }}" id="movalmacen_id" placeholder="Movalmacen Id">
            {!! $errors->first('movalmacen_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="producto_id" class="form-label">{{ __('Producto Id') }}</label>
            <input type="text" name="producto_id" class="form-control @error('producto_id') is-invalid @enderror" value="{{ old('producto_id', $detMovalmacen?->producto_id) }}" id="producto_id" placeholder="Producto Id">
            {!! $errors->first('producto_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_producto" class="form-label">{{ __('Json Producto') }}</label>
            <input type="text" name="json_producto" class="form-control @error('json_producto') is-invalid @enderror" value="{{ old('json_producto', $detMovalmacen?->json_producto) }}" id="json_producto" placeholder="Json Producto">
            {!! $errors->first('json_producto', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="unidad_id" class="form-label">{{ __('Unidad Id') }}</label>
            <input type="text" name="unidad_id" class="form-control @error('unidad_id') is-invalid @enderror" value="{{ old('unidad_id', $detMovalmacen?->unidad_id) }}" id="unidad_id" placeholder="Unidad Id">
            {!! $errors->first('unidad_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_listaunidad" class="form-label">{{ __('Json Listaunidad') }}</label>
            <input type="text" name="json_listaunidad" class="form-control @error('json_listaunidad') is-invalid @enderror" value="{{ old('json_listaunidad', $detMovalmacen?->json_listaunidad) }}" id="json_listaunidad" placeholder="Json Listaunidad">
            {!! $errors->first('json_listaunidad', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="cantidad" class="form-label">{{ __('Cantidad') }}</label>
            <input type="text" name="cantidad" class="form-control @error('cantidad') is-invalid @enderror" value="{{ old('cantidad', $detMovalmacen?->cantidad) }}" id="cantidad" placeholder="Cantidad">
            {!! $errors->first('cantidad', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="comentario" class="form-label">{{ __('Comentario') }}</label>
            <input type="text" name="comentario" class="form-control @error('comentario') is-invalid @enderror" value="{{ old('comentario', $detMovalmacen?->comentario) }}" id="comentario" placeholder="Comentario">
            {!! $errors->first('comentario', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="situacion" class="form-label">{{ __('Situacion') }}</label>
            <input type="text" name="situacion" class="form-control @error('situacion') is-invalid @enderror" value="{{ old('situacion', $detMovalmacen?->situacion) }}" id="situacion" placeholder="Situacion">
            {!! $errors->first('situacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $detMovalmacen?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>