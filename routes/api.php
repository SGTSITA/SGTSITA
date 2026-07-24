<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/dashboard/operacion-activa', [App\Http\Controllers\ApiValidationController::class, 'getOperacionActiva']);
    Route::post('/dashboard/finalizar-viaje', [App\Http\Controllers\ApiValidationController::class, 'finalizarViaje']);
    Route::post('/dashboard/info-viaje', [App\Http\Controllers\ApiValidationController::class, 'infoViaje']);
    Route::get('/dashboard/cotizaciones', [App\Http\Controllers\ApiValidationController::class, 'getCotizaciones']);
    Route::get('/dashboard/viajes', [App\Http\Controllers\ApiValidationController::class, 'getViajes']);
    Route::get('/dashboard/contenedores', [App\Http\Controllers\ApiValidationController::class, 'getContenedores']);
    Route::get('/dashboard/monitoreo', [App\Http\Controllers\ApiValidationController::class, 'getMonitoreo']);
    Route::get('/dashboard/planeacion', [App\Http\Controllers\ApiValidationController::class, 'getPlaneacion']);
    Route::get('/dashboard/reportes', [App\Http\Controllers\ApiValidationController::class, 'getReportes']);
    Route::get('/dashboard/empresas-propias', [App\Http\Controllers\ApiValidationController::class, 'getEmpresasPropias']);
    Route::get('/dashboard/catalogos-programar-viaje', [App\Http\Controllers\ApiValidationController::class, 'getCatalogsProgramarViaje']);
    Route::post('/dashboard/programar-viaje', [App\Http\Controllers\ApiValidationController::class, 'programarViajeMobile']);
    Route::post('/dashboard/anular-planeacion', [App\Http\Controllers\ApiValidationController::class, 'anularPlaneacionMobile']);
    Route::post('/dashboard/finalizar-viaje', [App\Http\Controllers\ApiValidationController::class, 'finalizarViajeMobile']);
    Route::get('/dashboard/bancos', [App\Http\Controllers\ApiValidationController::class, 'getBancosMobile']);
    Route::get('/dashboard/bancos/{id}/reporte', [App\Http\Controllers\ApiValidationController::class, 'exportarReporteBancoMobile']);
    Route::post('/dashboard/reportes/generar', [App\Http\Controllers\ApiValidationController::class, 'generarReporteMobile']);
});

Route::get('/api/coordenadas/subclientes/{clienteId}', [App\Http\Controllers\CoordenadasController::class, 'getSubclientes']);
Route::get('/api/coordenadas/entidadesPC', [App\Http\Controllers\CoordenadasController::class, 'getEntidadesPC']);

// SGT Validation and Login APIs
Route::post('/login', [App\Http\Controllers\ApiValidationController::class, 'login']);
Route::post('/validate-operador', [App\Http\Controllers\ApiValidationController::class, 'validateOperador']);
Route::get('/dashboard/reportes/descargar', [App\Http\Controllers\ApiValidationController::class, 'descargarReporteMobile']);
Route::get('/dashboard/bancos/{id}/reporte-descargar', [App\Http\Controllers\ApiValidationController::class, 'descargarReporteBancoMobile']);
Route::post('/operador/coordenadas', [App\Http\Controllers\ApiValidationController::class, 'guardarCoordenadas']);
Route::post('/operador/iniciar-viaje', [App\Http\Controllers\ApiValidationController::class, 'iniciarViaje']);
Route::post('/operador/finalizar-viaje', [App\Http\Controllers\ApiValidationController::class, 'finalizarViajeOperador']);
Route::post('/operador/estatus-flujo', [App\Http\Controllers\ApiValidationController::class, 'obtenerEstatusFlujo']);