@extends('layouts.app')

@section('template_title')
    Respuestum
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Respuestum') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('respuesta.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
                                  {{ __('Create New') }}
                                </a>
                              </div>
                        </div>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body bg-white">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>No</th>
                                        
										<th>Codigo</th>
										<th>Fecha</th>
										<th>Persona Id</th>
										<th>Persona</th>
										<th>Encuesta Id</th>
										<th>Movimiento Id</th>
										<th>Movimiento</th>
										<th>Sucursal Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($respuesta as $respuestum)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $respuestum->codigo }}</td>
											<td>{{ $respuestum->fecha }}</td>
											<td>{{ $respuestum->persona_id }}</td>
											<td>{{ $respuestum->persona }}</td>
											<td>{{ $respuestum->encuesta_id }}</td>
											<td>{{ $respuestum->movimiento_id }}</td>
											<td>{{ $respuestum->movimiento }}</td>
											<td>{{ $respuestum->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('respuesta.destroy',$respuestum->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('respuesta.show',$respuestum->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('respuesta.edit',$respuestum->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {!! $respuesta->links() !!}
            </div>
        </div>
    </div>
@endsection
