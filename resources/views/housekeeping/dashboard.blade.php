@extends('layouts.app')

@section('content')
<div class="main-body">
    <div class="row">
        <div class="card main-content">
            <div class="card-body bg-white">
                
                <!-- Filtro de Estado -->
                <div class="mb-4">
                    <label for="estado-filter" class="block text-gray-700">Filtrar por Estado:</label>
                    <select id="estado-filter" class="w-full p-2 border border-gray-300 rounded-lg" onchange="filterByEstado()">
                        <option value="">Todos</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="En progreso">En progreso</option>
                        <option value="Completado">Completado</option>
                    </select>
                </div>

                <!-- Tabla Agrupada -->
                <div id="tb-container" class="table-responsive">
                    @foreach ($housekeepingJornadas as $jornada)
                        <h6 class="text-lg font-bold bg-gray-200 p-2 rounded mt-4">
                            Jornada: {{ $jornada->fecha_programada }}
                             {{-- | Estado: 
                            <span class="px-2 py-1 rounded text-white 
                                {{ $jornada->estado == 'Pendiente' ? 'bg-yellow-500' : 
                                   ($jornada->estado == 'En progreso' ? 'bg-blue-500' : 'bg-green-500') }}">
                                {{ $jornada->estado }}
                            </span> --}}    
                        </h6>
                        
                        <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                            <thead class="thead-custom">
                                <tr class="bg-primary text-white text-left">
                                    <th class="p-2">Habitación</th>
                                    <th class="p-2">Inicio</th>
                                    <th class="p-2">Fin</th>
                                    <th class="p-2">Tiempo</th>
                                    <th class="p-2">Estado</th>
                                    <th class="p-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jornada->housekeepingDetalles as $detalle)
                                    <tr class="border-b">
                                        <td class="p-2">{{ $detalle->habitacion->numero }}</td>
                                        <td class="p-2">{{ $detalle->inicio_limpieza ?? '-' }}</td>
                                        <td class="p-2">{{ $detalle->fin_limpieza ?? '-' }}</td>
                                        <td class="p-2">{{ $detalle->tiempo ? $detalle->tiempo . ' min' : '-' }}</td>
                                        <td class="p-2">
                                            <span class="px-2 py-1 rounded text-white 
                                                {{ $detalle->estado == 'Pendiente' ? 'bg-yellow-500' : 
                                                   ($detalle->estado == 'En progreso' ? 'bg-blue-500' : 'bg-green-500') }}">
                                                {{ $detalle->estado }}
                                            </span>
                                        </td>
                                        <td class="p-2">
                                            @if ($detalle->estado == 'Pendiente')
                                                <a href="{{ route('housekeeping.start', $detalle->id) }}"
                                                    class="btn-floating blue darken-1 waves-light tooltipped"
                                                    data-tooltip="Iniciar limpieza">
                                                    <i class="material-icons">play_arrow</i>
                                                </a>
                                            @elseif ($detalle->estado == 'En progreso')
                                                <a href="#" class="btn-floating green darken-1 waves-light tooltipped"
                                                    data-tooltip="Finalizar limpieza"
                                                    onclick="abrirModalCompletarLimpieza({{ $detalle->id }})">
                                                    <i class="material-icons">check</i>
                                                </a>
                                            @else
                                                <span class="text-gray-500">Completado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function filterByEstado() {
        let estado = document.getElementById('estado-filter').value;
        let rows = document.querySelectorAll("tbody tr");

        rows.forEach(row => {
            let estadoText = row.children[4].textContent.trim();
            row.style.display = estado === "" || estadoText === estado ? "" : "none";
        });
    }
</script>

@endsection
