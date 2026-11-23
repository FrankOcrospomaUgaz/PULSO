@extends('layouts.app')

@section('template_title')
    Dinero
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Dinero') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('dineros.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Valor</th>
										<th>Tipo</th>
										<th>Descripcion</th>
										<th>Orden</th>
										<th>Situacion</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dineros as $dinero)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $dinero->valor }}</td>
											<td>{{ $dinero->tipo }}</td>
											<td>{{ $dinero->descripcion }}</td>
											<td>{{ $dinero->orden }}</td>
											<td>{{ $dinero->situacion }}</td>

                                            <td>
                                                <form action="{{ route('dineros.destroy',$dinero->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('dineros.show',$dinero->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('dineros.edit',$dinero->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $dineros->links() !!}
            </div>
        </div>
    </div>
@endsection
