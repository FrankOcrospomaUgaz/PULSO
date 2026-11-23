@extends('layouts.app')

@section('template_title')
    Det Movventum
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Det Movventum') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('det-movventa.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Tipo Detalle</th>
										<th>Movventa Id</th>
										<th>Codigo</th>
										<th>Descripcion</th>
										<th>Producto Id</th>
										<th>Json Producto</th>
										<th>Json Medidaproducto</th>
										<th>Unidad Id</th>
										<th>Json Unidad</th>
										<th>Igv Id</th>
										<th>Json Igv</th>
										<th>Cantidad</th>
										<th>Monto</th>
										<th>Dcto Porcentaje</th>
										<th>Monto Original</th>
										<th>Comentario</th>
										<th>Det Categoria</th>
										<th>Opc Categoria</th>
										<th>Det Movventa Id</th>
										<th>Complementos</th>
										<th>Situacion</th>
										<th>Sucursal Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($detMovventa as $detMovventum)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $detMovventum->tipo_detalle }}</td>
											<td>{{ $detMovventum->movventa_id }}</td>
											<td>{{ $detMovventum->codigo }}</td>
											<td>{{ $detMovventum->descripcion }}</td>
											<td>{{ $detMovventum->producto_id }}</td>
											<td>{{ $detMovventum->json_producto }}</td>
											<td>{{ $detMovventum->json_medidaproducto }}</td>
											<td>{{ $detMovventum->unidad_id }}</td>
											<td>{{ $detMovventum->json_unidad }}</td>
											<td>{{ $detMovventum->igv_id }}</td>
											<td>{{ $detMovventum->json_igv }}</td>
											<td>{{ $detMovventum->cantidad }}</td>
											<td>{{ $detMovventum->monto }}</td>
											<td>{{ $detMovventum->dcto_porcentaje }}</td>
											<td>{{ $detMovventum->monto_original }}</td>
											<td>{{ $detMovventum->comentario }}</td>
											<td>{{ $detMovventum->det_categoria }}</td>
											<td>{{ $detMovventum->opc_categoria }}</td>
											<td>{{ $detMovventum->det_movventa_id }}</td>
											<td>{{ $detMovventum->complementos }}</td>
											<td>{{ $detMovventum->situacion }}</td>
											<td>{{ $detMovventum->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('det-movventa.destroy',$detMovventum->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('det-movventa.show',$detMovventum->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('det-movventa.edit',$detMovventum->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $detMovventa->links() !!}
            </div>
        </div>
    </div>
@endsection
