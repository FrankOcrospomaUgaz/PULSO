<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="jornada_id" class="form-label">{{ __('Jornada Id') }}</label>
            <input type="text" name="jornada_id" class="form-control @error('jornada_id') is-invalid @enderror" value="{{ old('jornada_id', $housekeepingDetalle?->jornada_id) }}" id="jornada_id" placeholder="Jornada Id">
            {!! $errors->first('jornada_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="habitacion_id" class="form-label">{{ __('Habitacion Id') }}</label>
            <input type="text" name="habitacion_id" class="form-control @error('habitacion_id') is-invalid @enderror" value="{{ old('habitacion_id', $housekeepingDetalle?->habitacion_id) }}" id="habitacion_id" placeholder="Habitacion Id">
            {!! $errors->first('habitacion_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="inicio_limpieza" class="form-label">{{ __('Inicio Limpieza') }}</label>
            <input type="text" name="inicio_limpieza" class="form-control @error('inicio_limpieza') is-invalid @enderror" value="{{ old('inicio_limpieza', $housekeepingDetalle?->inicio_limpieza) }}" id="inicio_limpieza" placeholder="Inicio Limpieza">
            {!! $errors->first('inicio_limpieza', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fin_limpieza" class="form-label">{{ __('Fin Limpieza') }}</label>
            <input type="text" name="fin_limpieza" class="form-control @error('fin_limpieza') is-invalid @enderror" value="{{ old('fin_limpieza', $housekeepingDetalle?->fin_limpieza) }}" id="fin_limpieza" placeholder="Fin Limpieza">
            {!! $errors->first('fin_limpieza', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tiempo" class="form-label">{{ __('Tiempo') }}</label>
            <input type="text" name="tiempo" class="form-control @error('tiempo') is-invalid @enderror" value="{{ old('tiempo', $housekeepingDetalle?->tiempo) }}" id="tiempo" placeholder="Tiempo">
            {!! $errors->first('tiempo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="estado" class="form-label">{{ __('Estado') }}</label>
            <input type="text" name="estado" class="form-control @error('estado') is-invalid @enderror" value="{{ old('estado', $housekeepingDetalle?->estado) }}" id="estado" placeholder="Estado">
            {!! $errors->first('estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="observaciones" class="form-label">{{ __('Observaciones') }}</label>
            <input type="text" name="observaciones" class="form-control @error('observaciones') is-invalid @enderror" value="{{ old('observaciones', $housekeepingDetalle?->observaciones) }}" id="observaciones" placeholder="Observaciones">
            {!! $errors->first('observaciones', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="empleado_id" class="form-label">{{ __('Empleado Id') }}</label>
            <input type="text" name="empleado_id" class="form-control @error('empleado_id') is-invalid @enderror" value="{{ old('empleado_id', $housekeepingDetalle?->empleado_id) }}" id="empleado_id" placeholder="Empleado Id">
            {!! $errors->first('empleado_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>