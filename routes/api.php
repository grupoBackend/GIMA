<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\Admin\AuditoriaController;
use App\Http\Controllers\Api\Admin\DireccionController;
use App\Http\Controllers\Api\Admin\UbicacionController;
use App\Http\Controllers\Api\Catalogo\ActivoController;
use App\Http\Controllers\Api\Catalogo\ArticuloController;
use App\Http\Controllers\Api\General\NotificacionController;
use App\Http\Controllers\Api\Mantenimiento\ReporteController;
use App\Http\Controllers\Api\Catalogo\MaterialArticuloController;
use App\Http\Controllers\Api\Mantenimiento\MantenimientoController;
use App\Http\Controllers\Api\Mantenimiento\RepuestoUsadoController;
use App\Http\Controllers\Api\Mantenimiento\SesionesMantenimientoController;
use App\Http\Controllers\Api\Mantenimiento\CalendarioMantenimientoController;

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

    // -- Modulo GIMA: Admin ---
    Route::prefix('admin')->group(function () {
        Route::apiResource('direcciones', DireccionController::class)
            ->parameters(['direcciones' => 'direccion']);
        Route::apiResource('auditorias', AuditoriaController::class)
            ->parameters(['auditorias' => 'auditoria']);
        Route::apiResource('ubicaciones', UbicacionController::class)
            ->parameters(['ubicaciones' => 'ubicacion']);
    });

    // --- Módulo GIMA: Mantenimiento ---
    Route::prefix('mantenimiento')->group(function () {
        Route::apiResource('calendario', CalendarioMantenimientoController::class);
        Route::apiResource('reportes', ReporteController::class);
        Route::apiResource('gestion', MantenimientoController::class);
        Route::apiResource('sesiones', SesionesMantenimientoController::class)
            ->parameters(['sesiones' => 'sesion']);
        Route::apiResource('repuestos-usados', RepuestoUsadoController::class)
            ->parameters(['repuestos-usados' => 'repuesto-usado']);
    });
    // Agregamos el prefijo 'catalogo' para ordenar las rutas
    Route::prefix('catalogo')->group(function () {
        Route::apiResource('articulos', ArticuloController::class)
            ->parameters(['articulos' => 'articulo']);

        // Rutas para Activos
        Route::apiResource('activos', ActivoController::class)
            ->parameters(['activos' => 'activo']);

        // Rutas para MaterialArticulo
        Route::apiResource('materiales-articulo', MaterialArticuloController::class)
            ->parameters(['materiales-articulo' => 'material_articulo']);
    });

    Route::prefix('general')->group(function () {

        // Esto crea las rutas: /api/general/notificaciones
        Route::apiResource('notificaciones', NotificacionController::class)
            ->parameters(['notificaciones' => 'notificacion']);
    });
});
