@extends('layouts.app')

@section('template_title')
    Det Movreservaproducto
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Det Movreservaproducto') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('det-movreservaproductos.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Movreserva Id</th>
										<th>Producto Id</th>
										<th>Json Producto</th>
										<th>Cantidad</th>
										<th>Monto</th>
										<th>Comentario</th>
										<th>Situacion</th>
										<th>Sucursal Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($detMovreservaproductos as $detMovreservaproducto)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $detMovreservaproducto->movreserva_id }}</td>
											<td>{{ $detMovreservaproducto->producto_id }}</td>
											<td>{{ $detMovreservaproducto->json_producto }}</td>
											<td>{{ $detMovreservaproducto->cantidad }}</td>
											<td>{{ $detMovreservaproducto->monto }}</td>
											<td>{{ $detMovreservaproducto->comentario }}</td>
											<td>{{ $detMovreservaproducto->situacion }}</td>
											<td>{{ $detMovreservaproducto->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('det-movreservaproductos.destroy',$detMovreservaproducto->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('det-movreservaproductos.show',$detMovreservaproducto->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('det-movreservaproductos.edit',$detMovreservaproducto->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $detMovreservaproductos->links() !!}
            </div>
        </div>
    </div>
@endsection
