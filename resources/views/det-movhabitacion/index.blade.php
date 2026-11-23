@extends('layouts.app')

@section('template_title')
    Det Movhabitacion
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Det Movhabitacion') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('det-movhabitacions.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
										<th>Movhabitacion Id</th>
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
                                    @foreach ($detMovhabitacions as $detMovhabitacion)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $detMovhabitacion->tipo_detalle }}</td>
											<td>{{ $detMovhabitacion->movhabitacion_id }}</td>
											<td>{{ $detMovhabitacion->codigo }}</td>
											<td>{{ $detMovhabitacion->descripcion }}</td>
											<td>{{ $detMovhabitacion->producto_id }}</td>
											<td>{{ $detMovhabitacion->json_producto }}</td>
											<td>{{ $detMovhabitacion->json_medidaproducto }}</td>
											<td>{{ $detMovhabitacion->unidad_id }}</td>
											<td>{{ $detMovhabitacion->json_unidad }}</td>
											<td>{{ $detMovhabitacion->igv_id }}</td>
											<td>{{ $detMovhabitacion->json_igv }}</td>
											<td>{{ $detMovhabitacion->cantidad }}</td>
											<td>{{ $detMovhabitacion->monto }}</td>
											<td>{{ $detMovhabitacion->dcto_porcentaje }}</td>
											<td>{{ $detMovhabitacion->monto_original }}</td>
											<td>{{ $detMovhabitacion->comentario }}</td>
											<td>{{ $detMovhabitacion->det_categoria }}</td>
											<td>{{ $detMovhabitacion->opc_categoria }}</td>
											<td>{{ $detMovhabitacion->det_movventa_id }}</td>
											<td>{{ $detMovhabitacion->complementos }}</td>
											<td>{{ $detMovhabitacion->situacion }}</td>
											<td>{{ $detMovhabitacion->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('det-movhabitacions.destroy',$detMovhabitacion->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('det-movhabitacions.show',$detMovhabitacion->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('det-movhabitacions.edit',$detMovhabitacion->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $detMovhabitacions->links() !!}
            </div>
        </div>
    </div>
@endsection
