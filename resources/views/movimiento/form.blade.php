<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="numero" class="form-label">{{ __('Numero') }}</label>
            <input type="text" name="numero" class="form-control @error('numero') is-invalid @enderror" value="{{ old('numero', $movimiento?->numero) }}" id="numero" placeholder="Numero">
            {!! $errors->first('numero', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha" class="form-label">{{ __('Fecha') }}</label>
            <input type="text" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', $movimiento?->fecha) }}" id="fecha" placeholder="Fecha">
            {!! $errors->first('fecha', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="usuario_id" class="form-label">{{ __('Usuario Id') }}</label>
            <input type="text" name="usuario_id" class="form-control @error('usuario_id') is-invalid @enderror" value="{{ old('usuario_id', $movimiento?->usuario_id) }}" id="usuario_id" placeholder="Usuario Id">
            {!! $errors->first('usuario_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="usuario" class="form-label">{{ __('Usuario') }}</label>
            <input type="text" name="usuario" class="form-control @error('usuario') is-invalid @enderror" value="{{ old('usuario', $movimiento?->usuario) }}" id="usuario" placeholder="Usuario">
            {!! $errors->first('usuario', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="persona_id" class="form-label">{{ __('Persona Id') }}</label>
            <input type="text" name="persona_id" class="form-control @error('persona_id') is-invalid @enderror" value="{{ old('persona_id', $movimiento?->persona_id) }}" id="persona_id" placeholder="Persona Id">
            {!! $errors->first('persona_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="persona" class="form-label">{{ __('Persona') }}</label>
            <input type="text" name="persona" class="form-control @error('persona') is-invalid @enderror" value="{{ old('persona', $movimiento?->persona) }}" id="persona" placeholder="Persona">
            {!! $errors->first('persona', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="responsable_id" class="form-label">{{ __('Responsable Id') }}</label>
            <input type="text" name="responsable_id" class="form-control @error('responsable_id') is-invalid @enderror" value="{{ old('responsable_id', $movimiento?->responsable_id) }}" id="responsable_id" placeholder="Responsable Id">
            {!! $errors->first('responsable_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="responsable" class="form-label">{{ __('Responsable') }}</label>
            <input type="text" name="responsable" class="form-control @error('responsable') is-invalid @enderror" value="{{ old('responsable', $movimiento?->responsable) }}" id="responsable" placeholder="Responsable">
            {!! $errors->first('responsable', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="comentario" class="form-label">{{ __('Comentario') }}</label>
            <input type="text" name="comentario" class="form-control @error('comentario') is-invalid @enderror" value="{{ old('comentario', $movimiento?->comentario) }}" id="comentario" placeholder="Comentario">
            {!! $errors->first('comentario', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="situacion" class="form-label">{{ __('Situacion') }}</label>
            <input type="text" name="situacion" class="form-control @error('situacion') is-invalid @enderror" value="{{ old('situacion', $movimiento?->situacion) }}" id="situacion" placeholder="Situacion">
            {!! $errors->first('situacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tipomovimiento_id" class="form-label">{{ __('Tipomovimiento Id') }}</label>
            <input type="text" name="tipomovimiento_id" class="form-control @error('tipomovimiento_id') is-invalid @enderror" value="{{ old('tipomovimiento_id', $movimiento?->tipomovimiento_id) }}" id="tipomovimiento_id" placeholder="Tipomovimiento Id">
            {!! $errors->first('tipomovimiento_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tipodocumento_id" class="form-label">{{ __('Tipodocumento Id') }}</label>
            <input type="text" name="tipodocumento_id" class="form-control @error('tipodocumento_id') is-invalid @enderror" value="{{ old('tipodocumento_id', $movimiento?->tipodocumento_id) }}" id="tipodocumento_id" placeholder="Tipodocumento Id">
            {!! $errors->first('tipodocumento_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $movimiento?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="movimiento_id" class="form-label">{{ __('Movimiento Id') }}</label>
            <input type="text" name="movimiento_id" class="form-control @error('movimiento_id') is-invalid @enderror" value="{{ old('movimiento_id', $movimiento?->movimiento_id) }}" id="movimiento_id" placeholder="Movimiento Id">
            {!! $errors->first('movimiento_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>