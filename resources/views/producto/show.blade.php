@extends('layouts.app')

@section('template_title')
    {{ $producto->name ?? __('Show') . " " . __('Producto') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Producto</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('productos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Codigo:</strong>
                            {{ $producto->codigo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $producto->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Abreviatura:</strong>
                            {{ $producto->abreviatura }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo:</strong>
                            {{ $producto->tipo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Categoria Id:</strong>
                            {{ $producto->categoria_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Marca Id:</strong>
                            {{ $producto->marca_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Unidadbase Id:</strong>
                            {{ $producto->unidadbase_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Medidapeso Id:</strong>
                            {{ $producto->medidapeso_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Kardex:</strong>
                            {{ $producto->kardex }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Compuesto:</strong>
                            {{ $producto->compuesto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Autoproduccion:</strong>
                            {{ $producto->autoproduccion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Peso:</strong>
                            {{ $producto->peso }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Comentario:</strong>
                            {{ $producto->comentario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Imagen:</strong>
                            {{ $producto->imagen }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Complemento:</strong>
                            {{ $producto->complemento }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Modocomplemento:</strong>
                            {{ $producto->modocomplemento }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Empresa Id:</strong>
                            {{ $producto->empresa_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Clasificacion:</strong>
                            {{ $producto->clasificacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Nro Parte:</strong>
                            {{ $producto->nro_parte }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Caracteristicas:</strong>
                            {{ $producto->caracteristicas }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Distribuidor Id:</strong>
                            {{ $producto->distribuidor_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Imagen2:</strong>
                            {{ $producto->imagen2 }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Imagen3:</strong>
                            {{ $producto->imagen3 }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
