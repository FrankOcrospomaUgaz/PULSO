@extends('layouts.app')

@section('template_title')
    Rel Operacionsucursal
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Rel Operacionsucursal') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('rel-operacionsucursals.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Orden</th>
										<th>Situacion</th>
										<th>Operacion Id</th>
										<th>Tabla Id</th>
										<th>Sucursal Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($relOperacionsucursals as $relOperacionsucursal)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $relOperacionsucursal->orden }}</td>
											<td>{{ $relOperacionsucursal->situacion }}</td>
											<td>{{ $relOperacionsucursal->operacion_id }}</td>
											<td>{{ $relOperacionsucursal->tabla_id }}</td>
											<td>{{ $relOperacionsucursal->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('rel-operacionsucursals.destroy',$relOperacionsucursal->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('rel-operacionsucursals.show',$relOperacionsucursal->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('rel-operacionsucursals.edit',$relOperacionsucursal->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $relOperacionsucursals->links() !!}
            </div>
        </div>
    </div>
@endsection
