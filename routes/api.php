<?php

use App\Http\Controllers\AmenityController;
use App\Http\Controllers\ImagenesTipohabitacionController;
use App\Http\Controllers\SurveyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\SalonController;
use App\Http\Controllers\ReservaApiController;
use App\Http\Controllers\MovreservaPaqueteController;
use App\Http\Controllers\SalonApiController;
use App\Http\Controllers\Api\ErpIntegrationController;
use App\Http\Controllers\Api\DocumentLookupController;
use App\Http\Middleware\VerifyErpIntegrationSignature;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('document-lookup')->middleware('throttle:30,1')->group(function () {
    Route::get('/dni', [DocumentLookupController::class, 'dni']);
    Route::get('/ruc', [DocumentLookupController::class, 'ruc']);
});

Route::patch('/integrations/erp/branches/{branchId}/status', [ErpIntegrationController::class, 'setBranchStatus'])
    ->middleware(VerifyErpIntegrationSignature::class);

Route::prefix('/survey')->name('survey.')->controller(SurveyController::class)->middleware(['survey.api'])->group(function () {
    // Ruta para obtener los datos del cliente
    Route::get('/customer/{id}', 'getCustomerData')->name('get_customer');

    // Ruta para obtener los datos de la habitación
    Route::get('/checkRoomCode/{id}', 'getRoomData')->name('get_rooms');

    // Nueva ruta para obtener los datos de la encuesta
    Route::get('', 'getSurveyData')->name('get_survey');

    // Ruta para guardar los datos de la encuesta
    Route::post('/', 'storeSurveyData')->name('store_survey');

});


// Rutas públicas para habitaciones
Route::get('/habitacion/{sucursal_id}/{tipohabitacion_id?}', [HabitacionController::class, 'habitaciones']);
Route::get('/habitaciones/disponibles/{fecha}/{fechafin}/{sucursal_id}/{tipohabitacion_id?}', [HabitacionController::class, 'habitacionesDisponibles']);

Route::prefix('/habitacion')->group(function () {
    // Ruta para cargar una imagen de tipo habitación
    Route::post('/{tipohabitacionId}/imagenes', [ImagenesTipohabitacionController::class, 'storeImage']);
    
    // Ruta para obtener las imágenes de un tipo de habitación
    Route::get('/{tipohabitacionId}/imagenes', [ImagenesTipohabitacionController::class, 'getImages']);
});
Route::prefix('/tipohabitacion')->group(function () {
    // Ruta para obtener todos los tipos de habitaciones
    Route::get('/{sucursal_id}', [ImagenesTipohabitacionController::class, 'getAllTiposHabitacion']);
    
    // Ruta para obtener la información detallada de un tipo de habitación específico
    Route::get('/detalle/{id}', [ImagenesTipohabitacionController::class, 'getTipoHabitacionById']);
});
Route::prefix('/salon')->group(function () {
    Route::post('/store/{sucursal_id}', [SalonApiController::class, 'store']);
    Route::get('/{sucursal_id}', [SalonApiController::class, 'index']);
    Route::get('/{salon_id}/detalles', [SalonApiController::class, 'getDetalles']);

});
Route::prefix('/amenities')->group(function () {
    Route::post('/{tipohabitacionId}', [AmenityController::class, 'store']);
    Route::get('/tipo-habitacion/{tipohabitacionId}', [AmenityController::class, 'getByTipoHabitacion']);
    Route::get('/', [AmenityController::class, 'getAll']);
});


Route::prefix('reserva')->group(function () {
    Route::post('/habitacion', [ReservaApiController::class, 'store']);
    Route::get('/habitacion/{id}', [ReservaApiController::class, 'show']);

});



Route::prefix('reserva')->group(function () {
    Route::post('/paquete', [MovreservaPaqueteController::class, 'store']);
    Route::get('/paquete/{id}', [MovreservaPaqueteController::class, 'show']);
});

Route::prefix('/amenities')->group(function () {
    Route::post('/{tipohabitacionId}', [AmenityController::class, 'store']);
    Route::get('/tipo-habitacion/{tipohabitacionId}', [AmenityController::class, 'getByTipoHabitacion']);
    Route::get('/{sucursal_id}', [AmenityController::class, 'getAll']);
    Route::delete('/{amenityId}', [AmenityController::class, 'destroy']);

});
