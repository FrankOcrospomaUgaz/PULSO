<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="movcaja_id" class="form-label">{{ __('Movcaja Id') }}</label>
            <input type="text" name="movcaja_id" class="form-control @error('movcaja_id') is-invalid @enderror" value="{{ old('movcaja_id', $detMovcaja?->movcaja_id) }}" id="movcaja_id" placeholder="Movcaja Id">
            {!! $errors->first('movcaja_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tipo" class="form-label">{{ __('Tipo') }}</label>
            <input type="text" name="tipo" class="form-control @error('tipo') is-invalid @enderror" value="{{ old('tipo', $detMovcaja?->tipo) }}" id="tipo" placeholder="Tipo">
            {!! $errors->first('tipo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechavencimiento" class="form-label">{{ __('Fechavencimiento') }}</label>
            <input type="text" name="fechavencimiento" class="form-control @error('fechavencimiento') is-invalid @enderror" value="{{ old('fechavencimiento', $detMovcaja?->fechavencimiento) }}" id="fechavencimiento" placeholder="Fechavencimiento">
            {!! $errors->first('fechavencimiento', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechapago" class="form-label">{{ __('Fechapago') }}</label>
            <input type="text" name="fechapago" class="form-control @error('fechapago') is-invalid @enderror" value="{{ old('fechapago', $detMovcaja?->fechapago) }}" id="fechapago" placeholder="Fechapago">
            {!! $errors->first('fechapago', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="mediopago_id" class="form-label">{{ __('Mediopago Id') }}</label>
            <input type="text" name="mediopago_id" class="form-control @error('mediopago_id') is-invalid @enderror" value="{{ old('mediopago_id', $detMovcaja?->mediopago_id) }}" id="mediopago_id" placeholder="Mediopago Id">
            {!! $errors->first('mediopago_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="mediopago" class="form-label">{{ __('Mediopago') }}</label>
            <input type="text" name="mediopago" class="form-control @error('mediopago') is-invalid @enderror" value="{{ old('mediopago', $detMovcaja?->mediopago) }}" id="mediopago" placeholder="Mediopago">
            {!! $errors->first('mediopago', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="numero" class="form-label">{{ __('Numero') }}</label>
            <input type="text" name="numero" class="form-control @error('numero') is-invalid @enderror" value="{{ old('numero', $detMovcaja?->numero) }}" id="numero" placeholder="Numero">
            {!! $errors->first('numero', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tarjeta_id" class="form-label">{{ __('Tarjeta Id') }}</label>
            <input type="text" name="tarjeta_id" class="form-control @error('tarjeta_id') is-invalid @enderror" value="{{ old('tarjeta_id', $detMovcaja?->tarjeta_id) }}" id="tarjeta_id" placeholder="Tarjeta Id">
            {!! $errors->first('tarjeta_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tarjeta" class="form-label">{{ __('Tarjeta') }}</label>
            <input type="text" name="tarjeta" class="form-control @error('tarjeta') is-invalid @enderror" value="{{ old('tarjeta', $detMovcaja?->tarjeta) }}" id="tarjeta" placeholder="Tarjeta">
            {!! $errors->first('tarjeta', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="banco_id" class="form-label">{{ __('Banco Id') }}</label>
            <input type="text" name="banco_id" class="form-control @error('banco_id') is-invalid @enderror" value="{{ old('banco_id', $detMovcaja?->banco_id) }}" id="banco_id" placeholder="Banco Id">
            {!! $errors->first('banco_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="banco" class="form-label">{{ __('Banco') }}</label>
            <input type="text" name="banco" class="form-control @error('banco') is-invalid @enderror" value="{{ old('banco', $detMovcaja?->banco) }}" id="banco" placeholder="Banco">
            {!! $errors->first('banco', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="billeteradigital_id" class="form-label">{{ __('Billeteradigital Id') }}</label>
            <input type="text" name="billeteradigital_id" class="form-control @error('billeteradigital_id') is-invalid @enderror" value="{{ old('billeteradigital_id', $detMovcaja?->billeteradigital_id) }}" id="billeteradigital_id" placeholder="Billeteradigital Id">
            {!! $errors->first('billeteradigital_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="billeteradigital" class="form-label">{{ __('Billeteradigital') }}</label>
            <input type="text" name="billeteradigital" class="form-control @error('billeteradigital') is-invalid @enderror" value="{{ old('billeteradigital', $detMovcaja?->billeteradigital) }}" id="billeteradigital" placeholder="Billeteradigital">
            {!! $errors->first('billeteradigital', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="pos_id" class="form-label">{{ __('Pos Id') }}</label>
            <input type="text" name="pos_id" class="form-control @error('pos_id') is-invalid @enderror" value="{{ old('pos_id', $detMovcaja?->pos_id) }}" id="pos_id" placeholder="Pos Id">
            {!! $errors->first('pos_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="pos" class="form-label">{{ __('Pos') }}</label>
            <input type="text" name="pos" class="form-control @error('pos') is-invalid @enderror" value="{{ old('pos', $detMovcaja?->pos) }}" id="pos" placeholder="Pos">
            {!! $errors->first('pos', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="monto" class="form-label">{{ __('Monto') }}</label>
            <input type="text" name="monto" class="form-control @error('monto') is-invalid @enderror" value="{{ old('monto', $detMovcaja?->monto) }}" id="monto" placeholder="Monto">
            {!! $errors->first('monto', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="comentario" class="form-label">{{ __('Comentario') }}</label>
            <input type="text" name="comentario" class="form-control @error('comentario') is-invalid @enderror" value="{{ old('comentario', $detMovcaja?->comentario) }}" id="comentario" placeholder="Comentario">
            {!! $errors->first('comentario', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="situacion" class="form-label">{{ __('Situacion') }}</label>
            <input type="text" name="situacion" class="form-control @error('situacion') is-invalid @enderror" value="{{ old('situacion', $detMovcaja?->situacion) }}" id="situacion" placeholder="Situacion">
            {!! $errors->first('situacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $detMovcaja?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>