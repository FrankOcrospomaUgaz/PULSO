@extends('layouts.app')

@section('template_title')
    Det Movcaja
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Det Movcaja') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('det-movcajas.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Movcaja Id</th>
										<th>Tipo</th>
										<th>Fechavencimiento</th>
										<th>Fechapago</th>
										<th>Mediopago Id</th>
										<th>Mediopago</th>
										<th>Numero</th>
										<th>Tarjeta Id</th>
										<th>Tarjeta</th>
										<th>Banco Id</th>
										<th>Banco</th>
										<th>Billeteradigital Id</th>
										<th>Billeteradigital</th>
										<th>Pos Id</th>
										<th>Pos</th>
										<th>Monto</th>
										<th>Comentario</th>
										<th>Situacion</th>
										<th>Sucursal Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($detMovcajas as $detMovcaja)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $detMovcaja->movcaja_id }}</td>
											<td>{{ $detMovcaja->tipo }}</td>
											<td>{{ $detMovcaja->fechavencimiento }}</td>
											<td>{{ $detMovcaja->fechapago }}</td>
											<td>{{ $detMovcaja->mediopago_id }}</td>
											<td>{{ $detMovcaja->mediopago }}</td>
											<td>{{ $detMovcaja->numero }}</td>
											<td>{{ $detMovcaja->tarjeta_id }}</td>
											<td>{{ $detMovcaja->tarjeta }}</td>
											<td>{{ $detMovcaja->banco_id }}</td>
											<td>{{ $detMovcaja->banco }}</td>
											<td>{{ $detMovcaja->billeteradigital_id }}</td>
											<td>{{ $detMovcaja->billeteradigital }}</td>
											<td>{{ $detMovcaja->pos_id }}</td>
											<td>{{ $detMovcaja->pos }}</td>
											<td>{{ $detMovcaja->monto }}</td>
											<td>{{ $detMovcaja->comentario }}</td>
											<td>{{ $detMovcaja->situacion }}</td>
											<td>{{ $detMovcaja->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('det-movcajas.destroy',$detMovcaja->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('det-movcajas.show',$detMovcaja->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('det-movcajas.edit',$detMovcaja->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $detMovcajas->links() !!}
            </div>
        </div>
    </div>
@endsection
