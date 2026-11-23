<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title"><i class="fa-solid fa-cash-register left"></i>{{ $tituloHistorial ?? 'Historial de Alojamientos' }}</span>
                @if (!empty($subtituloHistorial))
                    <p class="grey-text text-darken-1 without-tb" style="margin-top: 8px;">{{ $subtituloHistorial }}</p>
                @endif
            </div>
            <div class="card-body bg-white">
                <div id="tb-container" class="table-responsive">
                      <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                                <thead class="thead-custom">
                                    <tr>
                                        
                                        <th>Habitación</th>
                                        <th>Huespeds</th>
                                        <th>Ingreso</th>
                                        <th>Salida Programada</th>
                                        <th>Salida Real</th>
                                        <th>Estadía</th>
                                        <th>Precio Hab.</th>
                                        <th>Early Check-In</th>
                                        <th>Late Checkout</th>
                                        <th>Descuento</th>
                                        <th>Total</th>
                                        <th>Moneda</th>

                                        <th>Comentario</th>
                                        <th>Estado</th>
                                       
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($movimientoHabitacion as $mov)
                                        <tr>
                                            <td class="inpt-cantidad-mc ">{{ $mov->habitacion->numero ?? '-' }}</td>
                                            <td>
                                                @foreach ($mov->huespeds as $huesped)
                                                    {{ $huesped->persona->personamaestro->nrodoc }} -
                                                    {{ $huesped->persona->nombres }} {{ $huesped->persona->apellidos }}<br>
                                                @endforeach
                                                @if ($mov->huespeds->isEmpty())
                                                    0 -CLIENTES VARIOS
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($mov->fechaingreso)->format('Y-m-d') }}<br>{{ \Carbon\Carbon::parse($mov->fechaingreso)->format('h:i:s A') }}</td>

                                            <td>
                                                @if($mov->fechasalida)
                                                    {{ \Carbon\Carbon::parse($mov->fechasalida)->format('Y-m-d') }}<br>{{ \Carbon\Carbon::parse($mov->fechasalida)->format('h:i:s A') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            
                                            <td>
                                                @if($mov->fecha_salida_real)
                                                    {{ \Carbon\Carbon::parse($mov->fecha_salida_real)->format('Y-m-d') }}<br>{{ \Carbon\Carbon::parse($mov->fecha_salida_real)->format('h:i:s A') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            
                                            <td>
                                                {{ $mov->horas 
                                                    ? $mov->horas . ' hrs' 
                                                    : ($mov->dias == 1 
                                                        ? '1 noche' 
                                                        : ( number_format($mov->dias, 0) ?? 0) . ' noches') 
                                                }}
                                            </td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($mov->preciohabitacion, 2) }}</td>
                                            <td class="inpt-cantidad-mc "> {{ number_format($mov->early_checkin ?? 0, 2) }}</td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($mov->late_checkout ?? 0, 2) }}</td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($mov->descuento ?? 0, 2) }}</td>
                                            <td class="inpt-cantidad-mc " > {{ number_format($mov->total, 2) }}</td>
                                            <td>{{ $mov->moneda }}</td>

                                        
                                            <td>{{ $mov->comentario ?? '-' }}</td>
                                            <td>
                                                <span class="new badge {{ $mov->situacion === 'C' ? 'green' : 'yellow black-text' }}"
                                                    data-badge-caption="{{ $mov->situacion === 'C' ? 'Culminada' : 'En curso' }}">
                                                </span>
                                            </td>
                                            
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="14" class="center-align grey-text text-darken-1">
                                                No se encontraron alojamientos en el rango seleccionado.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                
                                
                            </table>
                </div>

            </div>
        </div>

    </div>
</div>
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
