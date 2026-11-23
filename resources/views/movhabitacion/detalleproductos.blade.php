<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">Detalle de Productos / Servicios	</span>
            </div>
            <div class="card-body bg-white">
                    <table class="highlight striped centered detalle-productos" id="data-table">
                        <thead class="bg-primary">
                            <tr style="color: white">
                                <th>Descripción</th>
                                <th>Precio compra</th>
                                <th>Unidad</th>
                                <th>Cantidad</th>

                                <th>IGV</th>
                                <th>Precio Unitario</th>

                                <th>Monto</th>
                                <th>Código</th>
                                <th>Clasificacion</th>

                                <th>Estado pago</th>


                            </tr>
                        </thead>
                        <tbody>
                            <!-- Decodificar el JSON y recorrerlo -->
                            @php
                                $detallesMovimientos = json_decode($detallesJson, true);
                            @endphp

                            @foreach ($detallesMovimientos as $detalle)
                            <tr data-producto-id="{{ $detalle['producto_id'] }}">

                                <td>{{ $detalle['descripcion'] }}</td>
                                <td class="inpt-cantidad-mc">{{ number_format($detalle['precio_compra'], 2) }}</td>
                                <td>{{ $detalle['unidad'] }}</td>
                                <td class="inpt-cantidad-mc cantidad" >
                                    <span id="cantidad_{{ $detalle['producto_id'] }}">{{ $detalle['cantidad'] }}</span>
                                </td>
                                <td class="inpt-cantidad-mc">{{ number_format($detalle['igv'], 2) }}</td>
                                <td class="inpt-cantidad-mc precio-unitario">{{ number_format($detalle['precio_unitario'], 2) }}</td>
                                <td class="inpt-cantidad-mc">
                                    <span class="precio-total">
                                        {{ number_format($detalle['total'], 2) }}
                                    </span>
                                </td>
                                <td>{{ $detalle['codigo'] }}</td>
                              

                                    <td>
                                        <span class="new badge  {{ $detalle['clasificacion'] == 'Bien' ? 'purple' : 'red' }}"  data-badge-caption="  {{ $detalle['clasificacion'] }}">
                                          
                                        </span>
                                    </td>
                                    <td>
                                        <span class="new badge {{ $detalle['situacion'] == 'P' ? 'green white-text' : 'yellow black-text' }} "  data-badge-caption="  {{  $detalle['situacion'] == 'P' ? 'Pagado' : 'Pendiente' }}">
                                            
                                        </span>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                        @if ($totalProductosConsumidos > 0)
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="right-align"
                                        style="font-weight: bold; text-align: right">Total Productos /
                                        Servicios</td>
                                    <td colspan="1" class="inpt-cantidad-mc" style="font-weight: bold;">
                                        S/. {{ number_format($totalProductosConsumidos, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif

                    </table>
                </div>
            </div>
        </div>
    </div>



