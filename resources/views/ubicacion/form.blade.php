<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="codigo" class="form-label">{{ __('Codigo') }}</label>
            <input type="text" name="codigo" class="form-control @error('codigo') is-invalid @enderror" value="{{ old('codigo', $ubicacion?->codigo) }}" id="codigo" placeholder="Codigo">
            {!! $errors->first('codigo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="descripcion" class="form-label">{{ __('Descripcion') }}</label>
            <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion', $ubicacion?->descripcion) }}" id="descripcion" placeholder="Descripcion">
            {!! $errors->first('descripcion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="totalfilas" class="form-label">{{ __('Totalfilas') }}</label>
            <input type="text" name="totalfilas" class="form-control @error('totalfilas') is-invalid @enderror" value="{{ old('totalfilas', $ubicacion?->totalfilas) }}" id="totalfilas" placeholder="Totalfilas">
            {!! $errors->first('totalfilas', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="totalcolumnas" class="form-label">{{ __('Totalcolumnas') }}</label>
            <input type="text" name="totalcolumnas" class="form-control @error('totalcolumnas') is-invalid @enderror" value="{{ old('totalcolumnas', $ubicacion?->totalcolumnas) }}" id="totalcolumnas" placeholder="Totalcolumnas">
            {!! $errors->first('totalcolumnas', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>