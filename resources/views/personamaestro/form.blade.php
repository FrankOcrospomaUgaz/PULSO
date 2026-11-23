<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="tipopersona" class="form-label">{{ __('Tipopersona') }}</label>
            <input type="text" name="tipopersona" class="form-control @error('tipopersona') is-invalid @enderror" value="{{ old('tipopersona', $personamaestro?->tipopersona) }}" id="tipopersona" placeholder="Tipopersona">
            {!! $errors->first('tipopersona', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="nombres" class="form-label">{{ __('Nombres') }}</label>
            <input type="text" name="nombres" class="form-control @error('nombres') is-invalid @enderror" value="{{ old('nombres', $personamaestro?->nombres) }}" id="nombres" placeholder="Nombres">
            {!! $errors->first('nombres', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="apellidos" class="form-label">{{ __('Apellidos') }}</label>
            <input type="text" name="apellidos" class="form-control @error('apellidos') is-invalid @enderror" value="{{ old('apellidos', $personamaestro?->apellidos) }}" id="apellidos" placeholder="Apellidos">
            {!! $errors->first('apellidos', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="nrodoc" class="form-label">{{ __('Nrodoc') }}</label>
            <input type="text" name="nrodoc" class="form-control @error('nrodoc') is-invalid @enderror" value="{{ old('nrodoc', $personamaestro?->nrodoc) }}" id="nrodoc" placeholder="Nrodoc">
            {!! $errors->first('nrodoc', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sexo" class="form-label">{{ __('Sexo') }}</label>
            <input type="text" name="sexo" class="form-control @error('sexo') is-invalid @enderror" value="{{ old('sexo', $personamaestro?->sexo) }}" id="sexo" placeholder="Sexo">
            {!! $errors->first('sexo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechanac" class="form-label">{{ __('Fechanac') }}</label>
            <input type="text" name="fechanac" class="form-control @error('fechanac') is-invalid @enderror" value="{{ old('fechanac', $personamaestro?->fechanac) }}" id="fechanac" placeholder="Fechanac">
            {!! $errors->first('fechanac', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>