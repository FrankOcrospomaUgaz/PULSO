<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">Progreso de Housekeeping - {{ $housekeeping->fecha_programada }}</span>
            </div>
            <div class="card-body bg-white">

                <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                    <thead class="thead-custom">
                        <tr>
                            <th>Habitación</th>
                            <th>Empleado Asignado</th>
                            <th>Estado</th>
                            <th class="p-2">Inicio</th>
                            <th class="p-2">Fin</th>
                            <th class="p-2">Tiempo</th>
                            <th class="p-2">Observaciones</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->habitacion->numero }}</td>
                                <td>{{ $detalle->persona->nombres }}</td>
                                <td>
                                    <span 
                                        class="new badge 
                                            {{ $detalle->estado == 'Pendiente' ? 'red' : '' }}
                                            {{ $detalle->estado == 'En progreso' ? 'yellow black-text' : '' }}
                                            {{ $detalle->estado == 'Completado' ? 'green' : '' }}"
                                        data-badge-caption="{{ $detalle->estado }}">
                                    </span>
                                </td>
                                <td class="p-2">{{ $detalle->inicio_limpieza ?? '-' }}</td>
                                <td class="p-2">{{ $detalle->fin_limpieza ?? '-' }}</td>
                                <td class="p-2">{{ $detalle->tiempo ? $detalle->tiempo . ' min' : '-' }}</td>
                                <td class="p-2">{{ $detalle->observaciones ?? '-' }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
