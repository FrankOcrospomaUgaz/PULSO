<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">Asignar Empleado - Housekeeping {{ $housekeeping->fecha_programada }}</span>
            </div>
            <div class="card-body bg-white">

                <form  id="form" method="POST"  enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="form-action-url"
                    value="{{ route('housekeeping.asignar.store', ) }}">
                    <input type="hidden" name="housekeeping_id" value="{{ $housekeeping->id }}">

                    <table class="highlight striped centered">
                        <thead class="thead-custom">
                            <tr>
                                <th>Habitación</th>
                                <th>Empleado a asignar</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detalles as $detalle)
                                <tr>
                                    <td>{{ $detalle->habitacion->numero }}</td>
                                    <td>
                                        <div class="input-field">
                                            <select class="select2 empleados" name="empleados[{{ $detalle->id }}]">
                                                <option value="" disabled {{ $detalle->empleado_id ? '' : 'selected' }}>
                                                    Asignar a un empleado
                                                </option>
                                                @foreach ($empleados as $empleado)
                                                    <option value="{{ $empleado->id }}"
                                                        {{ $detalle->empleado_id == $empleado->id ? 'selected' : '' }}>
                                                        {{ $empleado->nombres }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="submit" class="btn waves-effect blue">
                                            <i class="material-icons">check</i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>

            </div>
        </div>
    </div>
</div>
