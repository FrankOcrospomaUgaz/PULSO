@extends('layouts.app')

@section('template_title')
    Historial Habitacion
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Historial Habitacion') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('historial-habitacions.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Situacion Pago</th>
										<th>Situacion Checkout</th>
										<th>Movhabitacion Id</th>
										<th>Relturnocaja Id</th>
										<th>Sucursal Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historialHabitacions as $historialHabitacion)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $historialHabitacion->situacion_pago }}</td>
											<td>{{ $historialHabitacion->situacion_checkout }}</td>
											<td>{{ $historialHabitacion->movhabitacion_id }}</td>
											<td>{{ $historialHabitacion->relturnocaja_id }}</td>
											<td>{{ $historialHabitacion->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('historial-habitacions.destroy',$historialHabitacion->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('historial-habitacions.show',$historialHabitacion->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('historial-habitacions.edit',$historialHabitacion->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $historialHabitacions->links() !!}
            </div>
        </div>
    </div>
@endsection
