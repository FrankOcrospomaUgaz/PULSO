@extends('layouts.app')

@section('template_title')
    Movhabitacion Habitacion
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Movhabitacion Habitacion') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('movhabitacion-habitacions.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Movhabitacion Id</th>
										<th>Habitacion Id</th>
										<th>Principal</th>
										<th>Tarifa</th>
										<th>Json Habitacion</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movhabitacionHabitacions as $movhabitacionHabitacion)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $movhabitacionHabitacion->movhabitacion_id }}</td>
											<td>{{ $movhabitacionHabitacion->habitacion_id }}</td>
											<td>{{ $movhabitacionHabitacion->principal }}</td>
											<td>{{ $movhabitacionHabitacion->tarifa }}</td>
											<td>{{ $movhabitacionHabitacion->json_habitacion }}</td>

                                            <td>
                                                <form action="{{ route('movhabitacion-habitacions.destroy',$movhabitacionHabitacion->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('movhabitacion-habitacions.show',$movhabitacionHabitacion->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('movhabitacion-habitacions.edit',$movhabitacionHabitacion->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $movhabitacionHabitacions->links() !!}
            </div>
        </div>
    </div>
@endsection
