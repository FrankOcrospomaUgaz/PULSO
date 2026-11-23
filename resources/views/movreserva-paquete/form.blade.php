<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="observacion" class="form-label">{{ __('Observacion') }}</label>
            <input type="text" name="observacion" class="form-control @error('observacion') is-invalid @enderror" value="{{ old('observacion', $movreservaPaquete?->observacion) }}" id="observacion" placeholder="Observacion">
            {!! $errors->first('observacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="nropersonas" class="form-label">{{ __('Nropersonas') }}</label>
            <input type="text" name="nropersonas" class="form-control @error('nropersonas') is-invalid @enderror" value="{{ old('nropersonas', $movreservaPaquete?->nropersonas) }}" id="nropersonas" placeholder="Nropersonas">
            {!! $errors->first('nropersonas', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechavencimiento" class="form-label">{{ __('Fechavencimiento') }}</label>
            <input type="text" name="fechavencimiento" class="form-control @error('fechavencimiento') is-invalid @enderror" value="{{ old('fechavencimiento', $movreservaPaquete?->fechavencimiento) }}" id="fechavencimiento" placeholder="Fechavencimiento">
            {!! $errors->first('fechavencimiento', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechainicio" class="form-label">{{ __('Fechainicio') }}</label>
            <input type="text" name="fechainicio" class="form-control @error('fechainicio') is-invalid @enderror" value="{{ old('fechainicio', $movreservaPaquete?->fechainicio) }}" id="fechainicio" placeholder="Fechainicio">
            {!! $errors->first('fechainicio', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechasalida" class="form-label">{{ __('Fechasalida') }}</label>
            <input type="text" name="fechasalida" class="form-control @error('fechasalida') is-invalid @enderror" value="{{ old('fechasalida', $movreservaPaquete?->fechasalida) }}" id="fechasalida" placeholder="Fechasalida">
            {!! $errors->first('fechasalida', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechacancelacion" class="form-label">{{ __('Fechacancelacion') }}</label>
            <input type="text" name="fechacancelacion" class="form-control @error('fechacancelacion') is-invalid @enderror" value="{{ old('fechacancelacion', $movreservaPaquete?->fechacancelacion) }}" id="fechacancelacion" placeholder="Fechacancelacion">
            {!! $errors->first('fechacancelacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="precio_paquete" class="form-label">{{ __('Precio Paquete') }}</label>
            <input type="text" name="precio_paquete" class="form-control @error('precio_paquete') is-invalid @enderror" value="{{ old('precio_paquete', $movreservaPaquete?->precio_paquete) }}" id="precio_paquete" placeholder="Precio Paquete">
            {!! $errors->first('precio_paquete', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="total" class="form-label">{{ __('Total') }}</label>
            <input type="text" name="total" class="form-control @error('total') is-invalid @enderror" value="{{ old('total', $movreservaPaquete?->total) }}" id="total" placeholder="Total">
            {!! $errors->first('total', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="salon_package_id" class="form-label">{{ __('Salon Package Id') }}</label>
            <input type="text" name="salon_package_id" class="form-control @error('salon_package_id') is-invalid @enderror" value="{{ old('salon_package_id', $movreservaPaquete?->salon_package_id) }}" id="salon_package_id" placeholder="Salon Package Id">
            {!! $errors->first('salon_package_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="salon_package_name" class="form-label">{{ __('Salon Package Name') }}</label>
            <input type="text" name="salon_package_name" class="form-control @error('salon_package_name') is-invalid @enderror" value="{{ old('salon_package_name', $movreservaPaquete?->salon_package_name) }}" id="salon_package_name" placeholder="Salon Package Name">
            {!! $errors->first('salon_package_name', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_paquetes" class="form-label">{{ __('Json Paquetes') }}</label>
            <input type="text" name="json_paquetes" class="form-control @error('json_paquetes') is-invalid @enderror" value="{{ old('json_paquetes', $movreservaPaquete?->json_paquetes) }}" id="json_paquetes" placeholder="Json Paquetes">
            {!! $errors->first('json_paquetes', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="estado" class="form-label">{{ __('Estado') }}</label>
            <input type="text" name="estado" class="form-control @error('estado') is-invalid @enderror" value="{{ old('estado', $movreservaPaquete?->estado) }}" id="estado" placeholder="Estado">
            {!! $errors->first('estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="movimiento_id" class="form-label">{{ __('Movimiento Id') }}</label>
            <input type="text" name="movimiento_id" class="form-control @error('movimiento_id') is-invalid @enderror" value="{{ old('movimiento_id', $movreservaPaquete?->movimiento_id) }}" id="movimiento_id" placeholder="Movimiento Id">
            {!! $errors->first('movimiento_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $movreservaPaquete?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="celular" class="form-label">{{ __('Celular') }}</label>
            <input type="text" name="celular" class="form-control @error('celular') is-invalid @enderror" value="{{ old('celular', $movreservaPaquete?->celular) }}" id="celular" placeholder="Celular">
            {!! $errors->first('celular', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $movreservaPaquete?->email) }}" id="email" placeholder="Email">
            {!! $errors->first('email', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>