<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="tipo_detalle" class="form-label">{{ __('Tipo Detalle') }}</label>
            <input type="text" name="tipo_detalle" class="form-control @error('tipo_detalle') is-invalid @enderror" value="{{ old('tipo_detalle', $detMovventum?->tipo_detalle) }}" id="tipo_detalle" placeholder="Tipo Detalle">
            {!! $errors->first('tipo_detalle', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="movventa_id" class="form-label">{{ __('Movventa Id') }}</label>
            <input type="text" name="movventa_id" class="form-control @error('movventa_id') is-invalid @enderror" value="{{ old('movventa_id', $detMovventum?->movventa_id) }}" id="movventa_id" placeholder="Movventa Id">
            {!! $errors->first('movventa_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="codigo" class="form-label">{{ __('Codigo') }}</label>
            <input type="text" name="codigo" class="form-control @error('codigo') is-invalid @enderror" value="{{ old('codigo', $detMovventum?->codigo) }}" id="codigo" placeholder="Codigo">
            {!! $errors->first('codigo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="descripcion" class="form-label">{{ __('Descripcion') }}</label>
            <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion', $detMovventum?->descripcion) }}" id="descripcion" placeholder="Descripcion">
            {!! $errors->first('descripcion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="producto_id" class="form-label">{{ __('Producto Id') }}</label>
            <input type="text" name="producto_id" class="form-control @error('producto_id') is-invalid @enderror" value="{{ old('producto_id', $detMovventum?->producto_id) }}" id="producto_id" placeholder="Producto Id">
            {!! $errors->first('producto_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_producto" class="form-label">{{ __('Json Producto') }}</label>
            <input type="text" name="json_producto" class="form-control @error('json_producto') is-invalid @enderror" value="{{ old('json_producto', $detMovventum?->json_producto) }}" id="json_producto" placeholder="Json Producto">
            {!! $errors->first('json_producto', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_medidaproducto" class="form-label">{{ __('Json Medidaproducto') }}</label>
            <input type="text" name="json_medidaproducto" class="form-control @error('json_medidaproducto') is-invalid @enderror" value="{{ old('json_medidaproducto', $detMovventum?->json_medidaproducto) }}" id="json_medidaproducto" placeholder="Json Medidaproducto">
            {!! $errors->first('json_medidaproducto', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="unidad_id" class="form-label">{{ __('Unidad Id') }}</label>
            <input type="text" name="unidad_id" class="form-control @error('unidad_id') is-invalid @enderror" value="{{ old('unidad_id', $detMovventum?->unidad_id) }}" id="unidad_id" placeholder="Unidad Id">
            {!! $errors->first('unidad_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_unidad" class="form-label">{{ __('Json Unidad') }}</label>
            <input type="text" name="json_unidad" class="form-control @error('json_unidad') is-invalid @enderror" value="{{ old('json_unidad', $detMovventum?->json_unidad) }}" id="json_unidad" placeholder="Json Unidad">
            {!! $errors->first('json_unidad', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="igv_id" class="form-label">{{ __('Igv Id') }}</label>
            <input type="text" name="igv_id" class="form-control @error('igv_id') is-invalid @enderror" value="{{ old('igv_id', $detMovventum?->igv_id) }}" id="igv_id" placeholder="Igv Id">
            {!! $errors->first('igv_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_igv" class="form-label">{{ __('Json Igv') }}</label>
            <input type="text" name="json_igv" class="form-control @error('json_igv') is-invalid @enderror" value="{{ old('json_igv', $detMovventum?->json_igv) }}" id="json_igv" placeholder="Json Igv">
            {!! $errors->first('json_igv', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="cantidad" class="form-label">{{ __('Cantidad') }}</label>
            <input type="text" name="cantidad" class="form-control @error('cantidad') is-invalid @enderror" value="{{ old('cantidad', $detMovventum?->cantidad) }}" id="cantidad" placeholder="Cantidad">
            {!! $errors->first('cantidad', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="monto" class="form-label">{{ __('Monto') }}</label>
            <input type="text" name="monto" class="form-control @error('monto') is-invalid @enderror" value="{{ old('monto', $detMovventum?->monto) }}" id="monto" placeholder="Monto">
            {!! $errors->first('monto', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="dcto_porcentaje" class="form-label">{{ __('Dcto Porcentaje') }}</label>
            <input type="text" name="dcto_porcentaje" class="form-control @error('dcto_porcentaje') is-invalid @enderror" value="{{ old('dcto_porcentaje', $detMovventum?->dcto_porcentaje) }}" id="dcto_porcentaje" placeholder="Dcto Porcentaje">
            {!! $errors->first('dcto_porcentaje', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="monto_original" class="form-label">{{ __('Monto Original') }}</label>
            <input type="text" name="monto_original" class="form-control @error('monto_original') is-invalid @enderror" value="{{ old('monto_original', $detMovventum?->monto_original) }}" id="monto_original" placeholder="Monto Original">
            {!! $errors->first('monto_original', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="comentario" class="form-label">{{ __('Comentario') }}</label>
            <input type="text" name="comentario" class="form-control @error('comentario') is-invalid @enderror" value="{{ old('comentario', $detMovventum?->comentario) }}" id="comentario" placeholder="Comentario">
            {!! $errors->first('comentario', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="det_categoria" class="form-label">{{ __('Det Categoria') }}</label>
            <input type="text" name="det_categoria" class="form-control @error('det_categoria') is-invalid @enderror" value="{{ old('det_categoria', $detMovventum?->det_categoria) }}" id="det_categoria" placeholder="Det Categoria">
            {!! $errors->first('det_categoria', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="opc_categoria" class="form-label">{{ __('Opc Categoria') }}</label>
            <input type="text" name="opc_categoria" class="form-control @error('opc_categoria') is-invalid @enderror" value="{{ old('opc_categoria', $detMovventum?->opc_categoria) }}" id="opc_categoria" placeholder="Opc Categoria">
            {!! $errors->first('opc_categoria', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="det_movventa_id" class="form-label">{{ __('Det Movventa Id') }}</label>
            <input type="text" name="det_movventa_id" class="form-control @error('det_movventa_id') is-invalid @enderror" value="{{ old('det_movventa_id', $detMovventum?->det_movventa_id) }}" id="det_movventa_id" placeholder="Det Movventa Id">
            {!! $errors->first('det_movventa_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="complementos" class="form-label">{{ __('Complementos') }}</label>
            <input type="text" name="complementos" class="form-control @error('complementos') is-invalid @enderror" value="{{ old('complementos', $detMovventum?->complementos) }}" id="complementos" placeholder="Complementos">
            {!! $errors->first('complementos', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="situacion" class="form-label">{{ __('Situacion') }}</label>
            <input type="text" name="situacion" class="form-control @error('situacion') is-invalid @enderror" value="{{ old('situacion', $detMovventum?->situacion) }}" id="situacion" placeholder="Situacion">
            {!! $errors->first('situacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $detMovventum?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>