<div id="filtros-control-habitaciones" class="row control-habitaciones-filtros" style="display: none;">
    <div class="input-field col s12 m6 l5 control-filtro-busqueda" style="margin-top: 0;">
        <i class="material-icons prefix">search</i>
        <input id="buscar-control-habitaciones" type="text" class="validate" autocomplete="off"
            oninput="filtrarControlHabitaciones()"
            aria-label="Buscar por habitación, tipo o huésped">
        <label for="buscar-control-habitaciones">Habitación, tipo o huésped</label>
        <span id="resultado-control-habitaciones" class="helper-text" aria-live="polite"></span>
    </div>

    <div class="input-field col s12 m3 l2 control-filtro-select" style="margin-top: 0;">
        <select id="estado-control-habitaciones" class="validate" onchange="filtrarControlHabitaciones()">
            <option value="">Todos los estados</option>
            <option value="ocupada">Ocupada</option>
            <option value="disponible">Disponible</option>
            <option value="reservada">Reservada</option>
            <option value="limpieza">Limpieza</option>
            <option value="mantenimiento">Mantenimiento</option>
        </select>
        <label for="estado-control-habitaciones">Estado</label>
        <span class="helper-text" id="estado-control-habitaciones_spn" data-error="" data-success=""></span>
    </div>

    <div class="input-field col s12 m3 l2 control-filtro-select" style="margin-top: 0;">
        <select id="piso-control-habitaciones" class="validate" onchange="filtrarControlHabitaciones()">
            <option value="">Todos los pisos</option>
            @foreach ($Pisos as $piso)
                <option value="{{ $piso->id }}">{{ $piso->nombre }}</option>
            @endforeach
        </select>
        <label for="piso-control-habitaciones">Piso</label>
        <span class="helper-text" id="piso-control-habitaciones_spn" data-error="" data-success=""></span>
    </div>

    <div class="input-field col s10 m10 l2 control-filtro-select" style="margin-top: 0;">
        <select id="tipo-control-habitaciones" class="validate" onchange="filtrarControlHabitaciones()">
            <option value="">Todos los tipos</option>
            @foreach ($Categorias as $categoria)
                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
            @endforeach
        </select>
        <label for="tipo-control-habitaciones">Tipo de habitación</label>
        <span class="helper-text" id="tipo-control-habitaciones_spn" data-error="" data-success=""></span>
    </div>

    <div class="col s2 m2 l1 control-filtro-clear">
        <button id="limpiar-control-habitaciones" type="button" class="btn-flat tooltipped control-filtro-limpiar"
            onclick="limpiarFiltrosControlHabitaciones()" data-position="top" data-tooltip="Limpiar filtros"
            aria-label="Limpiar filtros">
            <i class="fa-solid fa-eraser"></i>
        </button>
    </div>
</div>
