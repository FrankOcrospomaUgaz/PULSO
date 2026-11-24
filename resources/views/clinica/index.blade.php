@extends('layouts.app')

@section('template_title')
    {{ $title }}
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top:0">
            <div class="col s12">
                <div class="card main-content">
                    <div class="card-content">
                        <div class="row" id="filtros" style="display:flex;align-items:center;flex-wrap:wrap;margin-bottom:4px">
                            <div class="col s12 m7">
                                <form method="GET" action="{{ route($type.'.index') }}" style="display:flex;align-items:flex-start;gap:8px">
                                    <div class="input-field" style="flex:1;margin-top:0">
                                        <input type="text" id="search-input" name="q" value="{{ $search }}" placeholder="Buscar...">
                                        <label for="search-input" class="{{ $search ? 'active' : '' }}">Buscar por nombre, documento o contacto</label>
                                    </div>
                                    <button class="btn waves-effect waves-light btn-buscar" type="submit" aria-label="Buscar">
                                        <i class="fa-solid fa-magnifying-glass white-text"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="col s12 m5 right-align" style="margin-top:0">
                                <a href="{{ route($type.'.create') }}" class="btn waves-effect waves-light btn-crear" style="border-radius:10px">
                                    <i class="fa-solid fa-plus left"></i>Nuevo registro
                                </a>
                            </div>
                        </div>

                        <div class="row valign-wrapper" style="margin:0 0 10px">
                            <div class="col s12 left-align valign-wrapper" style="padding-left:13px">
                                <span style="margin-right:8px">Registros por página:</span>
                                <form method="GET" action="{{ route($type.'.index') }}" style="display:flex;align-items:center;gap:8px">
                                    <input type="hidden" name="q" value="{{ $search }}">
                                    <select name="perPage" class="browser-default" onchange="this.form.submit()">
                                        @foreach([10,25,50,100] as $pageSize)
                                            <option value="{{ $pageSize }}" @selected($rows->perPage()==$pageSize)>{{ $pageSize }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>

                        @if(session('success'))<div class="card-panel green lighten-5 green-text text-darken-3">{{ session('success') }}</div>@endif
                        @if(session('error'))<div class="card-panel red lighten-5 red-text text-darken-3">{{ session('error') }}</div>@endif

                        <div class="table-wrapper">
                            <div class="table-scroll" style="overflow-x:auto">
                                <table class="highlight striped centered" style="width:max-content;min-width:100%">
                                    <thead class="thead-custom"><tr>
                                        <th class="no-hide">No.</th>
                                        @if($type==='servicios-clinicos')<th>Servicio</th><th>Duración</th><th>Precio</th><th>Estado</th>
                                        @elseif($type==='profesionales')<th>Profesional</th><th>Especialidad</th><th>Contacto</th><th>Estado</th>
                                        @else<th>Paciente</th><th>Documento</th><th>Fecha nac.</th><th>Contacto</th><th>Padre, madre o tutor</th>@endif
                                        <th class="no-hide">Operaciones</th>
                                    </tr></thead>
                                    <tbody>
                                        @forelse($rows as $row)
                                            <tr>
                                                <td>{{ $rows->firstItem()+$loop->index }}</td>
                                                @if($type==='servicios-clinicos')
                                                    <td>{{ $row->nombre }}</td><td>{{ $row->duracion_minutos }} min</td><td>S/ {{ number_format($row->precio,2) }}</td>
                                                    <td><span class="new badge {{ $row->activo?'green':'red' }}" data-badge-caption="{{ $row->activo?'Activo':'Inactivo' }}"></span></td>
                                                @elseif($type==='profesionales')
                                                    <td>{{ $row->nombres }} {{ $row->apellidos }}</td><td>{{ $row->especialidad }}</td><td>{{ $row->telefono ?: '—' }}<br>{{ $row->email ?: '' }}</td>
                                                    <td><span class="new badge {{ $row->activo?'green':'red' }}" data-badge-caption="{{ $row->activo?'Activo':'Inactivo' }}"></span></td>
                                                @else
                                                    <td>{{ $row->nombres }} {{ $row->apellidos }}</td><td>{{ $row->tipo_documento }} {{ $row->numero_documento ?: '—' }}</td>
                                                    <td>{{ $row->fecha_nacimiento?->format('d/m/Y') ?: '—' }}</td><td>{{ $row->telefono ?: '—' }}<br>{{ $row->email ?: '' }}</td>
                                                    <td>{{ $row->tutor_nombres ?: '—' }}<br>{{ $row->tutor_telefono ?: '' }}</td>
                                                @endif
                                                <td style="white-space:nowrap">
                                                    <a href="{{ route($type.'.edit',$row->id) }}" class="btn-floating yellow darken-2 waves-effect waves-light btn-crear tooltipped" data-tooltip="Editar" aria-label="Editar">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route($type.'.destroy',$row->id) }}" class="delete-form" style="display:inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn-floating red waves-effect waves-light tooltipped" data-tooltip="Eliminar" aria-label="Eliminar">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="center grey-text">No hay registros para mostrar.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row" style="margin-bottom:0">
                            <div class="col s12 m6"><p>Mostrando {{ $rows->firstItem() ?? 0 }} a {{ $rows->lastItem() ?? 0 }} de {{ $rows->total() }} registros</p></div>
                            <div class="col s12 m6 right-align">{{ $rows->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
