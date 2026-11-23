@extends('layouts.app')

@section('template_title')
    Permisousuario
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Permisousuario') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('permisousuarios.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Situacion</th>
										<th>Menuopcion Id</th>
										<th>Perfil Id</th>
										<th>Sucursal Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permisousuarios as $permisousuario)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $permisousuario->situacion }}</td>
											<td>{{ $permisousuario->menuopcion_id }}</td>
											<td>{{ $permisousuario->perfil_id }}</td>
											<td>{{ $permisousuario->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('permisousuarios.destroy',$permisousuario->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('permisousuarios.show',$permisousuario->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('permisousuarios.edit',$permisousuario->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $permisousuarios->links() !!}
            </div>
        </div>
    </div>
@endsection
