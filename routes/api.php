<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// --- Imports de tus compañeros ---
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
use App\Http\Controllers\Api\Catalogo\ArticuloController;
use App\Http\Controllers\Api\Catalogo\ActivoController;
use App\Http\Controllers\Api\Catalogo\MaterialArticuloController;
use App\Http\Controllers\Api\General\NotificacionController;
use App\Http\Controllers\Api\Admin\UbicacionController;

use App\Http\Controllers\Api\Inventario\ProveedorController;
use App\Http\Controllers\Api\Inventario\RepuestoController; 
use App\Http\Controllers\Api\Mantenimiento\CalendarioMantenimientoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ----- Rutas Públicas ---
Route::prefix('autenticacion')->group(function () {
    Route::post('iniciar-sesion', [AuthController::class, 'login']);
    Route::post('registrar', [AuthController::class, 'register']);
});

// --- Rutas Protegidas ---
Route::middleware('auth:sanctum')->group(function () {

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

    // --- Mantenimiento ---
    Route::prefix('mantenimiento')->group(function () {
        Route::apiResource('calendario', CalendarioMantenimientoController::class);
        Route::apiResource('reportes', ReporteController::class);
        Route::apiResource('gestion', MantenimientoController::class);
        Route::apiResource('sesiones', SesionesMantenimientoController::class)
            ->parameters(['sesiones' => 'sesion']);
        Route::apiResource('repuestos-usados', RepuestoUsadoController::class)
            ->parameters(['repuestos-usados' => 'repuesto-usado']);
    });

    // --- Catálogo ---
    Route::prefix('catalogo')->group(function () {
        Route::apiResource('articulos', ArticuloController::class)
            ->parameters(['articulos' => 'articulo']);
        Route::apiResource('activos', ActivoController::class)
            ->parameters(['activos' => 'activo']);
        Route::apiResource('materiales-articulo',MaterialArticuloController::class)
            ->parameters(['materiales-articulo' => 'materiales_articulo']);
    });
    
    // --- General ---
    Route::prefix('general')->group(function () {
        Route::apiResource('notificaciones', NotificacionController::class)
            ->parameters(['notificaciones' => 'notificacion']);
    });

    // ==========================================
    // MÓDULO: INVENTARIO (V2) ---
    // ==========================================
    Route::prefix('inventario')->group(function () {
        Route::apiResource('proveedores', ProveedorController::class);
        Route::apiResource('repuestos', RepuestoController::class);
        
        Route::get('stock', [RepuestoController::class, 'indexStock']);
        Route::match(['put', 'patch'], 'stock/{id}', [RepuestoController::class, 'updateStock']);
    });

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
