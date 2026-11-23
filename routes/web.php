<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeccioneController;
use App\Http\Controllers\TablaOperacioneController;
use App\Http\Controllers\AutocompleteController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\HousekeepingLoginController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\SalonPackageController;
use App\Http\Controllers\SalonServiceController;
use App\Events\CheckoutReminder;
use App\Models\MovhabitacionHabitacion;
use App\Models\Conceptopago;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/housekeeping/login/{ruc}', [HousekeepingLoginController::class, 'showLoginForm'])->name('housekeeping.login.form');
Route::post('/housekeeping/login', [HousekeepingLoginController::class, 'login'])->name('housekeeping.login');
Route::post('/housekeeping/logout/{ruc}', [HousekeepingLoginController::class, 'logout'])->name('housekeeping.logout');


Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/download-bill/{publicName}/{format}', [App\Http\Controllers\SolicitudController::class, 'downloadBill'])
    ->where('format', 'xml|cdr')
    ->name('download.bill');

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'sucursal.active'])->name('dashboard');

Route::middleware(['auth', 'sucursal.active'])->group(function () {
    if (app()->environment('local')) {
        Route::get('/dev/checkout-reminder-test', function () {
            $user = Auth::user();
            $persona = $user ? $user->persona : null;
            $sucursalId = $persona ? $persona->sucursal_id : null;
            $detalle = MovhabitacionHabitacion::with(['habitacion', 'movhabitacion'])
                ->when($sucursalId, function ($query) use ($sucursalId) {
                    $query->where(function ($inner) use ($sucursalId) {
                        $inner->whereHas('movhabitacion', function ($movhabitacionQuery) use ($sucursalId) {
                            $movhabitacionQuery->where('sucursal_id', $sucursalId);
                        })->orWhereHas('habitacion', function ($habitacionQuery) use ($sucursalId) {
                            $habitacionQuery->where('sucursal_id', $sucursalId);
                        });
                    });
                })
                ->latest('fechacheckout')
                ->first();

            if (!$detalle) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No hay habitaciones registradas para probar.',
                ], 404);
            }

            event(new CheckoutReminder($detalle));

            $habitacion = $detalle->habitacion;

            return response()->json([
                'ok' => true,
                'habitacion' => $habitacion ? ($habitacion->numero ?? $detalle->habitacion_id) : $detalle->habitacion_id,
            ]);
        })->name('dev.checkout.reminder.test');
    }

    Route::post('/checkout-reminder/ping', function () {
        $lock = Cache::lock('checkout-reminder-ping', 55);
        if (!$lock->get()) {
            return response()->json(['ok' => true, 'locked' => true]);
        }

        try {
            Artisan::call('habitaciones:checkout-reminder');
        } finally {
            optional($lock)->release();
        }

        return response()->json(['ok' => true]);
    })->name('checkout.reminder.ping');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/housekeeping/jornada', [HousekeepingLoginController::class, 'index'])->name('housekeeping.dashboard');
    Route::get('/housekeeping/start/{id}', [HousekeepingLoginController::class, 'startCleaning'])->name('housekeeping.start');
    Route::post('/housekeeping/finish/{id}', [HousekeepingLoginController::class, 'finishCleaning'])->name('housekeeping.finish');
    Route::middleware(['auth', 'check.permission:15'])->group(function () {
        Route::resource('tablas', App\Http\Controllers\TablaController::class);
        Route::resource('campos', App\Http\Controllers\CampoController::class);
        Route::resource('tablas.campos', App\Http\Controllers\TablaCampoController::class);
        Route::resource('tablas.operaciones', TablaOperacioneController::class);
        Route::resource('operaciones', App\Http\Controllers\OperacioneController::class);
        Route::resource('modulos', App\Http\Controllers\ModuloController::class);
        Route::resource('tipoopciones', App\Http\Controllers\TipoopcioneController::class);
        Route::resource('menuopciones', App\Http\Controllers\MenuopcioneController::class);
        Route::resource('perfiles', App\Http\Controllers\PerfileController::class);
        Route::resource('parametros', App\Http\Controllers\ParametroController::class);
        Route::resource('categoriaparametros', App\Http\Controllers\CategoriaparametroController::class);
        Route::resource('perfilsucursal', App\Http\Controllers\PerfilsucursalController::class);
        Route::resource('unidad', App\Http\Controllers\UnidadController::class);
        Route::resource('rel_tablasucursal', App\Http\Controllers\RelTablasucursalController::class);
        Route::resource('conceptopago', App\Http\Controllers\ConceptopagoController::class);
        Route::resource('mediopago', App\Http\Controllers\MediopagoController::class);
        Route::resource('tarjeta', App\Http\Controllers\TarjetumController::class);
        Route::resource('banco', App\Http\Controllers\BancoController::class);
        Route::resource('billeteradigital', App\Http\Controllers\BilleteradigitalController::class);
        Route::resource('pasarelapago', App\Http\Controllers\PasarelapagoController::class);
        Route::resource('igv', App\Http\Controllers\IgvController::class);
        Route::resource('motivomovimiento', App\Http\Controllers\MotivomovimientoController::class);
        Route::resource('rel_parametrosucursal', App\Http\Controllers\RelParametrosucursalController::class);
        Route::resource('rel_parametroempresa', App\Http\Controllers\RelParametroempresaController::class);
        Route::resource('rol', App\Http\Controllers\RolController::class);
        Route::resource('secciones', App\Http\Controllers\SeccioneController::class);
        Route::resource('tipomovimiento', App\Http\Controllers\TipomovimientoController::class);
        Route::resource('tipodocumento', App\Http\Controllers\TipodocumentoController::class);
        Route::resource('empresas', App\Http\Controllers\EmpresaController::class);

    });
    Route::resource('motivoreserva', App\Http\Controllers\MotivoreservaController::class);
    Route::resource('sucursal', App\Http\Controllers\SucursalController::class);
    Route::resource('plantilla_contrato', App\Http\Controllers\PlantillaContratoController::class);
    Route::resource('huesped', App\Http\Controllers\HuespedController::class);
    Route::resource('pisos', App\Http\Controllers\PisoController::class);
    Route::resource('tipohabitacion', App\Http\Controllers\TipohabitacionController::class);
    Route::resource('habitacion', App\Http\Controllers\HabitacionController::class);
    Route::post('habitacion/destroy-many', [App\Http\Controllers\HabitacionController::class, 'destroyMany'])->name('habitacion.destroyMany');
    Route::resource('producto', App\Http\Controllers\ProductoController::class);
    Route::resource('marca', App\Http\Controllers\MarcaController::class);
    Route::resource('categoria', App\Http\Controllers\CategoriumController::class);
    Route::resource('movalmacen', App\Http\Controllers\MovalmacenController::class);
    Route::resource('caja', App\Http\Controllers\CajaController::class);
    Route::resource('turno', App\Http\Controllers\TurnoController::class);
    Route::resource('movcaja', App\Http\Controllers\MovcajaController::class);
    Route::resource('movventa', App\Http\Controllers\MovventumController::class);
    Route::resource('movcompra', App\Http\Controllers\MovcompraController::class);
    Route::resource('movhabitacion', App\Http\Controllers\MovhabitacionController::class);
    Route::resource('salon', App\Http\Controllers\SalonController::class);
    Route::resource('ambiente', App\Http\Controllers\AmbienteController::class);
    Route::resource('edificio', App\Http\Controllers\EdificioController::class);
    Route::resource('contrato', App\Http\Controllers\ContratoController::class);
Route::resource('tipoambiente', App\Http\Controllers\TipoambienteController::class);
    Route::resource('stockproducto', App\Http\Controllers\StockproductoController::class);
    Route::resource('kardex', App\Http\Controllers\KardexController::class);
    Route::resource('facturador', App\Http\Controllers\FacturadorController::class);
    Route::resource('persona', App\Http\Controllers\PersonaController::class);
    Route::resource('rel_turnocaja', App\Http\Controllers\RelTurnocajaController::class);
    Route::resource('solicitud', App\Http\Controllers\SolicitudController::class);
    Route::resource('encuesta', App\Http\Controllers\EncuestumController::class);
    Route::resource('pregunta', App\Http\Controllers\DetEncuestumController::class);
    Route::resource('respuesta', App\Http\Controllers\RespuestumController::class);
    Route::resource('categoriapregunta', App\Http\Controllers\CategoriapreguntumController::class);
    Route::resource('housekeeping', App\Http\Controllers\HousekeepingController::class);
    Route::get('tipohabitacion/{id}/imagenes', [App\Http\Controllers\TipohabitacionController::class, 'imagenes'])->name('tipohabitacion.imagenes');
    Route::post('tipohabitacion/{id}/imagenes', [App\Http\Controllers\ImagenesTipohabitacionController::class, 'actualizarImagenes'])->name('imagenes.actualizar');

    Route::get('huesped/{id}/alojamientos', [App\Http\Controllers\HuespedController::class, 'alojamientos'])->name('huesped.alojamientos');

    Route::get('/housekeeping/progreso/{id}', [HousekeepingController::class, 'progreso'])->name('housekeeping.progreso');
    Route::get('housekeeping/asignar/{id}', [App\Http\Controllers\HousekeepingController::class, 'asignar'])->name('housekeeping.asignar');
    Route::post('housekeeping/atenderstore', [App\Http\Controllers\HousekeepingController::class, 'asignarStore'])->name('housekeeping.asignar.store');
    Route::get('/my_workshift', [App\Http\Controllers\MenuopcioneController::class, 'my_workshift'])->name('my_workshift');

    Route::get('movreserva/create', [App\Http\Controllers\MovreservaController::class, 'create'])->name('movreserva.create');
    Route::resource('movreserva', App\Http\Controllers\MovreservaController::class);
    Route::resource('movreserva_paquete', App\Http\Controllers\MovreservaPaqueteController::class);
    Route::get('movreserva_paquete/create', [App\Http\Controllers\MovreservaPaqueteController::class, 'create'])->name('movreserva_paquete.create');
    Route::get('salon/{id}/servicios', [App\Http\Controllers\SalonController::class, 'servicios'])->name('salon.servicios');
    Route::get('salon/{id}/paquetes', [App\Http\Controllers\SalonController::class, 'paquetes'])->name('salon.paquetes');
    Route::resource('salon.servicios', SalonServiceController::class);
    Route::resource('salon.paquetes', SalonPackageController::class);
    Route::resource('origenreserva', App\Http\Controllers\OrigenreservaController::class);
    Route::resource('situacion', App\Http\Controllers\SituacionController::class);
    Route::resource('contrato.compromiso_pago', App\Http\Controllers\CompromisoPagoController::class);
    Route::get('contratos/{id}/compromiso_pago/pagar', [App\Http\Controllers\CompromisoPagoController::class, 'pagar'])->name('contrato.comprimiso_pago.pagar');
    Route::post('contratos/compromiso_pago/pagostore', [App\Http\Controllers\CompromisoPagoController::class, 'pagostore'])->name('contrato.compromiso_pago.pagostore');

    Route::delete('movreserva/{id}/anular', [App\Http\Controllers\MovreservaController::class, 'anular'])->name('movreserva.anular');
    Route::get('movreserva/{id}/detalles', [App\Http\Controllers\MovreservaController::class, 'verDetalles'])->name('movreserva.detalles');
    Route::get('movreserva/{id}/atender', [App\Http\Controllers\MovreservaController::class, 'atender'])->name('movreserva.atender');
    Route::post('movreserva/atenderstore', [App\Http\Controllers\MovreservaController::class, 'atenderstore'])->name('movreserva.atenderstore');
    Route::get('movreservagenerarpdf/{id}', [App\Http\Controllers\MovreservaController::class, 'generarPDF'])->name('movreserva.generarPDF');
    Route::get('movreserva/reporte/pdf', [App\Http\Controllers\MovreservaController::class, 'generarPDFReporte'])->name('movreserva.reporte.pdf');
    Route::get('movreserva/reporte/excel', [App\Http\Controllers\MovreservaController::class, 'exportarExcelReporte'])->name('movreserva.reporte.excel');
    Route::get('/reservas/habitaciones-disponibles', [App\Http\Controllers\MovreservaController::class, 'habitacionesDisponibles']);
    Route::get('/contratos/ambientes-por-edificio/{edificio}', [App\Http\Controllers\ContratoController::class, 'ambientesPorEdificio']);
    Route::get('/ambientes/{id}/datos', [App\Http\Controllers\AmbienteController::class, 'obtenerDatos']);
    Route::get('contratos/{id}/renovar', [App\Http\Controllers\ContratoController::class, 'renovar'])->name('contrato.renovar');
    Route::get('contratos/{id}/pagos', [App\Http\Controllers\CompromisoPagoController::class, 'index'])->name('contrato.pagos');
    Route::delete('contratos/{id}/anular', [App\Http\Controllers\ContratoController::class, 'anular'])->name('contrato.anular');
    Route::delete('contratos/{id}/finalizar', [App\Http\Controllers\ContratoController::class, 'finalizar'])->name('contrato.finalizar');
    Route::get('movreservareport/', [App\Http\Controllers\MovreservaController::class, 'report'])->name('movreserva.reporte.index');
    Route::get('parcialesalquiler/{id}', [App\Http\Controllers\CompromisoPagoController::class, 'historial'])->name('contrato.compromiso_pago.parciales');

Route::post('/plantilla-contrato/vista-previa', [App\Http\Controllers\ContratoController::class, 'generarVistaPrevia'])->name('contrato.generarVistaPrevia');
Route::get('/conceptos-pago-por-tipo/{tipo}', [App\Http\Controllers\ConceptoPagoController::class, 'getPorTipo']);



    Route::get('/facturador/desactivar/{id}', [App\Http\Controllers\FacturadorController::class, 'desactivar'])->name('facturador.desactivar');
    Route::get('/facturador/activar/{id}', [App\Http\Controllers\FacturadorController::class, 'activar'])->name('facturador.activar');
    Route::get('sucursal/facturador/{sucursal_id}/', [App\Http\Controllers\FacturadorController::class, 'create'])->name('facturador.create');
    Route::get('sucursal/facturador/edit/{id}/{sucursal_id}/', [App\Http\Controllers\FacturadorController::class, 'edit'])->name('facturador.edit');

    Route::delete('movventa/{id}/anular', [App\Http\Controllers\MovventumController::class, 'anular'])->name('movventa.anular');
    Route::get('movcompracreate/{personaCreadaId?}', [App\Http\Controllers\MovcompraController::class, 'create'])->name('movcompra.create');
    Route::delete('movcompra/{id}/anular', [App\Http\Controllers\MovcompraController::class, 'anular'])->name('movcompra.anular');
    Route::get('movcompra/{id}/detalles', [App\Http\Controllers\MovcompraController::class, 'verDetalles'])->name('movcompra.verDetalles');
    Route::get('movventacreate/{personaCreadaId?}', [App\Http\Controllers\MovventumController::class, 'create'])->name('movventa.create');
    Route::get('movventacreate/{personaCreadaId?}', [App\Http\Controllers\MovventumController::class, 'create'])->name('movventa.create.index');
    Route::get('movventagenerarpdf/{id}', [App\Http\Controllers\MovventumController::class, 'generarPDF'])->name('movventa.generarPDF');
    Route::get('movventa/{id}/detalles', [App\Http\Controllers\MovventumController::class, 'verDetalles'])->name('movventa.verDetalles');
    Route::get('movventa/{id}/alojamiento', [App\Http\Controllers\MovventumController::class, 'verAlojamiento'])->name('movventa.alojamiento');

    Route::get('movventa/{id}/convertirAComprobante/{personaCreadaId?}', [App\Http\Controllers\MovventumController::class, 'convertirAComprobante'])->name('movventa.convertirAComprobante');
    Route::post('movventa/{id}/convertirstore', [App\Http\Controllers\MovventumController::class, 'convertirstore'])->name('movventa.convertirstore');

    Route::get('arqueoTicket/{turnocajaId}', [App\Http\Controllers\RelTurnocajaController::class, 'arqueoTicket'])->name('rel_turnocaja.arqueoTicket');
    Route::get('arqueoPDF/{turnocajaId}', [App\Http\Controllers\RelTurnocajaController::class, 'arqueoPDF'])->name('rel_turnocaja.arqueoPDF');
    Route::get('movventaimprimirComprobante/{id}', [App\Http\Controllers\MovventumController::class, 'imprimirComprobante'])->name('movventa.imprimirComprobante');
    Route::get('movventa/generarPDFreporte/{tipo?}/{turno?}/{fecha_inicio?}/{fecha_fin?}', [App\Http\Controllers\MovventumController::class, 'generarPDFVentas'])->name('comprobante.generarPDFreporteVenta');
    Route::get(
        'ventas/generarExcelReporte/{tipo?}/{turno?}/{fecha_inicio}/{fecha_fin}',
        [App\Http\Controllers\MovventumController::class, 'generarExcelVentas']
    )
        ->name('ventas.generarExcelReporte');
    Route::post('/generate-approval-code', [App\Http\Controllers\MovventumController::class, 'generarCodigoAprobacion']);
    Route::post('/movhabitacion/generate-approval-code', [App\Http\Controllers\MovhabitacionController::class, 'generarCodigoAprobacionAnulacion'])
        ->name('movhabitacion.generate-approval-code');

    Route::get('solicitudcancell_boletas', [App\Http\Controllers\SolicitudController::class, 'modalcancell_boletas'])->name('comprobante.cancell_boletas');
    Route::post('solicitud/cancellboletas', [App\Http\Controllers\SolicitudController::class, 'cancell_boletas'])->name('cancell_boletas.store');

    Route::get('solicitudcancell_facturas', [App\Http\Controllers\SolicitudController::class, 'modalcancell_facturas'])->name('comprobante.cancell_facturas');
    Route::post('solicitud/cancellfacturas', [App\Http\Controllers\SolicitudController::class, 'cancell_facturas'])->name('cancell_facturas.store');

    Route::get('solicitud_anuladas_boleta_detalle/{id}', [App\Http\Controllers\SolicitudController::class, 'anulacion_boleta_detalle'])->name('verBoletas');
    Route::get('solicitud_anuladas_factura_detalle/{id}', [App\Http\Controllers\SolicitudController::class, 'anulacion_factura_detalle'])->name('verFacturas');

    Route::get('solicitud_anuladas_boletas', [App\Http\Controllers\SolicitudController::class, 'anulacion_boletas'])->name('resumenboletas.index');
    Route::get('solicitud_anuladas_facturas', [App\Http\Controllers\SolicitudController::class, 'anulacion_facturas'])->name('resumenfacturas.index');

    Route::get('solicitudsend_pending_boletas', [App\Http\Controllers\SolicitudController::class, 'send_pending_boletas'])->name('comprobante.send_pending_boletas');
    Route::get('solicitudsend_pending_facturas', [App\Http\Controllers\SolicitudController::class, 'send_pending_facturas'])->name('comprobante.send_pending_facturas');
    Route::get('solicitudsync', [App\Http\Controllers\SolicitudController::class, 'sync'])->name('comprobante.sync');
    Route::post('solicitud/enviarCorreo', [App\Http\Controllers\SolicitudController::class, 'enviarCorreo'])->name('enviarCorreo.cliente');
    Route::get('enviarCorreo/modal/{id}', [App\Http\Controllers\SolicitudController::class, 'enviarCorreoModal'])->name('comprobante.enviarCorreo');
    Route::post('solicitud/enviarWhatsApp', [App\Http\Controllers\SolicitudController::class, 'enviarWhatsApp'])->name('enviarWhatsApp.cliente');
    Route::get('enviarWhatsApp/modal/{id}', [App\Http\Controllers\SolicitudController::class, 'enviarWhatsAppModal'])->name('comprobante.enviarWhatsApp');
    Route::get('comprobantereporte', [App\Http\Controllers\SolicitudController::class, 'reporte'])->name('comprobante.reporte.index');

    Route::get('solicitud/{id}/download-xml', [App\Http\Controllers\SolicitudController::class, 'downloadXmlById'])->name('solicitud.downloadXml');
    Route::get('solicitud/{id}/download-cdr', [App\Http\Controllers\SolicitudController::class, 'downloadCdrById'])->name('solicitud.downloadCdr');
    Route::get('solicitud/generarPDF/{id}', [App\Http\Controllers\MovventumController::class, 'generarPDF'])->name('comprobante.generarPDF');
    Route::get('movventareport/', [App\Http\Controllers\MovventumController::class, 'report'])->name('movventa/report.index');

    Route::get('solicitud/generarPDFreporte/{tipo?}/{situacion?}/{fecha_inicio}/{fecha_fin}', [App\Http\Controllers\SolicitudController::class, 'generarPDF'])->name('comprobante.generarPDFreporte');
    Route::get('solicitud/generarExcelReporte/{tipo?}/{situacion?}/{fecha_inicio}/{fecha_fin}', [App\Http\Controllers\SolicitudController::class, 'generarExcel'])->name('comprobante.generarExcelReporte');
    Route::get('movhabitacion/exportarExcel/{habitacion}/{cliente}/{estado}/{fecha_inicio}/{fecha_fin}', [App\Http\Controllers\MovhabitacionController::class, 'exportarExcel'])->name('movhabitacion.exportarExcel');


    Route::get('movhabitacion/checkin/{id}/{personaCreadaId?}', [App\Http\Controllers\MovhabitacionController::class, 'checkin'])->name('movhabitacion.checkin');
    Route::get('movhabitacion/checkinedit/{habitacion_id}/{personaCreadaId?}', [App\Http\Controllers\MovhabitacionController::class, 'editCheckin'])->name('movhabitacion.editCheckin');
    Route::put('movhabitacion/updatecheckin/{id}', [App\Http\Controllers\MovhabitacionController::class, 'updatecheckin'])->name('movhabitacion.updatecheckin');

    Route::get('movhabitacion/checkout/{id}/{personaCreadaId?}', [App\Http\Controllers\MovhabitacionController::class, 'checkout'])->name('movhabitacion.checkout');
    Route::get('movhabitacion/ver/{id}/', [App\Http\Controllers\MovhabitacionController::class, 'ver'])->name('movhabitacion.ver');

    Route::get('movhabitacion/productos/{id}', [App\Http\Controllers\MovhabitacionController::class, 'productos'])->name('movhabitacion.productos');
    Route::get('movhabitacion/servicios/{id}', [App\Http\Controllers\MovhabitacionController::class, 'servicios'])->name('movhabitacion.servicios');
    Route::get('/movhabitacionvista-general', [App\Http\Controllers\MovhabitacionController::class, 'vistaGeneral'])->name('movhabitacion.vistaGeneral');
    Route::get('/movhabitacion/filtrar/{categoriaId}', [App\Http\Controllers\MovhabitacionController::class, 'filtrarPorCategoria'])->name('movhabitacion.filtrar');
    Route::get('/movhabitacion/filtrar-piso/{pisoId?}', [App\Http\Controllers\MovhabitacionController::class, 'filtrarPorPiso'])->name('movhabitacion.filtrarPiso');
    Route::get('/movhabitacion/filtrarEstado/{estado}', [App\Http\Controllers\MovhabitacionController::class, 'filtrarPorEstado'])->name('movhabitacion.filtrarEstado');
    Route::match(['get', 'delete'], 'movhabitacion/anular/{id}', [App\Http\Controllers\MovhabitacionController::class, 'anular'])->name('movhabitacion.anular');
    Route::get('movhabitacion/historial-ventas/{id}', [App\Http\Controllers\MovhabitacionController::class, 'historial'])->name('movhabitacion.historial-ventas');

    Route::get('checkouts/', [App\Http\Controllers\MovhabitacionController::class, 'checkouts'])->name('checkouts.index');
    Route::get('checkouts/productos/{movid}', [App\Http\Controllers\MovhabitacionController::class, 'checkoutsproductos'])->name('checkouts.productos');
    Route::get('checkouts/{id}/detalle-consumo-pdf', [App\Http\Controllers\MovhabitacionController::class, 'detalleConsumoPdf'])->name('checkouts.detalleConsumoPdf');
    Route::get('/checkouts/exportar-pdf/{habitacion}/{cliente}/{estado}/{fecha_inicio}/{fecha_fin}', [App\Http\Controllers\MovhabitacionController::class, 'exportarPDF'])->name('checkouts.exportarPDF');
    Route::get('/filter-checkouts', [App\Http\Controllers\MovhabitacionController::class, 'filter']);
    Route::get('/resumen-alojamientos/pdf', [App\Http\Controllers\MovhabitacionController::class, 'generarResumenPDF'])->name('mohabitacion.resumen_pdf');
    Route::get('/filter-huesped', [App\Http\Controllers\HuespedController::class, 'filter'])->name('huesped.filter');
    Route::get('alojamientos', [App\Http\Controllers\MovhabitacionController::class, 'alojamientos'])->name('movhabitacion.alojamientos.index');
    Route::get('/filter-by-situacion/{situacion}', [App\Http\Controllers\MovhabitacionController::class, 'filterBySituacion']);
    Route::get('/filter-habitaciones', [App\Http\Controllers\HabitacionController::class, 'filter']);
    Route::get('/filter-stockproductos', [App\Http\Controllers\StockproductoController::class, 'filter']);
    Route::get('/filter-ventas', [App\Http\Controllers\MovventumController::class, 'filter']);
    Route::get('/filter-reservas', [App\Http\Controllers\MovreservaController::class, 'filter']);
    Route::get('generarPDF/huesped', [App\Http\Controllers\HuespedController::class, 'generarPDFHuesped'])->name('huesped.generarPDF');
    Route::post('movhabitacion/{id}/process-checkout/{accion}', [App\Http\Controllers\MovhabitacionController::class, 'checkoutstore'])->name('movhabitacion.checkoutstore');
    Route::post('movhabitacion/storeProductos', [App\Http\Controllers\MovhabitacionController::class, 'storeProductos'])->name('movhabitacion.storeProductos');
    Route::get('/api/actualizar-comprobante/{tipoDocumentoId}', [App\Http\Controllers\MovhabitacionController::class, 'actualizarComprobante']);
    Route::get('/habitacion/dejar-limpiar/{id}/{opcion?}', [App\Http\Controllers\MovhabitacionController::class, 'dejarLimpiar'])->name('habitacion.dejarlimpiar');
    Route::get('/habitacion/limpiar/{id}/{opcion?}', [App\Http\Controllers\MovhabitacionController::class, 'Limpiar'])->name('habitacion.limpiar');
    Route::get('/habitacion/reservar/{id}', [App\Http\Controllers\MovhabitacionController::class, 'reservar'])->name('habitacion.reservar');
    Route::get('/habitacion/mantenimiento/{id}/{opcion?}', [App\Http\Controllers\MovhabitacionController::class, 'mantenimiento'])->name('habitacion.mantenimiento');
    Route::get('/calendario/reservas/', [App\Http\Controllers\MovhabitacionController::class, 'calendario'])->name('calendarioreservas.index');
    // Compatibility alias used by menu actions stored in existing databases.
    Route::get('/calendario/citas/', [App\Http\Controllers\MovhabitacionController::class, 'calendario'])->name('calendario-citas.index');
    Route::get('/verificar-estado-habitacion/{habitacionId}', [App\Http\Controllers\HabitacionController::class, 'verificarEstadoHabitacion']);


    Route::get('movcajaentrada/{cajaId}/{operacion}', [App\Http\Controllers\MovcajaController::class, 'create'])->name('movcaja.ingreso');
    Route::get('movcajasalida/{cajaId}/{operacion}', [App\Http\Controllers\MovcajaController::class, 'create'])->name('movcaja.egreso');
    Route::get('movcajacreate/{cajaId}/{operacion}', [App\Http\Controllers\MovcajaController::class, 'create'])->name('movcaja.create');
    Route::get('movcajaapertura/{cajaId}/{operacion}', [App\Http\Controllers\MovcajaController::class, 'create'])->name('movcaja.apertura');
    Route::get('movcajacierre/{cajaId}', [App\Http\Controllers\MovcajaController::class, 'cerrarCaja'])->name('movcaja.cierre');
    Route::post('/movcaja/cerrar/{cajaId}', [App\Http\Controllers\MovcajaController::class, 'storeCierre'])->name('movcaja.storeCierre');
    Route::post('movcaja/resumen/estado', [App\Http\Controllers\MovcajaController::class, 'setResumenEstado'])->name('movcaja.resumen.estado');
    Route::post('movcaja/apertura/sync', [App\Http\Controllers\MovcajaController::class, 'syncApertura'])->name('movcaja.apertura.sync');
    Route::delete('movcaja/{id}/anular', [App\Http\Controllers\MovcajaController::class, 'anular'])->name('movcaja.anular');
    Route::get('cuentas/cobrar', [App\Http\Controllers\CuentaCobrarpagarController::class, 'cobrar'])->name('cuentas.cobrar.index');
    Route::get('cuentas/pagar', [App\Http\Controllers\CuentaCobrarpagarController::class, 'pagar'])->name('cuentas.pagar.index');
    Route::get('cuentas/{cuenta}/pago', [App\Http\Controllers\CuentaCobrarpagarController::class, 'pago'])->name('cuentas.pago');
    Route::post('cuentas/{cuenta}/pago', [App\Http\Controllers\CuentaCobrarpagarController::class, 'guardarPago'])->name('cuentas.guardarPago');
    Route::get('cuentas/{tipo}/pdf', [App\Http\Controllers\CuentaCobrarpagarController::class, 'pdf'])->name('cuentas.pdf');
    Route::get('cuentas/{tipo}/excel', [App\Http\Controllers\CuentaCobrarpagarController::class, 'excel'])->name('cuentas.excel');
    Route::get('movcajaedit/{id}/{operacion}', [App\Http\Controllers\MovcajaController::class, 'edit'])->name('movcaja.edit');
    Route::get('/movcajadetalle/detallesmovimiento', [App\Http\Controllers\MovcajaController::class, 'verDetallesMovimiento'])->name('verDetalleMovimiento');
    Route::get('movcaja/{id}/detalles', [App\Http\Controllers\MovcajaController::class, 'verDetalles'])->name('movcaja.verDetalles');
    Route::get('/filter-by-caja/{cajaId}', [App\Http\Controllers\MovcajaController::class, 'filterByCaja']);
    Route::get('movcaja/generar-pdf/{cajaId}', [App\Http\Controllers\MovcajaController::class, 'generarPdfCierre'])->name('movcaja.generarPdfCierre');
    Route::get('movcaja/generar-pdf-ticket/{cajaId}', [App\Http\Controllers\MovcajaController::class, 'generarPdfCierreTicket'])->name('movcaja.generarPdfCierreTicket');
    Route::get('movcajareport/', [App\Http\Controllers\MovcajaController::class, 'report'])->name('movcaja/report.index');

    Route::get('/productos/filtrar/{categoria}/{clasificacion}', [App\Http\Controllers\ProductoController::class, 'filtrarPorCategoria']);
    Route::get('/productos/detalle/{id}', [App\Http\Controllers\ProductoController::class, 'detalle'])->name('productos.detalle');


    Route::get('movalmacenentrada', [App\Http\Controllers\MovalmacenController::class, 'entrada'])->name('movalmacen.entrada');
    Route::get('movalmacensalida', [App\Http\Controllers\MovalmacenController::class, 'salida'])->name('movalmacen.salida');
    Route::get('movalmacen/{id}/detalles', [App\Http\Controllers\MovalmacenController::class, 'verDetalles'])->name('movalmacen.verDetalles');
    Route::delete('movalmacen/{id}/anular', [App\Http\Controllers\MovalmacenController::class, 'anular'])->name('movalmacen.anular');

    Route::get('categoria/{id}/activar', [App\Http\Controllers\IgvController::class, 'activar'])->name('categoria.activar');
    Route::get('categoria/{id}/desactivar', [App\Http\Controllers\IgvController::class, 'desactivar'])->name('categoria.desactivar');
    Route::post('/categoria/subir/{id}', [App\Http\Controllers\MenuopcioneController::class, 'moveOrder'])->name('categoria.subir');
    Route::post('/categoria/bajar/{id}', [App\Http\Controllers\MenuopcioneController::class, 'moveOrder'])->name('categoria.bajar');



    Route::get('producto/print_barcodes', [App\Http\Controllers\MotivomovimientoController::class, 'print_barcodes'])->name('producto.print_barcodes');
    Route::get('producto/generate_barcodes', [App\Http\Controllers\MotivomovimientoController::class, 'generate_barcodes'])->name('producto.generate_barcodes');

    Route::get('rel_operacionsucursalperfil/{id}/activar', [App\Http\Controllers\PerfilsucursalController::class, 'activar'])->name('rel_operacionsucursalperfil.activar');
    Route::get('rel_operacionsucursalperfil/{id}/desactivar', [App\Http\Controllers\PerfilsucursalController::class, 'desactivar'])->name('rel_operacionsucursalperfil.desactivar');
    Route::get('/perfilsucursal/verPermisoUsuario/{id}', [App\Http\Controllers\PerfilsucursalController::class, 'verPermisoUsuario'])->name('verPermisoUsuario');
    Route::get('/perfilsucursal/verOperacionesPerfil/{id}', [App\Http\Controllers\PerfilsucursalController::class, 'verOperacionesPerfil'])->name('verOperacionesPerfil');
    Route::get('verSeccionesSucursalPerfil/{id}', [App\Http\Controllers\PerfilsucursalController::class, 'verSeccionesSucursalPerfil'])->name('perfilsucursal.verSecciones');
    Route::get('/seccionessucursalperfil/edit/{id}', [App\Http\Controllers\PerfilsucursalController::class, 'seccionsucursalperfiledit'])->name('rel_seccionsucursalperfil.edit');
    Route::put('/seccionessucursalperfil/update/{id}', [App\Http\Controllers\PerfilsucursalController::class, 'seccionsucursalperfilupdate'])->name('rel_seccionsucursalperfil.update');
    Route::get('finalizarmovhabitacion/{id}/activar', [App\Http\Controllers\MovhabitacionController::class, 'finalizarmovhabitacion'])->name('finalizarmovhabitacion');
    Route::get('reactivarmovhabitacion/{id}/activar', [App\Http\Controllers\MovhabitacionController::class, 'reactivarmovhabitacion'])->name('reactivarmovhabitacion');

    Route::get('finalizarmovreserva/{id}/activar', [App\Http\Controllers\MovhabitacionController::class, 'finalizarmovreserva'])->name('finalizarmovreserva');
    Route::get('cambiaraocupada/{id}', [App\Http\Controllers\HabitacionController::class, 'cambiaraocupada'])->name('cambiaraocupada');
    Route::get('cambiaradisponible/{id}', [App\Http\Controllers\HabitacionController::class, 'cambiaradisponible'])->name('cambiaradisponible');

    Route::get('permisousuario/{id}/activar', [App\Http\Controllers\PerfilsucursalController::class, 'activarPermiso'])->name('permisousuario.activar');
    Route::get('permisousuario/{id}/desactivar', [App\Http\Controllers\PerfilsucursalController::class, 'desactivarPermiso'])->name('permisousuario.desactivar');
    Route::get('permisousuario/{id}/importar', [App\Http\Controllers\PerfilsucursalController::class, 'import'])->name('permisousuario.import');
    Route::post('importarpermisosperfilsucursal/', [App\Http\Controllers\PerfilsucursalController::class, 'importar'])->name('importarpermisosperfilsucursal');

    Route::get('motivomovimiento/{id}/activar', [App\Http\Controllers\MotivomovimientoController::class, 'activar'])->name('motivomovimiento.activar');
    Route::get('motivomovimiento/{id}/desactivar', [App\Http\Controllers\MotivomovimientoController::class, 'desactivar'])->name('motivomovimiento.desactivar');

    Route::get('igv/{id}/activar', [App\Http\Controllers\IgvController::class, 'activar'])->name('igv.activar');
    Route::get('igv/{id}/desactivar', [App\Http\Controllers\IgvController::class, 'desactivar'])->name('igv.desactivar');

    Route::get('pasarelapago/{id}/activar', [App\Http\Controllers\PasarelapagoController::class, 'activar'])->name('pasarelapago.activar');
    Route::get('pasarelapago/{id}/desactivar', [App\Http\Controllers\PasarelapagoController::class, 'desactivar'])->name('pasarelapago.desactivar');

    Route::get('billeteradigital/{id}/activar', [App\Http\Controllers\BilleteradigitalController::class, 'activar'])->name('billeteradigital.activar');
    Route::get('billeteradigital/{id}/desactivar', [App\Http\Controllers\BilleteradigitalController::class, 'desactivar'])->name('billeteradigital.desactivar');

    Route::get('banco/{id}/activar', [App\Http\Controllers\BancoController::class, 'activar'])->name('banco.activar');
    Route::get('banco/{id}/desactivar', [App\Http\Controllers\BancoController::class, 'desactivar'])->name('banco.desactivar');

    Route::get('tarjeta/{id}/activar', [App\Http\Controllers\TarjetumController::class, 'activar'])->name('tarjeta.activar');
    Route::get('tarjeta/{id}/desactivar', [App\Http\Controllers\TarjetumController::class, 'desactivar'])->name('tarjeta.desactivar');

    Route::get('mediopago/{id}/activar', [App\Http\Controllers\MediopagoController::class, 'activar'])->name('mediopago.activar');
    Route::get('mediopago/{id}/desactivar', [App\Http\Controllers\MediopagoController::class, 'desactivar'])->name('mediopago.desactivar');
    Route::get('/listarmediopago/{idpasarela}', [App\Http\Controllers\MediopagoController::class, 'listarMedioPago'])->name('listarmediopago');

    Route::get('/miempresa', [App\Http\Controllers\EmpresaController::class, 'miempresa'])->name('miempresa.index');
    Route::resource('rel_parametrosucursal', App\Http\Controllers\RelParametrosucursalController::class);

    Route::get('rel_parametrosucursalcreate/{sucursal_id}', [App\Http\Controllers\RelParametrosucursalController::class, 'create'])->name('rel_parametrosucursal.create');
    Route::delete('rel_parametrosucursal/destroy/{id}/{sucursal_id}', [App\Http\Controllers\RelParametrosucursalController::class, 'destroy'])->name('rel_parametrosucursal.destroy');
    Route::get('rel_parametrosucursalsetup/', [App\Http\Controllers\RelParametrosucursalController::class, 'setup'])->name('rel_parametromisucursal/setup.index');
    Route::put('/rel-parametrosucursal/update', [App\Http\Controllers\RelParametrosucursalController::class, 'updateParametros'])
        ->name('rel_parametrosucursal.updateParametros');

    Route::get('rel_parametrosucursal/import/{sucursal_id}', [App\Http\Controllers\RelParametrosucursalController::class, 'import'])->name('rel_parametrosucursal.import');
    Route::post('importarparametrosucursal/', [App\Http\Controllers\RelParametrosucursalController::class, 'importar'])->name('importarparametrosucursal');

    Route::delete('rel_parametroempresa/destroy/{id}/{empresa_id}', [App\Http\Controllers\RelParametroempresaController::class, 'destroy'])->name('rel_parametroempresa.destroy');
    Route::get('rel_parametroempresa/import/{empresa_id}', [App\Http\Controllers\RelParametroempresaController::class, 'import'])->name('rel_parametroempresa.import');
    Route::post('importarparametroempresa/', [App\Http\Controllers\RelParametroempresaController::class, 'importar'])->name('importarparametroempresa');
    Route::get('rel_parametroempresa/create/{empresa_id}', [App\Http\Controllers\RelParametroempresaController::class, 'create'])->name('rel_parametroempresa.create');
    Route::get('rel_parametroempresa/index/{empresa_id}', [App\Http\Controllers\RelParametroempresaController::class, 'index'])->name('rel_parametroempresa.index');

    Route::put('/rel_tablasucursal/update/{id}', [App\Http\Controllers\RelTablasucursalController::class, 'update'])->name('rel_tablasucursal.update');
    Route::get('/rel_tablasucursal/edit/{id}', [App\Http\Controllers\RelTablasucursalController::class, 'edit'])->name('rel_tablasucursal.edit');
    Route::get('rel_tablasucursal/create/{sucursal_id}', [App\Http\Controllers\RelTablasucursalController::class, 'create'])->name('rel_tablasucursal.create');
    Route::delete('rel_tablasucursal/destroy/{id}/{sucursal_id}', [App\Http\Controllers\RelTablasucursalController::class, 'destroy'])->name('rel_tablasucursal.destroy');
    Route::get('rel_tablasucursal/index/{sucursal_id}', [App\Http\Controllers\RelTablasucursalController::class, 'index'])->name('rel_tablasucursal.index');
    Route::get('/rel_tablasucursal/verParametros/{id}', [App\Http\Controllers\RelTablasucursalController::class, 'verSucursalOperaciones'])->name('rel_tablasucursal.verOperaciones');
    Route::get('/rel_tablasucursal/activar/{id}', [App\Http\Controllers\RelTablasucursalController::class, 'activar'])->name('rel_operacionsucursal.activar');
    Route::get('/rel_tablasucursal/desactivar/{id}', [App\Http\Controllers\RelTablasucursalController::class, 'desactivar'])->name('rel_operacionsucursal.desactivar');

    Route::post('/rel_tablasucursal/move/{id}/{direction}', [App\Http\Controllers\RelTablasucursalController::class, 'moveOrderOperacion'])->name('moveOrderOperacion');
    // Rutas para el módulo RelCamposucursal
    Route::get('/rel_camposucursal/verCampos/{id}', [App\Http\Controllers\RelCamposucursalController::class, 'verCampos'])->name('rel_tablasucursal.verCampos');

    // Ruta para mover el orden de los campos
    Route::post('/rel_camposucursal/move/{id}/{direction}', [App\Http\Controllers\RelCamposucursalController::class, 'moveOrderCampo'])->name('moveOrderCampo');

    // Ruta para activar un campo
    Route::get('/rel_camposucursal/activar/{id}', [App\Http\Controllers\RelCamposucursalController::class, 'activar'])->name('rel_camposucursal.activar');

    // Ruta para desactivar un campo
    Route::get('/rel_camposucursal/desactivar/{id}', [App\Http\Controllers\RelCamposucursalController::class, 'desactivar'])->name('rel_camposucursal.desactivar');

    Route::get('stockproducto/generarPDFreporte/{categoria?}/{tipo?}/{situacion?}', [App\Http\Controllers\StockproductoController::class, 'generarPDFStock'])->name('stockproducto.generarPDFreporte');



    Route::post('/categoriaparametrossubir/{id}/{direction}', [App\Http\Controllers\CategoriaparametroController::class, 'moveOrder'])->name('categoriaparametros.subir');
    Route::post('/categoriaparametrosbajar/{id}/{direction}', [App\Http\Controllers\CategoriaparametroController::class, 'moveOrder'])->name('categoriaparametros.bajar');




    // Rutas de activar y desactivar
    Route::get('secciones/{id}/activar', [SeccioneController::class, 'activar'])->name('secciones.activar');
    Route::get('secciones/{id}/desactivar', [SeccioneController::class, 'desactivar'])->name('secciones.desactivar');



    Route::delete('sucursal/{id}/{empresa_id}', [App\Http\Controllers\SucursalController::class, 'destroy'])->name('sucursal.destroy');
    Route::get('sucursal/create/{empresa_id}', [App\Http\Controllers\SucursalController::class, 'create'])->name('sucursal.create');

    // Route::get('persona/index/{sucursal_id}', [App\Http\Controllers\PersonaController::class, 'index'])->name('persona.index');
    Route::get('persona/create/{sucursal_id}/{accion}/{habitacion_id?}', [App\Http\Controllers\PersonaController::class, 'create'])->name('persona.create');
    Route::get('cliente/create/{sucursal_id}/{accion}/{habitacion_id?}', [App\Http\Controllers\PersonaController::class, 'clientecreate'])->name('cliente.create');

    Route::get('personaedit/{id}/{accion}', [App\Http\Controllers\PersonaController::class, 'edit'])->name('persona.edit');

    Route::get('persona/restablecerPassword/{persona_id}/{sucursal_id}', [App\Http\Controllers\PersonaController::class, 'restablecerPassword'])->name('persona.restablecerPassword');
    Route::get('persona/verUsuario/{persona_id}/{sucursal_id}', [App\Http\Controllers\PersonaController::class, 'verUsuario'])->name('persona.verUsuario');
    Route::put('persona/verUsuario/{persona_id}/{sucursal_id}', [App\Http\Controllers\PersonaController::class, 'actualizarUsuarioPerfil'])->name('persona.verUsuario.update');
    Route::post('/persona/nuevacontraseña/{persona_id}', [App\Http\Controllers\PersonaController::class, 'nuevacontraseña'])->name('persona.nuevacontraseña');
    Route::get('/restablecerPasswordUsuarioLogueado', [App\Http\Controllers\PersonaController::class, 'restablecerPasswordUsuarioLogueado'])->name('restablecerPasswordUsuarioLogueado');
    Route::post('/nuevacontraseñaUsuarioLogueado', [App\Http\Controllers\PersonaController::class, 'nuevacontraseñaUsuarioLogueado'])->name('nuevacontraseñaUsuarioLogueado');
    Route::get('/get-opciones-menu/{perfilId}/{sucursalId}', [App\Http\Controllers\PermisousuarioController::class, 'getOpcionesMenu']);
    Route::get('/autocomplete/personas', [AutocompleteController::class, 'personas'])->name('autocomplete.personas');
    Route::get('/autocomplete/productos', [AutocompleteController::class, 'productos'])->name('autocomplete.productos');
    Route::get('/autocomplete/menuopciones', [AutocompleteController::class, 'menuopciones'])->name('autocomplete.menuopciones');
    Route::get('/autocomplete/solicitudes', [AutocompleteController::class, 'solicitudes'])->name('autocomplete.solicitudes');
    Route::get('/autocomplete/habitaciones', [AutocompleteController::class, 'habitaciones'])->name('autocomplete.habitaciones');
    Route::get('/autocomplete/mediopagos', [AutocompleteController::class, 'mediopagos'])->name('autocomplete.mediopagos');


    Route::get('/sucursal/desactivar/{id}', [App\Http\Controllers\SucursalController::class, 'desactivar'])->name('sucursal.desactivar');
    Route::get('/sucursal/activar/{id}', [App\Http\Controllers\SucursalController::class, 'activar'])->name('sucursal.activar');
    // Route::get('/sucursal/importarConfiguracionStock/{id}', [App\Http\Controllers\SucursalController::class, 'importarConfiguracionStock'])->name('sucursal.importarConfiguracionStock');
    Route::post('/sucursal/importarProductos/{id}', [App\Http\Controllers\SucursalController::class, 'importarConfiguracionStock'])->name('sucursal.importarProductos');
    Route::get('/importProductos/{sucursal_id}', [App\Http\Controllers\SucursalController::class, 'importProductos'])->name('sucursal.importProductos');
    Route::get('/sucursal/transferirProductos/{sucursal_id}', [App\Http\Controllers\SucursalController::class, 'transferirProductos'])->name('sucursal.transferirProductos');
    Route::get('/sucursal/productosTransferencia/{sucursal_id}', [App\Http\Controllers\SucursalController::class, 'productosParaTransferencia'])->name('sucursal.productosTransferencia');
    Route::post('/sucursal/transferirProductos/{sucursal_id}', [App\Http\Controllers\SucursalController::class, 'guardarTransferenciaProductos'])->name('sucursal.guardarTransferenciaProductos');
    Route::delete('/sucursallimpiar/{id}', [App\Http\Controllers\SucursalController::class, 'limpiar'])->name('sucursal.limpiar');

    Route::get('/sucursal/facturacion/{id}', [App\Http\Controllers\SucursalController::class, 'verFacturacion'])->name('sucursal.facturacion');
    // Route::get('/sucursal/importarProveedores/{id}', [App\Http\Controllers\SucursalController::class, 'importarProveedores'])->name('sucursal.importarProveedores');
    Route::get('/sucursal/verSecciones/{id}', [App\Http\Controllers\SucursalController::class, 'verSecciones'])->name('sucursal.verSecciones');
    Route::get('/sucursal/verTablas/{id}', [App\Http\Controllers\SucursalController::class, 'verTablas'])->name('sucursal.verTablas');
    Route::get('/sucursal/verParametros/{id}', [App\Http\Controllers\SucursalController::class, 'verParametros'])->name('sucursal.verParametros');
    Route::get('/sucursal/verPersonal/{id}', [App\Http\Controllers\SucursalController::class, 'verPersonalSucursal'])->name('sucursal.verPersonal');
    Route::get('/sucursal/verPerfiles/{id}', [App\Http\Controllers\SucursalController::class, 'verPerfilSucursal'])->name('sucursal.verPerfiles');


    Route::get('/rel_seccionsucursal/activar/{id}', [App\Http\Controllers\RelSeccionsucursalController::class, 'activar'])->name('rel_seccionsucursal.activar');
    Route::get('/rel_seccionsucursal/desactivar/{id}', [App\Http\Controllers\RelSeccionsucursalController::class, 'desactivar'])->name('rel_seccionsucursal.desactivar');



    Route::post('/sucursal/importarStockDeProductos/{id}', [App\Http\Controllers\SucursalController::class, 'importarProveedores'])->name('sucursal.importarStockDeProductos');

    Route::post('/menuopciones/move/{id}/{direction}', [App\Http\Controllers\MenuopcioneController::class, 'moveOrder'])->name('moveOrder');
    Route::get('/verSucursal/{empresa}', [App\Http\Controllers\EmpresaController::class, 'verSucursal'])->name('verSucursal');
    Route::get('/verMisSucursales/{empresa}', [App\Http\Controllers\EmpresaController::class, 'verMisSucursales'])->name('verMisSucursales');

    Route::get('/verParametros/{id}', [App\Http\Controllers\EmpresaController::class, 'verParametrosEmpresa'])->name('empresas.verParametros');

    Route::get('encuesta/{id}/desactivar', [App\Http\Controllers\EncuestumController::class, 'desactivar'])->name('encuesta.desactivar');
    Route::get('encuesta/{id}/activar', [App\Http\Controllers\EncuestumController::class, 'activar'])->name('encuesta.activar');
    Route::get('encuesta/{id}/finalizar', [App\Http\Controllers\EncuestumController::class, 'finalizar'])->name('encuesta.finalizar');
    Route::get('encuesta/{id}/preguntas', [App\Http\Controllers\EncuestumController::class, 'verPreguntas'])->name('encuesta.preguntas');
    Route::get('encuesta/{id}/respuestas', [App\Http\Controllers\EncuestumController::class, 'verRespuestas'])->name('encuesta.respuestas');
    Route::get('encuesta/{id}/copiar', [App\Http\Controllers\EncuestumController::class, 'copiar'])->name('encuesta.copiar');
    Route::get('encuesta/{id}/estadisticas', [App\Http\Controllers\EncuestumController::class, 'estadisticas'])->name('encuesta.estadisticas');
    Route::get('respuesta/{id}/detalles', [App\Http\Controllers\EncuestumController::class, 'detalles'])->name('respuesta.detalle');
    Route::get('detencuesta/create/{encuesta_id}/', [App\Http\Controllers\DetEncuestumController::class, 'create'])->name('pregunta.create');
    // web.php
    Route::get('/solicitud/{id}/cambiar-estado', [App\Http\Controllers\SolicitudController::class, 'cambiarEstado'])->name('solicitud.cambiarEstado');


    Route::get('categoriapregunta/{id}/desactivar', [App\Http\Controllers\CategoriapreguntumController::class, 'desactivar'])->name('categoriapregunta.desactivar');
    Route::get('categoriapregunta/{id}/activar', [App\Http\Controllers\CategoriapreguntumController::class, 'activar'])->name('categoriapregunta.activar');

    // Ruta para obtener provincias por departamento
    Route::get('get-provincias/{departamento_id}', [App\Http\Controllers\SucursalController::class, 'getProvincias']);
    Route::get('get-distritos/{provincia_id}', [App\Http\Controllers\SucursalController::class, 'getDistritos']);

    Route::get('/api/secciones/{id}/opciones-menu', [App\Http\Controllers\RelSeccionopcionmenuController::class, 'getMenu']);
    Route::get('/business_intelligence', [App\Http\Controllers\SucursalController::class, 'business_intelligence'])->name('business_intelligence.index');

    Route::get('kardex-resumen-pdf', [App\Http\Controllers\KardexController::class, 'generarResumenPDF'])->name('kardex.resumen_pdf');


    #Reportes
    Route::get('report/movventa', [App\Http\Controllers\ReportController::class, 'movventa'])->name('report.movventa.index');
    Route::post('report/movventa/generar', [App\Http\Controllers\ReportController::class, 'movventaGenerar'])->name('report.movventa.generar');
    Route::get('report/movcaja', [App\Http\Controllers\ReportController::class, 'movcaja'])->name('report.movcaja.index');

    #Dashboard
    Route::get('dashboard/movventa', [App\Http\Controllers\ReportController::class, 'dashmovventa'])->name('dashventa.index');
    Route::get('dashboard/movcaja', [App\Http\Controllers\ReportController::class, 'dashmovcaja'])->name('dashcaja.index');
    Route::get('dashboard/movhabitacion', [App\Http\Controllers\ReportController::class, 'dashmovhabitacion'])->name('dashalojamiento.index');

    Route::get('/habitaciones/{id}/evolucion', [App\Http\Controllers\ReportController::class,  'evolucionHabitacion']);
    Route::get('/habitaciones/{id}/alojamientos', [App\Http\Controllers\ReportController::class, 'alojamientosHabitacion'])->name('habitacion.alojamientos');

});


require __DIR__ . '/auth.php';
