<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="tipo_solicitud" class="form-label">{{ __('Tipo Solicitud') }}</label>
            <input type="text" name="tipo_solicitud" class="form-control @error('tipo_solicitud') is-invalid @enderror" value="{{ old('tipo_solicitud', $solicitud?->tipo_solicitud) }}" id="tipo_solicitud" placeholder="Tipo Solicitud">
            {!! $errors->first('tipo_solicitud', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="data" class="form-label">{{ __('Data') }}</label>
            <input type="text" name="data" class="form-control @error('data') is-invalid @enderror" value="{{ old('data', $solicitud?->data) }}" id="data" placeholder="Data">
            {!! $errors->first('data', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha" class="form-label">{{ __('Fecha') }}</label>
            <input type="text" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', $solicitud?->fecha) }}" id="fecha" placeholder="Fecha">
            {!! $errors->first('fecha', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechaenvio" class="form-label">{{ __('Fechaenvio') }}</label>
            <input type="text" name="fechaenvio" class="form-control @error('fechaenvio') is-invalid @enderror" value="{{ old('fechaenvio', $solicitud?->fechaenvio) }}" id="fechaenvio" placeholder="Fechaenvio">
            {!! $errors->first('fechaenvio', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecharespuesta" class="form-label">{{ __('Fecharespuesta') }}</label>
            <input type="text" name="fecharespuesta" class="form-control @error('fecharespuesta') is-invalid @enderror" value="{{ old('fecharespuesta', $solicitud?->fecharespuesta) }}" id="fecharespuesta" placeholder="Fecharespuesta">
            {!! $errors->first('fecharespuesta', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fechaanulacion" class="form-label">{{ __('Fechaanulacion') }}</label>
            <input type="text" name="fechaanulacion" class="form-control @error('fechaanulacion') is-invalid @enderror" value="{{ old('fechaanulacion', $solicitud?->fechaanulacion) }}" id="fechaanulacion" placeholder="Fechaanulacion">
            {!! $errors->first('fechaanulacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="estado" class="form-label">{{ __('Estado') }}</label>
            <input type="text" name="estado" class="form-control @error('estado') is-invalid @enderror" value="{{ old('estado', $solicitud?->estado) }}" id="estado" placeholder="Estado">
            {!! $errors->first('estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="nombre" class="form-label">{{ __('Nombre') }}</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $solicitud?->nombre) }}" id="nombre" placeholder="Nombre">
            {!! $errors->first('nombre', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="total" class="form-label">{{ __('Total') }}</label>
            <input type="text" name="total" class="form-control @error('total') is-invalid @enderror" value="{{ old('total', $solicitud?->total) }}" id="total" placeholder="Total">
            {!! $errors->first('total', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="moneda" class="form-label">{{ __('Moneda') }}</label>
            <input type="text" name="moneda" class="form-control @error('moneda') is-invalid @enderror" value="{{ old('moneda', $solicitud?->moneda) }}" id="moneda" placeholder="Moneda">
            {!! $errors->first('moneda', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tipo_documento" class="form-label">{{ __('Tipo Documento') }}</label>
            <input type="text" name="tipo_documento" class="form-control @error('tipo_documento') is-invalid @enderror" value="{{ old('tipo_documento', $solicitud?->tipo_documento) }}" id="tipo_documento" placeholder="Tipo Documento">
            {!! $errors->first('tipo_documento', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="serie" class="form-label">{{ __('Serie') }}</label>
            <input type="text" name="serie" class="form-control @error('serie') is-invalid @enderror" value="{{ old('serie', $solicitud?->serie) }}" id="serie" placeholder="Serie">
            {!! $errors->first('serie', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="correlativo" class="form-label">{{ __('Correlativo') }}</label>
            <input type="text" name="correlativo" class="form-control @error('correlativo') is-invalid @enderror" value="{{ old('correlativo', $solicitud?->correlativo) }}" id="correlativo" placeholder="Correlativo">
            {!! $errors->first('correlativo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="ticket_solicitud" class="form-label">{{ __('Ticket Solicitud') }}</label>
            <input type="text" name="ticket_solicitud" class="form-control @error('ticket_solicitud') is-invalid @enderror" value="{{ old('ticket_solicitud', $solicitud?->ticket_solicitud) }}" id="ticket_solicitud" placeholder="Ticket Solicitud">
            {!! $errors->first('ticket_solicitud', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="persona_id" class="form-label">{{ __('Persona Id') }}</label>
            <input type="text" name="persona_id" class="form-control @error('persona_id') is-invalid @enderror" value="{{ old('persona_id', $solicitud?->persona_id) }}" id="persona_id" placeholder="Persona Id">
            {!! $errors->first('persona_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_persona" class="form-label">{{ __('Json Persona') }}</label>
            <input type="text" name="json_persona" class="form-control @error('json_persona') is-invalid @enderror" value="{{ old('json_persona', $solicitud?->json_persona) }}" id="json_persona" placeholder="Json Persona">
            {!! $errors->first('json_persona', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="sucursal_id" class="form-label">{{ __('Sucursal Id') }}</label>
            <input type="text" name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" value="{{ old('sucursal_id', $solicitud?->sucursal_id) }}" id="sucursal_id" placeholder="Sucursal Id">
            {!! $errors->first('sucursal_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_sucursal" class="form-label">{{ __('Json Sucursal') }}</label>
            <input type="text" name="json_sucursal" class="form-control @error('json_sucursal') is-invalid @enderror" value="{{ old('json_sucursal', $solicitud?->json_sucursal) }}" id="json_sucursal" placeholder="Json Sucursal">
            {!! $errors->first('json_sucursal', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="facturador_id" class="form-label">{{ __('Facturador Id') }}</label>
            <input type="text" name="facturador_id" class="form-control @error('facturador_id') is-invalid @enderror" value="{{ old('facturador_id', $solicitud?->facturador_id) }}" id="facturador_id" placeholder="Facturador Id">
            {!! $errors->first('facturador_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="json_facturador" class="form-label">{{ __('Json Facturador') }}</label>
            <input type="text" name="json_facturador" class="form-control @error('json_facturador') is-invalid @enderror" value="{{ old('json_facturador', $solicitud?->json_facturador) }}" id="json_facturador" placeholder="Json Facturador">
            {!! $errors->first('json_facturador', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="movimiento_id" class="form-label">{{ __('Movimiento Id') }}</label>
            <input type="text" name="movimiento_id" class="form-control @error('movimiento_id') is-invalid @enderror" value="{{ old('movimiento_id', $solicitud?->movimiento_id) }}" id="movimiento_id" placeholder="Movimiento Id">
            {!! $errors->first('movimiento_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="solicitud_facturador_id" class="form-label">{{ __('Solicitud Facturador Id') }}</label>
            <input type="text" name="solicitud_facturador_id" class="form-control @error('solicitud_facturador_id') is-invalid @enderror" value="{{ old('solicitud_facturador_id', $solicitud?->solicitud_facturador_id) }}" id="solicitud_facturador_id" placeholder="Solicitud Facturador Id">
            {!! $errors->first('solicitud_facturador_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="error_code" class="form-label">{{ __('Error Code') }}</label>
            <input type="text" name="error_code" class="form-control @error('error_code') is-invalid @enderror" value="{{ old('error_code', $solicitud?->error_code) }}" id="error_code" placeholder="Error Code">
            {!! $errors->first('error_code', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="error_text" class="form-label">{{ __('Error Text') }}</label>
            <input type="text" name="error_text" class="form-control @error('error_text') is-invalid @enderror" value="{{ old('error_text', $solicitud?->error_text) }}" id="error_text" placeholder="Error Text">
            {!! $errors->first('error_text', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="public_name" class="form-label">{{ __('Public Name') }}</label>
            <input type="text" name="public_name" class="form-control @error('public_name') is-invalid @enderror" value="{{ old('public_name', $solicitud?->public_name) }}" id="public_name" placeholder="Public Name">
            {!! $errors->first('public_name', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>