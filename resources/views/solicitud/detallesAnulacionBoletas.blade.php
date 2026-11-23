
<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i id="icono-seccion" class="fa fa-solid fa-receipt left"></i>Comprobantes
                </p>     
            </div>
            <div class="card-body bg-white">
                <div id="tb-container" class="table-responsive">
                    <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                        <thead class="thead-custom">
                            <tr>
                                <th class="center">Tipo</th>
                                <th class="center">Número</th>
                                <th class="center">Nro. de documento</th>
                                <th class="center">Persona</th>
                                <th class="center">Moneda</th>
                                <th class="center">Total</th>
                                <th class="center">Estado</th>
                                <th class="center">Fecha de solicitud</th>
                                <th class="center">F. de envío</th>
                                <th class="center">F. de respuesta</th>
                                <th class="center">F. de anulación</th>
                                <th class="center" >RUC de facturador</th>
                               
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($solicituds as $solicitud)
                                <tr>
                                    <td class="center">{{ $solicitud->tipo_documento == 'B' ? 'Boleta' : 'Factura' }}
                                    </td>
                                    
                                    <td class="center">{{ $solicitud->serie . '-' . $solicitud->correlativo }}</td>
                                    <td class="center">{{ $solicitud->persona->personamaestro->nrodoc }}</td>
                                    <td class="center">{{ $solicitud->movimiento->persona }}</td>
                                    <td class="center">{{ $solicitud->moneda }}</td>
                                    <td class="center inpt-cantidad-mc">{{ $solicitud->total }}</td>
                                    <td class="center">
                                        @php
                                            switch($solicitud->estado) {
                                                case 'R':
                                                    $estado = 'Aún no declarado';
                                                    $color = 'yellow black-text';
                                                    break;
                                                case 'E':
                                                case 'P':
                                                    $estado = 'No registrado';
                                                    $color = 'orange black-text';
                                                    break;
                                                case 'M':
                                                case 'T':
                                                    $estado = 'Declarado y activo';
                                                    $color = 'green';
                                                    break;
                                                case 'B':
                                                    $estado = 'Declarado y anulado';
                                                    $color = 'red white-text';
                                                    break;
                                                case 'I':
                                                    $estado = 'Declarado e incorrecto';
                                                    $color = 'purple white-text';
                                                    break;
                                                case 'S':
                                                case 'C':
                                                    $estado = 'Declarado sin comprobar';
                                                    $color = 'blue white-text';
                                                    break;
                                                default:
                                                    $estado = 'No tributable';
                                                    $color = 'grey white-text';
                                                    break;
                                            }
                                        @endphp
                                        <span class="new badge {{ $color }}" data-badge-caption="{{ $estado }}"></span>
                                    </td>
                                    
                                    <td class="center">{{ $solicitud->fecha }}</td>
                                    <td class="center">{{ $solicitud->fecha_envio }}</td>
                                    <td class="center">{{ $solicitud->fecha_respuesta }}</td>
                                    <td class="center" >{{ $solicitud->fecha_anulacion }}</td>
                                    <td class="center">
                                              {{ $solicitud->facturador->sucursal->empresa->ruc }}</td>

                                </tr>
                            @endforeach
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