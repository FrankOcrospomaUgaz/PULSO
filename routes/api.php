<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalonController;
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

Route::prefix('/salon')->group(function () {
    Route::post('/store/{sucursal_id}', [SalonApiController::class, 'store']);
    Route::get('/{sucursal_id}', [SalonApiController::class, 'index']);
    Route::get('/{salon_id}/detalles', [SalonApiController::class, 'getDetalles']);

});
