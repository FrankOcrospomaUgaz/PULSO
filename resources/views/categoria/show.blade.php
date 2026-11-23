@extends('layouts.app')

@section('template_title')
    {{ $categorium->name ?? __('Show') . " " . __('Categorium') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Categorium</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('categoria.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $categorium->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Abreviatura:</strong>
                            {{ $categorium->abreviatura }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Nivel:</strong>
                            {{ $categorium->nivel }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Orden:</strong>
                            {{ $categorium->orden }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Padre:</strong>
                            {{ $categorium->padre }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Imagen:</strong>
                            {{ $categorium->imagen }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Categoria Id:</strong>
                            {{ $categorium->categoria_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Empresa Id:</strong>
                            {{ $categorium->empresa_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
