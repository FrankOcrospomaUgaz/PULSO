@extends('layouts.app')

@section('template_title')
    Rel Listaunidadsucursal
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Rel Listaunidadsucursal') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('rel-listaunidadsucursals.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Preciocompra</th>
										<th>Preciomanoobra</th>
										<th>Precioventa</th>
										<th>Moneda</th>
										<th>Listaunidad Id</th>
										<th>Sucursal Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($relListaunidadsucursals as $relListaunidadsucursal)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $relListaunidadsucursal->preciocompra }}</td>
											<td>{{ $relListaunidadsucursal->preciomanoobra }}</td>
											<td>{{ $relListaunidadsucursal->precioventa }}</td>
											<td>{{ $relListaunidadsucursal->moneda }}</td>
											<td>{{ $relListaunidadsucursal->listaunidad_id }}</td>
											<td>{{ $relListaunidadsucursal->sucursal_id }}</td>

                                            <td>
                                                <form action="{{ route('rel-listaunidadsucursals.destroy',$relListaunidadsucursal->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('rel-listaunidadsucursals.show',$relListaunidadsucursal->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('rel-listaunidadsucursals.edit',$relListaunidadsucursal->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $relListaunidadsucursals->links() !!}
            </div>
        </div>
    </div>
@endsection
