@extends('layouts.app')

@section('template_title')
    Det Movalmacen
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Det Movalmacen') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('det-movalmacens.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Movalmacen Id</th>
										<th>Producto Id</th>
										<th>Json Producto</th>
										<th>Unidad Id</th>
										<th>Json Listaunidad</th>
										<th>Cantidad</th>
										<th>Comentario</th>
										<th>Situacion</th>
										<th>Sucursal Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($detMovalmacens as $detMovalmacen)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $detMovalmacen->movalmacen_id }}</td>
											<td>{{ $detMovalmacen->producto_id }}</td>
											<td>{{ $detMovalmacen->json_producto }}</td>
											<td>{{ $detMovalmacen->unidad_id }}</td>
											<td>{{ $detMovalmacen->json_listaunidad }}</td>
											<td>{{ $detMovalmacen->cantidad }}</td>
											<td>{{ $detMovalmacen->comentario }}</td>
											<td>{{ $detMovalmacen->situacion }}</td>
											<td>{{ $detMovalmacen->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('det-movalmacens.destroy',$detMovalmacen->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('det-movalmacens.show',$detMovalmacen->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('det-movalmacens.edit',$detMovalmacen->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $detMovalmacens->links() !!}
            </div>
        </div>
    </div>
@endsection
