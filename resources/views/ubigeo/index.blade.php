@extends('layouts.app')

@section('template_title')
    Ubigeo
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Ubigeo') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('ubigeos.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Descripcion</th>
										<th>Codigo</th>
										<th>Inei</th>
										<th>Tipo</th>
										<th>Ubigeo Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ubigeos as $ubigeo)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $ubigeo->descripcion }}</td>
											<td>{{ $ubigeo->codigo }}</td>
											<td>{{ $ubigeo->inei }}</td>
											<td>{{ $ubigeo->tipo }}</td>
											<td>{{ $ubigeo->ubigeo_id }}</td>

                                            <td>
                                                <form action="{{ route('ubigeos.destroy',$ubigeo->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('ubigeos.show',$ubigeo->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('ubigeos.edit',$ubigeo->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $ubigeos->links() !!}
            </div>
        </div>
    </div>
@endsection
