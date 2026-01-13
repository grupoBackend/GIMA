<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\Admin\DireccionController;
use App\Http\Controllers\Api\Mantenimiento\CalendarioMantenimientoController;
use App\Http\Controllers\Api\Mantenimiento\MantenimientoController;
use App\Http\Controllers\Api\Mantenimiento\ReporteController;
use App\Http\Controllers\Api\Mantenimiento\RepuestoUsadoController;
use App\Http\Controllers\Api\Mantenimiento\SesionesMantenimientoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ----- Rutas Públicas (Cualquiera entra) ---
Route::prefix('autenticacion')->group(function () {
    Route::post('iniciar-sesion', [AuthController::class, 'login']);
    Route::post('registrar', [AuthController::class, 'register']);
});

// --- 2. Rutas Protegidas (Necesitas Token "Bearer") ---
// Para ingresar a estas rutas primero tienen que iniciar sesion, 
// luego les dara un token: xxxxxxxxxxxxxxxxxxx
// eso lo copian y lo pegan en Auth
Route::middleware('auth:sanctum')->group(function () {

    // Ver mis propios datos
    Route::get('autenticacion/perfil', [AuthController::class, 'perfil']);

    Route::resource('direcciones', DireccionController::class)
        ->parameters(['direcciones' => 'direccion']);

    // --- Módulo GIMA: Mantenimiento ---
    Route::prefix('mantenimiento')->group(function () {
        Route::apiResource('calendario', CalendarioMantenimientoController::class);
        Route::apiResource('reportes', ReporteController::class);
        Route::apiResource('gestion', MantenimientoController::class);
        Route::apiResource('sesiones', SesionesMantenimientoController::class);
        Route::apiResource('repuestos-usados', RepuestoUsadoController::class);
    });
});
