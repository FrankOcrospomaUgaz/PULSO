@extends('layouts.app')

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="card-content" style="margin-top: -20px;">
                        <div class="row valign-wrapper" style="margin-bottom: 10px;">
                            <!-- Buscador -->
                            <div class="col s12 m6">
                                <div class="input-field">
                                    <i class="material-icons prefix">search</i>
                                    <input type="text" id="search-input" class="validate" placeholder="Buscar..." 
                                           data-url-base="{{ route('respuesta.detalle', $id) }}">
                                </div>
                            </div>

                 
                        </div>

                        <!-- Tabla de Valordet Respuestas -->
                        <div id="tb-container" class="table-responsive">
                            <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                                <thead class="thead-custom">
                                    <tr>
                                        <th>#</th>
                                        <th>Enunciado</th>
                                        <th>Valor</th>
                                     
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($valordetRespuesta as $valordetRespuestum)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $valordetRespuestum->detRespuestum->detEncuestum->enunciado }}</td>
                                            <td>{{ $valordetRespuestum->valor }}</td>
                                            
                                            
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Paginación -->
                            <div class="row">
                                <div class="col s12 m6">
                                    <p>Mostrando {{ $valordetRespuesta->firstItem() }} a {{ $valordetRespuesta->lastItem() }} de {{ $valordetRespuesta->total() }} registros</p>
                                </div>
                                <div class="col s12 m6 right-align">
                                    {{ $valordetRespuesta->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
