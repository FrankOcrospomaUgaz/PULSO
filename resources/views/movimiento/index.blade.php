@extends('layouts.app')

@section('template_title')
    Movimiento
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Movimiento') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('movimientos.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Numero</th>
										<th>Fecha</th>
										<th>Usuario Id</th>
										<th>Usuario</th>
										<th>Persona Id</th>
										<th>Persona</th>
										<th>Responsable Id</th>
										<th>Responsable</th>
										<th>Comentario</th>
										<th>Situacion</th>
										<th>Tipomovimiento Id</th>
										<th>Tipodocumento Id</th>
										<th>Sucursal Id</th>
										<th>Movimiento Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movimientos as $movimiento)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $movimiento->numero }}</td>
											<td>{{ $movimiento->fecha }}</td>
											<td>{{ $movimiento->usuario_id }}</td>
											<td>{{ $movimiento->usuario }}</td>
											<td>{{ $movimiento->persona_id }}</td>
											<td>{{ $movimiento->persona }}</td>
											<td>{{ $movimiento->responsable_id }}</td>
											<td>{{ $movimiento->responsable }}</td>
											<td>{{ $movimiento->comentario }}</td>
											<td>{{ $movimiento->situacion }}</td>
											<td>{{ $movimiento->tipomovimiento_id }}</td>
											<td>{{ $movimiento->tipodocumento_id }}</td>
											<td>{{ $movimiento->sucursal_id }}</td>
											<td>{{ $movimiento->movimiento_id }}</td>

                                            <td>
                                                <form action="{{ route('movimientos.destroy',$movimiento->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('movimientos.show',$movimiento->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('movimientos.edit',$movimiento->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $movimientos->links() !!}
            </div>
        </div>
    </div>
@endsection
