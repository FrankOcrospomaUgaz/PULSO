<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PULSO+') }}</title>

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600&family=Roboto:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Datatables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
    <!-- Incluir jQuery y Slick (si no están ya incluidos) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css" />
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js"></script>


    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script> <!-- ✅ ESTA ES LA CLAVE -->
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <!-- Datatables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    {{--
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script> --}}
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <!-- Botones de exportación -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>

    <!-- Librerías para exportar -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <!-- Requerido para Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <!-- Requerido para PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <!-- Requerido para PDF -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/5d6dc726ekftnqpthhtl224c9jp0qc72bk9jmn84ca8yikfj/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script src="https://unpkg.com/mammoth/mammoth.browser.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>


    <!-- CSS de Select2 -->
    {{--
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/css/select2.min.css" rel="stylesheet" /> --}}

    <!-- JS de Select2 -->
    {{--
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/js/select2.min.js"></script> --}}

    <!-- Materialize JS -->

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pulso-brand.css') }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('img/pulso-favicon.jpg') }}">



</head>

<body class="font-sans antialiased" style="background-color: #f1f2f2;">

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- No actualizar estos elementos -->
        @include('layouts.navigation')
        @include('components.brand-loader')



        <!-- Modal Structure -->
        <div id="modal" class="modal card">
            <div id="modal-body-content">
            </div>

        </div>
        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Solo actualizar este contenedor dinámicamente -->
        <div id="main-content">
            @if (session('erp_billing_notices'))
                <div class="container" style="padding-top: 18px;">
                    @foreach (session('erp_billing_notices') as $notice)
                        <div class="card-panel {{ ($notice['type'] ?? '') === 'suspension_today' ? 'orange lighten-4' : 'amber lighten-4' }}" role="status" style="border-left: 5px solid #f59e0b;">
                            <strong>{{ ($notice['type'] ?? '') === 'due_soon' ? 'Aviso de pago' : 'Cuota pendiente' }}</strong>
                            <div>{{ $notice['message'] ?? 'Tienes una cuota pendiente.' }}</div>
                            <small>Vencimiento: {{ $notice['due_date'] ?? '—' }} · Saldo: S/ {{ number_format((float) ($notice['balance'] ?? 0), 2) }}</small>
                        </div>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </div>

      
    </div>
      <!-- Footer (no se actualiza dinámicamente) -->
        @include('layouts.footer')
    <!-- Contenedor del mensaje de éxito -->
    {{-- <div id="mensaje-exito" class="card z-depth-3 hide"
        style="position: fixed; bottom: 10px; left: 20px; max-width: 350px; background-color: #FFFFFF; border-radius: 20px; display: flex; align-items: center; padding: 15px; flex-direction: column;">

        <div style="display: flex; align-items: center; width: 100%;">
            <!-- Icono del check -->
            <div
                style="display: flex; justify-content: center; align-items: center; background-color: #E8F5E9; border-radius: 50%; width: 40px; height: 40px; margin-right: 15px;">
                <i class="material-icons green-text" style="font-size: 2rem;">check</i>
            </div>
            <!-- Mensaje de texto -->
            <div>
                <strong class="black-text" id="mensaje-titulo">Éxito</strong>
                <p class="grey-text text-darken-2" id="mensaje-texto">Bienvenido al módulo.</p>
            </div>
        </div>

        <!-- Barra de progreso -->
        <div
            style="width: 100%; height: 5px; background-color: #E0E0E0; border-radius: 5px; overflow: hidden; margin-top: 10px;">
            <div id="barra-progreso"
                style="width: 100%; height: 100%; background-color: #4CAF50; transition: width 5s linear;"></div>
        </div>
    </div> --}}

    <!-- Contenedor del mensaje de error -->
    <div id="mensaje-error" class="card z-depth-3 hide"
        style="position: fixed; bottom: 20px; left: 20px; max-width: 350px; background-color: #FFEBEE; border-radius: 10px; display: flex; align-items: center; padding: 15px;">
        <!-- Icono de error -->
        <div
            style="display: flex; justify-content: center; align-items: center; background-color: #FFCDD2; border-radius: 50%; width: 40px; height: 40px; margin-right: 15px;">
            <i class="material-icons red-text" style="font-size: 2rem;">error_outline</i>
        </div>
        <!-- Contenido del mensaje -->
        <div>
            <strong class="black-text" id="mensaje-error-titulo">Error 500</strong>
            <p class="grey-text text-darken-2" id="mensaje-error-texto">Ocurrió un problema inesperado.</p>
        </div>
    </div>



    <!-- Modal de confirmación de eliminación -->
    <div id="modal-confirm-delete" class="modal s12 m6 l4">
        <div class="modal-content center-align">
            <div class="swal2-icon swal2-warning swal2-icon-show"
                style="display: flex; justify-content: center; margin-bottom: 20px;">
                <div class="swal2-icon-content">!</div>
            </div>
            <h4 style="font-weight: bold;">Atención</h4>
            <p>¿Realmente desea eliminar el registro?</p>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: center; gap: 20px;">
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cancelar</a>
            <a id="confirm-delete-btn" href="#!"
                class="modal-close waves-effect waves-red btn red darken-2">Eliminar</a>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div id="modal-confirm-anular" class="modal s12 m6 l4">
        <div class="modal-content center-align">
            <div class="swal2-icon swal2-warning swal2-icon-show"
                style="display: flex; justify-content: center; margin-bottom: 20px;">
                <div class="swal2-icon-content">!</div>
            </div>
            <h4 style="font-weight: bold;">Atención</h4>
            <p>¿Realmente desea anular el registro?</p>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: center; gap: 20px;">
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cancelar</a>
            <a id="confirm-anular-btn" href="#!" class="modal-close waves-effect waves-red btn red darken-2">Anular</a>
        </div>
    </div>


    <!-- Modal de confirmación de eliminación -->
    <div id="modal-confirm-finalizar" class="modal s12 m6 l4">
        <div class="modal-content center-align">
            <div class="swal2-icon swal2-warning swal2-icon-show"
                style="display: flex; justify-content: center; margin-bottom: 20px; border-color: #c9dae1; color: #87adbd;">
                <div class="swal2-icon-content">?</div>
            </div>
            <h4 style="font-weight: bold;">Atención</h4>
            <h6 class="swal2-html-container" id="swal2-html-container" style="display: block;">¿Realmente desea
                finalizar la encuesta?</h6>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: center; gap: 20px;">
            <a id="confirm-finalizar-btn" href="#!" class="modal-close waves-effect waves-red btn"
                style="background-color: #36393f">Finalizar</a>
            <a href="#!" class="modal-close waves-effect waves-green btn" style="background-color: #6e7881">Cancelar</a>

        </div>
    </div>

    <div id="modal-container"></div>
    <div class="modal bottom-sheet" id="det_movventa-maintenance-submodal" tabindex="0"
        style="z-index: 1003; display: none;">
        <div class="modal-content">
            <div class="row without-top">
                <p class="grey-text text-darken-3 modal-title without-tb center" id="det_movventa-submodal-title">
                    <i class="fa-solid fa-bag-shopping"></i> Nueva Glosa
                </p>
            </div>
            <div class="row without-tb submodal-form" id="det_movventa-submodal-form" style="    margin-bottom: -20px;">
                <form id="frmMantDet_MovVenta">
                    <input type="hidden" name="_method" value="PUT">
                    <input id="det_movventa_id" name="det_movventa_id" type="hidden" value="1">
                    <input id="producto_id" name="producto_id" type="hidden" value="45204">
                    <input id="medidaproducto_id" name="medidaproducto_id" type="hidden" value="57977">

                    <div class="row without-bottom">
                        <div class="col s12 m12 l3" id="divCantidad">
                            <div class="row valign-wrapper">
                                <div class="col s4 center">
                                    <button type="button" class="btn-floating btn-small waves-effect waves-light"
                                        id="btn-restar" onclick="restarCantidad();">
                                        <i class="material-icons">remove</i>
                                    </button>
                                </div>
                                <div class="col s4 center input-field">
                                    <input id="cantidad" name="cantidad" type="text" autocomplete="off"
                                        style="font-size: 1.5rem; font-weight: bold;"
                                        class="inpt-cantidad-lc submit-form" inputmode="numeric" value="1">
                                    <label for="cantidad"></label>
                                    <span class="helper-text" id="cantidad_spn"></span>
                                </div>
                                <div class="col s4 center">
                                    <button type="button" class="btn-floating btn-small waves-effect waves-light"
                                        id="btn-sumar" onclick="sumarCantidad();">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="input-field col s12 m6 l1">
                            <input id="codigo" name="codigo" type="text" class="submit-form" value="-">
                            <label for="codigo" id="lblCodigo" class="active">Código</label>
                            <span class="helper-text" id="codigo_spn"></span>
                        </div>

                        <div class="input-field col s12 m6 l4">
                            <input id="descripcion" name="descripcion" type="text" class="submit-form">
                            <label for="descripcion" id="lblDescripcion" class="active">Descripción</label>
                            <span class="helper-text" id="descripcion_spn"></span>
                        </div>
                        @php
                            $unidades = App\Models\Unidad::all();
                            $igvs = App\Models\Igv::where('situacion', true)->get();
                            $igvPredeterminado = App\Models\RelParametrosucursal::where('sucursal_id', Illuminate\Support\Facades\Auth::user()->persona->sucursal_id)
                                ->where('parametro_id', 8)
                                ->value('valor');
                            $igvObjeto = App\Models\Igv::find($igvPredeterminado);

                        @endphp
                        <div class="input-field col s12 m6 l2 custom-dropdown">
                            <select id="unidad_id" name="unidad_id">
                                @foreach ($unidades as $unidad)
                                    <option value="{{ $unidad->id }}">
{{ $unidad->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="unidad_id">Unidad</label>
                            <span class="helper-text" id="unidad_id_spn"></span>
                        </div>

                        <div class="input-field col s12 m6 l2" id="divIgv_id">
                            <select id="igv_id" name="igv_id">
                                @foreach ($igvs as $igv)
                                    <option value="{{ $igv->porcentaje_igv }}" data-igv-id="{{ $igv->id }}" {{ ($igvObjeto && $igv->id == $igvObjeto->id) ? 'selected' : '' }}>
{{ $igv->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="igv_id">IGV</label>
                            <span class="helper-text" id="igv_id_spn"></span>
                        </div>


                        <div class="input-field col s12 m6 l2" id="divDet_movventa_monto">
                            <input id="det_movventa_monto" name="det_movventa_monto" type="text"
                                style="font-size: 1.5rem; font-weight: bold;" autocomplete="off"
                                class="inpt-cantidad-lc submit-form" inputmode="numeric" value="0">
                            <label for="det_movventa_monto" class="active">Monto</label>
                            <span class="helper-text" id="det_movventa_monto_spn">PEN</span>
                        </div>

                        <div class="input-field col s12 m6 l3">
                            <textarea id="det_movventa_comentario" name="det_movventa_comentario"
                                class="materialize-textarea" rows="1" oninput="autoResize(this);"></textarea>
                            <label for="det_movventa_comentario" id="lblDet_movventa_comentario">Notas</label>
                            <span class="helper-text" id="det_movventa_comentario_spn"></span>
                        </div>

                        <div class="input-field col s12 m6 l2" id="divDcto_porcentaje" style="display: none;">
                            <input id="dcto_porcentaje" name="dcto_porcentaje" type="text" autocomplete="off"
                                class="inpt-cantidad-lc submit-form" inputmode="numeric">
                            <label for="dcto_porcentaje" class="active">Dcto. (%)</label>
                            <span class="helper-text" id="dcto_porcentaje_spn"></span>
                        </div>

                        <div class="input-field col s12 m6 l2" id="divDcto_monto" style="display: none;">
                            <input id="dcto_monto" name="dcto_monto" type="text" autocomplete="off"
                                class="inpt-cantidad-lc submit-form" inputmode="numeric">
                            <label for="dcto_monto" class="active">Dcto. (por monto)</label>
                            <span class="helper-text" id="dcto_monto_spn">PEN</span>
                        </div>

                        <div class="input-field col s12 m6 l2" id="divDet_movventa_preciocompra" style="display: none;">
                            <input id="det_movventa_preciocompra" name="det_movventa_preciocompra" type="text"
                                style="font-size: 1.5rem; font-weight: bold;" autocomplete="off"
                                class="inpt-cantidad-lc submit-form" disabled inputmode="numeric">
                            <label for="det_movventa_preciocompra" class="active">P. compra</label>
                            <span class="helper-text" id="det_movventa_preciocompra_spn">PEN</span>
                        </div>

                        <div class="col s12 m6 l2" id="divDet_categoria" style="display: none;">
                            <p id="pDet_categoria" style="text-align: left;">
                                <label for="det_categoria_all" class="truncate">
                                    <input type="checkbox" class="filled-in" id="det_categoria_all"
                                        onchange="todosDet_Categoria();">
                                    <span id="det_categoria_all_spn">Seleccionar todo</span>
                                </label>
                            </p>
                        </div>

                        <div class="col s12 m6 l2" id="divOpc_categoria" style="display: none;">
                            <p id="pOpc_categoria" style="text-align: left;"></p>
                        </div>

                        <div class="col s12 m6 l4" id="divUnir" style="display: none;">
                            <p style="text-align: left;">
                                <label class="truncate" for="unir">
                                    <input type="checkbox" class="filled-in" id="unir" name="unir" value="S" checked>
                                    <span>Unir cantidades nuevas de producto</span>
                                    <span class="helper-text" id="unir_spn"></span>
                                </label>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button"
                class="btn-flat waves-effect waves-dark btn-cancelar btn-mantenimiento grey-text text-darken-3"
                id="btn-cancel-Det_MovVenta"
                onclick="M.Modal.getInstance($('#det_movventa-maintenance-submodal')).close();">
                CANCELAR<i class="material-icons right">clear</i>
            </button>
            <button type="button" class="btn darken-2" id="btn-save-Det_MovVenta" onclick="actualizarMovVenta();">
                GUARDAR<i class="material-icons right">save</i>
            </button>
        </div>
    </div>








    @yield('scripts')

    <script src="{{ asset('js/app.js') }}"></script>





    <style>
        /* Clase personalizada para el SweetAlert2 */
        .rounded-swal {
            border-radius: 23px !important;
            /* Redondeado más suave */
        }

        .swal2-icon.swal2-info {
            margin-top: 60px;
            /* ⬅️ Puedes ajustar este número */
            margin-bottom: -10px;
        }
    </style>


</body>

</html>
