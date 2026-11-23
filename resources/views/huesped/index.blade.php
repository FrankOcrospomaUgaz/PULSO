@extends('layouts.app')

@section('template_title')
    Persona
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="card-content" style="margin-top: -20px">
                        <div class="row without-bottom filtro" id="filtro-MovVenta">
                            <div class="col s12 m6 l3">
                                <div class="input-field">
                                    <select name="tipopersona" id="tipopersona" onchange="filterHuesped()">
                                    <option value="Todos">Todos</option>
                                    <option value="Natural" >DNI</option>
                                    <option value="Juridico">RUC</option>
                                    <option value="Carnet de Extranjería">Carnet de Extranjería</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                    </select>
                                    <label for="tipopersona">Tipo</label>
                                </div>
                            </div>
                            <div class="col s12 m6 l2">
                                <div class="input-field">
                                    <div class="select-wrapper">
                                        <select id="genero" name="genero" onchange="filterHuesped()"
                                            tabindex="-1">
                                        <option value="0">Todos</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        </select>

                                    </div>
                                    <label for="genero">Género</label>
                                </div>
                            </div>
                            <div class="col s12 m6 l3">
                                <div class="input-field">
                                    <div class="select-wrapper">
                                        <select id="no_grato" name="no_grato" onchange="filterHuesped()">
                                            <option value="Todos" {{ request('no_grato') == 'Todos' || !request('no_grato') ? 'selected' : '' }}>Todos</option>
                                            <option value="0" {{ request('no_grato') === '0' ? 'selected' : '' }}>Persona Grata</option>
                                            <option value="1" {{ request('no_grato') === '1' ? 'selected' : '' }}>Persona No Grata</option>
                                        </select>
                                    </div>
                                    <label for="no_grato">Condición</label>
                                </div>
                            </div>
                            <div class="col s12 m6 l4">
                                  <div class="input-field" style="flex: 1; ">
                <input type="text" id="search-input" class="validate" 
                    data-url-base="{{ route('huesped.index') }}">
                <label for="search-input">Buscar</label>
            </div>
                            </div>
                            


                            <div class="row valign-wrapper" style="margin-bottom: -25px;">
                                <div class="col s12 m8 left-align valign-wrapper">
                                    <label for="records-per-page" style="margin-right: 10px;">Registros por página:</label>
                                    <div class="input-field" style="margin: 0;">
                                        <select id="records-per-page" class="browser-default" onchange="filterHuesped()">
                                            <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10
                                            </option>
                                            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25
                                            </option>
                                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50
                                            </option>
                                            <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100
                                            </option>
                                        </select>
                                    </div>
                                </div>


                                <div class="col s12 m12  l4 search-with-report right-align ">
                                    <div class="input-field">
                                        <button class="btn teal tooltipped btn-buscar" id="btn-Huesped"
                                            data-tooltip="Buscar" type="button" onclick="filterHuesped()"><i
                                                class="fa-solid fa-magnifying-glass white-text"></i></button>
                                        <button class="btn red tooltipped truncated btn-pdf" id="btn-pdf-MovVenta"
                                            data-tooltip="Reporte en PDF" type="button"
                                            onclick="generatePDFReportHuesped()">
                                            <i class="fa-solid fa-file-pdf white-text right"></i>PDF
                                        </button>

                                        <button class="btn green tooltipped truncated btn-excel" id="btn-excel-MovVenta"
                                            data-tooltip="Reporte en Excel" type="button"
                                            onclick="generateExcelReportVentas()"><i
                                                class="fa-solid fa-file white-text right"></i>Excel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($message = Session::get('success'))
                        <div class="card-panel green lighten-4 green-text text-darken-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif
                    <div id="tb-container" class="table-responsive">
                        <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                            <thead class="thead-custom">
                                <tr>
                                    <th>Distrito</th>
                                    <th>Tipo</th>
                                    <th>Nro. documento</th>
                                    <th>Nombres</th>
                                    <th>N° alojamientos</th>
                                    <th>Fecha</th>
                                    <th>Género</th>
                                    <th>Dirección</th>
                                    <th>Email</th>
                                    <th>Teléfono Fijo</th>
                                    <th>Teléfono Móvil</th>
                                    <th>Imagen</th>
                                    <th>Compartido</th>
                                    <th class="no-hide">Condición</th>
                                    <th>Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($personas as $persona)
                                                            <tr>
                                                                <td>{{ $persona->ubigeo->descripcion ?? '' }}</td>
                                                                <td>
                                                                    {{ strtoupper(
                                        $persona->personamaestro->tipopersona === 'Natural'
                                        ? 'DNI'
                                        : ($persona->personamaestro->tipopersona === 'Juridico'
                                            ? 'RUC'
                                            : $persona->personamaestro->tipopersona),
                                    ) }}
                                                                </td>

                                                                <td>{{ $persona->personamaestro->nrodoc }}</td>
                                                                <td>{{ $persona->apellidos }} {{ $persona->nombres }}</td>
                                                                <td>{{ $persona->huespedes->whereNotNull('movhabitacion_id')->count() }}</td>

                                                                <td>{{ $persona->personamaestro->fechanac }}</td>
                                                                <td>{{ $persona->personamaestro->sexo }}</td>
                                                                <td>{{ $persona->direccion }}</td>
                                                                <td>{{ $persona->email }}</td>
                                                                <td>{{ $persona->telefonofijo }}</td>
                                                                <td>{{ $persona->telefonomovil }}</td>
                                                                <td>{{ $persona->imagen }}</td>
                                                                <td>{{ $persona->compartido }}</td>
                                                                <td>
                                                                    @if ($persona->no_grato)
                                                                        <span class="badge red white-text" style="display: inline-flex; align-items: center; justify-content: center; float: none; margin: 0; padding: 4px 10px; border-radius: 6px; font-weight: bold; line-height: 1;">No Grata</span>
                                                                    @else
                                                                        <span class="badge green white-text" style="display: inline-flex; align-items: center; justify-content: center; float: none; margin: 0; padding: 4px 10px; border-radius: 6px; font-weight: bold; line-height: 1;">Grata</span>
                                                                    @endif
                                                                </td>

                                                                <td>
                                                                    @foreach ($operaciones as $operacion)
                                                                        @if ($operacion->tipo == 'Registro')

                                                                            <!-- Si es 'persona.destroy' -->
                                                                            @if ($operacion->accion === 'huesped.alojamientos')
                                                                                <a href="{{ route($operacion->accion, $persona->id) }}"
                                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear"
                                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                                    {!! $operacion->imagen !!}
                                                                                </a>
                                                                            @endif
                                                                        @endif
                                                                    @endforeach


                                                                </td>
                                                            </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $personas->firstItem() }} a {{ $personas->lastItem() }} de
                                    {{ $personas->total() }} registros
                                </p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $personas->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Structure -->
    <div id="modal-opcion" class="modal" style="max-width: 400px;">
        <div class="modal-content center-align">
            <!-- Botón de cierre (X) -->
            <a href="#!" class="modal-close btn-flat" style="position: absolute; right: 10px; top: 10px;">
                <i class="material-icons">close</i>
            </a>

            <!-- Contenido del modal -->
            <div class="swal2-icon swal2-question swal2-icon-show" style="display: flex;">
                <div class="swal2-icon-content">?</div>
            </div>
            <h4>Registrar personal</h4>
            <p>¿De qué manera desea registrar su nuevo empleado?</p>
            <div class="modal-footer center-align">
                <button id="opcion-doc-persona" class="btn btn-large" data-sucursal-id="{{ $sucursal_id }}">Nro. de
                    documento</button>
                <button id="opcion-nombre-persona" class="btn btn-large"
                    data-sucursal-id="{{ $sucursal_id }}">Nombre</button>

            </div>
        </div>
    </div>
@endsection