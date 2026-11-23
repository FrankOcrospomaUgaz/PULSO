@extends('layouts.app')

@section('template_title')
    {{ $listaunidad->name ?? __('Show') . " " . __('Listaunidad') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Listaunidad</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('listaunidads.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Formula:</strong>
                            {{ $listaunidad->formula }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Producto Id:</strong>
                            {{ $listaunidad->producto_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Unidad Id:</strong>
                            {{ $listaunidad->unidad_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Unidadbase Id:</strong>
                            {{ $listaunidad->unidadbase_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Empresa Id:</strong>
                            {{ $listaunidad->empresa_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
