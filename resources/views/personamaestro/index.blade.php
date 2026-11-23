@extends('layouts.app')

@section('template_title')
    Personamaestro
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Personamaestro') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('personamaestros.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Tipopersona</th>
										<th>Nombres</th>
										<th>Apellidos</th>
										<th>Nrodoc</th>
										<th>Sexo</th>
										<th>Fechanac</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($personamaestros as $personamaestro)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $personamaestro->tipopersona }}</td>
											<td>{{ $personamaestro->nombres }}</td>
											<td>{{ $personamaestro->apellidos }}</td>
											<td>{{ $personamaestro->nrodoc }}</td>
											<td>{{ $personamaestro->sexo }}</td>
											<td>{{ $personamaestro->fechanac }}</td>

                                            <td>
                                                <form action="{{ route('personamaestros.destroy',$personamaestro->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('personamaestros.show',$personamaestro->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('personamaestros.edit',$personamaestro->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $personamaestros->links() !!}
            </div>
        </div>
    </div>
@endsection
