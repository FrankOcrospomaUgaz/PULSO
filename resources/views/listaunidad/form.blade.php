<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="formula" class="form-label">{{ __('Formula') }}</label>
            <input type="text" name="formula" class="form-control @error('formula') is-invalid @enderror" value="{{ old('formula', $listaunidad?->formula) }}" id="formula" placeholder="Formula">
            {!! $errors->first('formula', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="producto_id" class="form-label">{{ __('Producto Id') }}</label>
            <input type="text" name="producto_id" class="form-control @error('producto_id') is-invalid @enderror" value="{{ old('producto_id', $listaunidad?->producto_id) }}" id="producto_id" placeholder="Producto Id">
            {!! $errors->first('producto_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="unidad_id" class="form-label">{{ __('Unidad Id') }}</label>
            <input type="text" name="unidad_id" class="form-control @error('unidad_id') is-invalid @enderror" value="{{ old('unidad_id', $listaunidad?->unidad_id) }}" id="unidad_id" placeholder="Unidad Id">
            {!! $errors->first('unidad_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="unidadbase_id" class="form-label">{{ __('Unidadbase Id') }}</label>
            <input type="text" name="unidadbase_id" class="form-control @error('unidadbase_id') is-invalid @enderror" value="{{ old('unidadbase_id', $listaunidad?->unidadbase_id) }}" id="unidadbase_id" placeholder="Unidadbase Id">
            {!! $errors->first('unidadbase_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="empresa_id" class="form-label">{{ __('Empresa Id') }}</label>
            <input type="text" name="empresa_id" class="form-control @error('empresa_id') is-invalid @enderror" value="{{ old('empresa_id', $listaunidad?->empresa_id) }}" id="empresa_id" placeholder="Empresa Id">
            {!! $errors->first('empresa_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>