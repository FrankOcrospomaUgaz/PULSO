@extends('layouts.app')

@section('template_title')
    Rel Movcajadinero
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Rel Movcajadinero') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('rel-movcajadineros.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Cantidad</th>
										<th>Comentario</th>
										<th>Dinero Id</th>
										<th>Movcaja Id</th>
										<th>Sucursal Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($relMovcajadineros as $relMovcajadinero)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $relMovcajadinero->cantidad }}</td>
											<td>{{ $relMovcajadinero->comentario }}</td>
											<td>{{ $relMovcajadinero->dinero_id }}</td>
											<td>{{ $relMovcajadinero->movcaja_id }}</td>
											<td>{{ $relMovcajadinero->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('rel-movcajadineros.destroy',$relMovcajadinero->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('rel-movcajadineros.show',$relMovcajadinero->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('rel-movcajadineros.edit',$relMovcajadinero->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $relMovcajadineros->links() !!}
            </div>
        </div>
    </div>
@endsection
