@extends('layouts.app')

@section('template_title')
    Ubicacion
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Ubicacion') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('ubicacions.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
										<th>Descripcion</th>
										<th>Totalfilas</th>
										<th>Totalcolumnas</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ubicacions as $ubicacion)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $ubicacion->codigo }}</td>
											<td>{{ $ubicacion->descripcion }}</td>
											<td>{{ $ubicacion->totalfilas }}</td>
											<td>{{ $ubicacion->totalcolumnas }}</td>

                                            <td>
                                                <form action="{{ route('ubicacions.destroy',$ubicacion->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('ubicacions.show',$ubicacion->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('ubicacions.edit',$ubicacion->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $ubicacions->links() !!}
            </div>
        </div>
    </div>
@endsection
