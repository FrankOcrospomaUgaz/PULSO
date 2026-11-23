@extends('layouts.app')

@section('template_title')
    Rel Camposucursal
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Rel Camposucursal') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('rel-camposucursals.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
										<th>Tipo</th>
										<th>Orden</th>
										<th>Diccionario</th>
										<th>Situacion</th>
										<th>Campo Id</th>
										<th>Tabla Id</th>
										<th>Sucursal Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($relCamposucursals as $relCamposucursal)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $relCamposucursal->descripcion }}</td>
											<td>{{ $relCamposucursal->tipo }}</td>
											<td>{{ $relCamposucursal->orden }}</td>
											<td>{{ $relCamposucursal->diccionario }}</td>
											<td>{{ $relCamposucursal->situacion }}</td>
											<td>{{ $relCamposucursal->campo_id }}</td>
											<td>{{ $relCamposucursal->tabla_id }}</td>
											<td>{{ $relCamposucursal->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('rel-camposucursals.destroy',$relCamposucursal->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('rel-camposucursals.show',$relCamposucursal->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('rel-camposucursals.edit',$relCamposucursal->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $relCamposucursals->links() !!}
            </div>
        </div>
    </div>
@endsection
