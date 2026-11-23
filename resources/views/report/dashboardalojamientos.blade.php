@extends('layouts.app')

@section('content')
    <style>
        .card-supertitle {
            font-size: 3rem !important;
            font-weight: 700 !important;
        }

        .bold {
            font-weight: 900 !important;
        }
    </style>
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="row without-bottom">
                        <div class="col s12 m6 l3">
                            <div class="card minicard red accent-3">
                                <div class="card-content white-text">
                                    <span class="card-supertitle">
                                        <font id="habitaciones-ocupadas">{{ $ocupadas }}</font>
                                        <i class="fa-solid fa-bed right"></i>
                                    </span>
                                    <p>Habitaciones Ocupadas</p>
                                </div>
                            </div>
                        </div>

                        <div class="col s12 m6 l3">
                            <div class="card minicard blue accent-2">
                                <div class="card-content white-text">
                                    <span class="card-supertitle">
                                        <font id="reservas-hoy">{{ $reservasHoy }}</font>
                                        <i class="fa-solid fa-calendar-day right"></i>
                                    </span>
                                    <p>Reservas para Hoy</p>
                                </div>
                            </div>
                        </div>

                        <div class="col s12 m6 l3">
                            <div class="card minicard green accent-2">
                                <div class="card-content grey-text text-darken-3">
                                    <span class="card-supertitle">
                                        <font id="habitaciones-limpieza">{{ $enLimpieza }}</font>
                                        <i class="fa-solid fa-broom right"></i>
                                    </span>
                                    <p>Habitaciones en Limpieza</p>
                                </div>
                            </div>
                        </div>

                        <div class="col s12 m6 l3">
                            <div class="card minicard orange lighten-1">
                                <div class="card-content grey-text text-darken-3">
                                    <span class="card-supertitle">
                                        <font id="habitaciones-mantenimiento">{{ $enMantenimiento }}</font>
                                        <i class="fa-solid fa-tools right"></i>
                                    </span>
                                    <p>Habitaciones en Mantenimiento</p>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros de búsqueda -->
                        <div class="col s12">
                            <div class="card minicard">
                                <form id="frmMantReporte">
                                    <input type="hidden" name="_token" value="NQsPUquIMghk2H9g5xuUFiFpREf84MUx0YpMBuRn"
                                        autocomplete="off">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-magnifying-glass left"></i> Filtros
                                            de búsqueda</span>
                                        <div class="row without-tb">
                                            <!-- filepath: c:\xampp\htdocs\hotelhub\resources\views\report\dashboardalojamientos.blade.php -->
                                            <div class="input-field col s12 m6 l2">
                                                <input id="fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ $fechaInicio->format('Y-m-d') }}">
                                                <label for="fecha_inicio" class="active">Fecha de inicio</label>
                                            </div>

                                            <div class="input-field col s12 m6 l2">
                                                <input id="hora_inicio" name="hora_inicio" type="time" value="00:00:00">
                                                <label for="hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l2">
                                                <input id="fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ $fechaFin->format('Y-m-d') }}">
                                                <label for="fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l2">
                                                <input id="hora_fin" name="hora_fin" type="time" value="23:59:59">
                                                <label for="hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l3">
                                                <button class="btn teal tooltipped btn-buscar" id="btn-Reporte"
                                                    data-tooltip="Actualizar todo" type="button"
                                                    onclick="actualizarGraficosDeAlojamientos();"><i
                                                        class="fa-solid fa-magnifying-glass white-text"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Izquierda -->
                        <div class="col s12 m12 l6 left-div">
                            <!-- Top de habitaciones -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title">
    <i class="fa-solid fa-arrow-trend-up left"></i>
    Top de
    <select class="browser-default records-displayed"
        id="records_displayed_top_productos" style="display:inline;"
        name="records_displayed_top_productos" onchange="topProductos();">
        <option value="10" {{ (int) $topHabitacionesLimit === 10 ? 'selected' : '' }}>10</option>
        <option value="25" {{ (int) $topHabitacionesLimit === 25 ? 'selected' : '' }}>25</option>
        <option value="50" {{ (int) $topHabitacionesLimit === 50 ? 'selected' : '' }}>50</option>
    </select> habitaciones
    <select class="browser-default records-displayed" id="asc_desc_top_productos"
        style="display:inline;" name="asc_desc_top_productos"
        onchange="topProductos();">
        <option value="desc" {{ $topHabitacionesOrden === 'desc' ? 'selected' : '' }}>mas alquiladas</option>
        <option value="asc" {{ $topHabitacionesOrden === 'asc' ? 'selected' : '' }}>menos alquiladas</option>
    </select>
    <button class="btn btn-floating tooltipped ml10 truncate right"
        data-tooltip="Actualizar" type="button" onclick="topProductos();">
        <i class="fa-solid fa-arrow-rotate-right"></i>
    </button>
</span>
<div class="row without-tb" style="margin-bottom: 0;">
    <div class="input-field col s12">
        <select id="categorias_top_habitaciones" name="categorias_top_habitaciones[]" multiple>
            @forelse ($categoriasHabitaciones as $categoriaHabitacion)
<option value="{{ $categoriaHabitacion->id }}"
{{ in_array((int) $categoriaHabitacion->id, $categoriasTopHabitacionesSeleccionadas->all(), true) ? 'selected' : '' }}>
 {{ $categoriaHabitacion->nombre }}
</option>
            @empty
                <option value="" disabled>No hay categorias disponibles</option>
            @endforelse
        </select>
        <label for="categorias_top_habitaciones">Categorias de habitaciones</label>
        <small class="grey-text text-darken-1">Por defecto se incluyen todas. Usa Actualizar para aplicar el filtro.</small>
    </div>
</div>
<div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div class="chart-container" style="position: relative; width:100%;">
                                                    <!-- Aquí va el gráfico amCharts -->
                                                    <div id="chartdiv" style="width: 100%; height: 500px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Evolución de habitación -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <!-- Título -->
                                        <span class="card-title">
                                            <i class="fa-solid fa-chart-line left"></i>
                                            Evolución de habitación
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button"
                                                onclick="recargarEvolucionHabitacion();">
                                                <i class="fa-solid fa-arrow-rotate-right"></i>
                                            </button>
                                        </span>

                                        <!-- Formulario Autocomplete -->
                                        <form id="evolucion-habitacion-form">
                                            <div class="row">
                                                <div class="col s12">
                                                    <div class="input-group-field" style="position: relative;">
                                                        <div class="input-field right-wrapper" style="margin-right:40px;">
                                                            <i class="fa-solid fa-bed prefix"></i>
                                                            <input type="text" name="buscar_habitacion"
                                                                id="evolucion-habitacion" class="autocomplete validate"
                                                                placeholder="Buscar habitación..." autocomplete="off"
                                                                data-autocomplete-url="{{ route('autocomplete.habitaciones') }}"
                                                                onfocus="activarAutocompleteHabitaciones()" />
                                                            <input type="hidden" name="habitacion_id"
                                                                id="evolucion-habitacion_id">
                                                            <label for="evolucion-habitacion"
                                                                class="active">Habitación</label>
                                                        </div>

                                                        <!-- Botón encima, alineado al lado derecho del input -->
                                                        <button class="btn red tooltipped"
                                                            id="evolucion-btn-quitar-habitacion"
                                                            data-tooltip="Deseleccionar habitación" type="button"
                                                            onclick="eliminarHabitacionSeleccionada();"
                                                            style="display:none; position: absolute; top: 8px; right: 0; padding: 0 10px; height: 40px;">
                                                            <i class="fa-solid fa-xmark"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>


                                        <!-- Contenedor del gráfico -->
                                        <div class="row" style="margin-top: 30px;">
                                            <div id="evolucion-chart-container">
                                                <div id="chartdiv-evolucion" style="width: 100%; height: 500px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Evolución de pedidos -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-chart-line left"></i>
                                            Evolución de alojamientos
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button"
                                                onclick="evolucionAlojamientos();"><i
                                                    class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div class="chart-container" style="position: relative; width:100%;">
                                                    <div id="chartdiv-evolucion-alojamientos"
                                                        style="width: 100%; height: 350px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- Derecha -->
                        <div class="col s12 m12 l6 right-div">
                            <!-- Top de categorías -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-arrow-trend-up left"></i>
    Top de <select class="browser-default records-displayed"
        id="records_displayed_top_categorias" style="display:inline;"
        name="records_displayed_top_categorias" onchange="topCategorias();">
        <option value="10" {{ (int) $topCategoriasLimit === 10 ? 'selected' : '' }}>10</option>
        <option value="25" {{ (int) $topCategoriasLimit === 25 ? 'selected' : '' }}>25</option>
        <option value="50" {{ (int) $topCategoriasLimit === 50 ? 'selected' : '' }}>50</option>
    </select> categorias <select class="browser-default records-displayed"
        id="asc_desc_top_categorias" style="display:inline;"
        name="asc_desc_top_categorias" onchange="topCategorias();">
        <option value="desc" {{ $topCategoriasOrden === 'desc' ? 'selected' : '' }}>mas alquiladas</option>
        <option value="asc" {{ $topCategoriasOrden === 'asc' ? 'selected' : '' }}>menos alquiladas</option>
    </select>
    <button class="btn btn-floating tooltipped ml10 truncate right"
        data-tooltip="Actualizar" type="button" onclick="topCategorias();"><i
            class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div id="chartdiv-categorias" style="width: 100%; height: 500px;"></div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Alojamientos por piso -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-store left"></i>
                                            Alojamientos por piso
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button" onclick="pedidosPorSalon();"><i
                                                    class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div id="chartdiv-alojamientos-piso" style="width: 100%; height: 300px;">
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Alojamientos por responsable -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-id-card-clip left"></i>
                                            Alojamientos por responsable
                                            <button class="btn btn-floating tooltipped ml10 truncate right"
                                                data-tooltip="Actualizar" type="button"
                                                onclick="alojamientosPorResponsable();"><i
                                                    class="fa-solid fa-arrow-rotate-right"></i></button></span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div class="chart-container" style="position: relative; width:100%;">
                                                    <div id="chartdiv-alojamientos-responsable"
                                                        style="width: 100%; height: 300px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="top_categorias_data" value='@json($topCategorias)'>

    <input type="hidden" id="top_habitaciones_data" value='@json($topHabitaciones)'>

    <input type="hidden" id="alojamientos_piso_data" value='@json($alojamientosPorPiso)'>
    <input type="hidden" id="alojamientos_responsable_data" value='@json($alojamientosPorResponsable)'>

    <input type="hidden" id="evolucion_alojamientos_data" value='@json($evolucionAlojamientos)'>
@endsection

