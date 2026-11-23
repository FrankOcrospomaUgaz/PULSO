@extends('layouts.app')

@section('template_title')
    Rel Productosucursal
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Rel Productosucursal') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('rel-productosucursals.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
										<th>Fechavencimiento</th>
										<th>Stockminimo</th>
										<th>Stockmaximo</th>
										<th>Stockoptimo</th>
										<th>Minimovender</th>
										<th>Minimocomprar</th>
										<th>Ubicacion Id</th>
										<th>Fila</th>
										<th>Columna</th>
										<th>Favorito</th>
										<th>Subalmacen Id</th>
										<th>Igv Id</th>
										<th>Venta Unitaria</th>
										<th>Producto Id</th>
										<th>Sucursal Id</th>
										<th>Duracion</th>
										<th>Proveedor Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($relProductosucursals as $relProductosucursal)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $relProductosucursal->situacion }}</td>
											<td>{{ $relProductosucursal->fechavencimiento }}</td>
											<td>{{ $relProductosucursal->stockminimo }}</td>
											<td>{{ $relProductosucursal->stockmaximo }}</td>
											<td>{{ $relProductosucursal->stockoptimo }}</td>
											<td>{{ $relProductosucursal->minimovender }}</td>
											<td>{{ $relProductosucursal->minimocomprar }}</td>
											<td>{{ $relProductosucursal->ubicacion_id }}</td>
											<td>{{ $relProductosucursal->fila }}</td>
											<td>{{ $relProductosucursal->columna }}</td>
											<td>{{ $relProductosucursal->favorito }}</td>
											<td>{{ $relProductosucursal->subalmacen_id }}</td>
											<td>{{ $relProductosucursal->igv_id }}</td>
											<td>{{ $relProductosucursal->venta_unitaria }}</td>
											<td>{{ $relProductosucursal->producto_id }}</td>
											<td>{{ $relProductosucursal->sucursal_id }}</td>
											<td>{{ $relProductosucursal->duracion }}</td>
											<td>{{ $relProductosucursal->proveedor_id }}</td>

                                            <td>
                                                <form action="{{ route('rel-productosucursals.destroy',$relProductosucursal->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('rel-productosucursals.show',$relProductosucursal->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('rel-productosucursals.edit',$relProductosucursal->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $relProductosucursals->links() !!}
            </div>
        </div>
    </div>
@endsection
