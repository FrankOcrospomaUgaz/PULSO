@extends('layouts.app')

@section('template_title')
    Detalle Habitacion
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Detalle Habitacion') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('detalle-habitacions.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Habitacion Id</th>
										<th>Descripcion</th>
										<th>Icono</th>
										<th>Situacion</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($detalleHabitacions as $detalleHabitacion)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $detalleHabitacion->habitacion_id }}</td>
											<td>{{ $detalleHabitacion->descripcion }}</td>
											<td>{{ $detalleHabitacion->icono }}</td>
											<td>{{ $detalleHabitacion->situacion }}</td>

                                            <td>
                                                <form action="{{ route('detalle-habitacions.destroy',$detalleHabitacion->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('detalle-habitacions.show',$detalleHabitacion->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('detalle-habitacions.edit',$detalleHabitacion->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $detalleHabitacions->links() !!}
            </div>
        </div>
    </div>
@endsection
