@extends('layouts.app')

@section('template_title')
    {{ $facturador->name ?? __('Show') . " " . __('Facturador') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Facturador</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('facturadors.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Empresa Facturador Id:</strong>
                            {{ $facturador->empresa_facturador_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Porcentaje Igv:</strong>
                            {{ $facturador->porcentaje_igv }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Username Wsdl:</strong>
                            {{ $facturador->username_wsdl }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Password Wsdl:</strong>
                            {{ $facturador->password_wsdl }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Modo Autenticacion:</strong>
                            {{ $facturador->modo_autenticacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Username Sunat:</strong>
                            {{ $facturador->username_sunat }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Password Sunat:</strong>
                            {{ $facturador->password_sunat }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $facturador->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $facturador->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
