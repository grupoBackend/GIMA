<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// --- Imports de tus compañeros ---
use App\Http\Controllers\Api\Admin\DireccionController;
use App\Http\Controllers\Api\Admin\HistorialLogsController;
use App\Http\Controllers\Api\Admin\UbicacionController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Catalogo\ActivoController;
use App\Http\Controllers\Api\Catalogo\ArticuloController;
use App\Http\Controllers\Api\General\NotificacionController;
use App\Http\Controllers\Api\Mantenimiento\ReporteController;
use App\Http\Controllers\Api\Catalogo\MaterialArticuloController;
use App\Http\Controllers\Api\Mantenimiento\MantenimientoController;
use App\Http\Controllers\Api\Mantenimiento\RepuestoUsadoController;
use App\Http\Controllers\Api\Mantenimiento\SesionesMantenimientoController;
use App\Http\Controllers\Api\Inventario\ProveedorController;
use App\Http\Controllers\Api\Inventario\RepuestoController;
use App\Http\Controllers\Api\Mantenimiento\CalendarioMantenimientoController;
use App\Http\Controllers\Api\General\PerfilController;

//Controladores de  los Dashboard 
use App\Http\Controllers\Api\Dashboard\MainDashboardController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ----- Rutas Públicas ---
Route::prefix('autenticacion')->group(function () {
    Route::post('iniciar-sesion', [AuthController::class, 'login']);
    Route::post('registrar', [AuthController::class, 'register']);
    Route::post('recuperar-password', [AuthController::class, 'resetWithPin']); // Olvidé contraseña
    Route::post('cerrar-sesion', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// --- Rutas Protegidas ---
Route::middleware('auth:sanctum')->group(function () {

    // Route::post('/usuario/actualizar', [AuthController::class, 'updateSensitiveData']); // Cambiar datos desde perfil

    Route::get('autenticacion/perfil', [AuthController::class, 'perfil']);

    // ==========================================
    // ---  Dashboards ---
    // ==========================================

    // 1. Main Dashboard (Exclusivo para Admin y Supervisor)
    // Usamos el middleware de Spatie 'role' para bloquear el acceso a otros usuarios
    Route::middleware(['role:admin|supervisor'])->prefix('dashboard/main')->group(function () {

        Route::get('/estadisticas', [MainDashboardController::class, 'estadisticasGenerales']);
        Route::get('/activos-estado', [MainDashboardController::class, 'barraActivos']);
        Route::get('/agenda', [MainDashboardController::class, 'agendaProxima']);
    });

    // -- Modulo GIMA: Admin ---
    Route::prefix('admin')->group(function () {
        //Direcciones, Auditorias, Ubicaciones, Usuarios
        Route::apiResource('direcciones', DireccionController::class)
            ->parameters(['direcciones' => 'direccion']);

        Route::apiResource('auditorias', HistorialLogsController::class)
            ->parameters(['auditorias' => 'auditoria']);

        Route::apiResource('ubicaciones', UbicacionController::class)
            ->parameters(['ubicaciones' => 'ubicacion']);

        Route::apiResource('users', UserController::class);

        // Rutas adicionales para usuarios
        Route::patch('users/{id}/status', [UserController::class, 'changeStatus']);
        Route::patch('users/{id}/roles', [UserController::class, 'asignarRol']);
    });

    // --- Modulo: Mantenimiento ---
    Route::prefix('mantenimiento')->group(function () {
        Route::patch('sesiones/{sesion}/finalizar', [SesionesMantenimientoController::class, 'finalizar']);
        Route::patch('mantenimientos/{id}/asignar-tecnico', [MantenimientoController::class, 'asignarTecnico']);
        Route::patch('mantenimientos/{id}/estado', [MantenimientoController::class, 'cambiarEstado']);
        //-- Calendario, Reportes, Gestión, Sesiones, Repuestos Usados
        Route::apiResource('calendario', CalendarioMantenimientoController::class);

        Route::apiResource('reportes', ReporteController::class);

        Route::apiResource('mantenimientos', MantenimientoController::class);

        Route::apiResource('sesiones', SesionesMantenimientoController::class)
            ->parameters(['sesiones' => 'sesion']);
        Route::apiResource('repuestos-usados', RepuestoUsadoController::class)
            ->parameters(['repuestos-usados' => 'repuesto-usado']);
    });

    // ---Modulo: Catálogo ---
    Route::prefix('catalogo')->group(function () {
        //-- Artículos, Activos, Materiales Artículo
        Route::apiResource('articulos', ArticuloController::class)
            ->parameters(['articulos' => 'articulo']);

        //api/catalogo/activos/por-tipo
        Route::get('activos/por-categoria', [ActivoController::class, 'activosPorCategoria']);

        Route::apiResource('activos', ActivoController::class)
            ->parameters(['activos' => 'activo']);

        Route::apiResource('materiales-articulo', MaterialArticuloController::class)
            ->parameters(['materiales-articulo' => 'material_articulo']);
    });

    // --- General ---
    Route::prefix('general')->group(function () {

        // PERFIL
        Route::get('perfil', [PerfilController::class, 'show']);      // ver perfil
        Route::put('perfil', [PerfilController::class, 'update']);    // actualizar perfil
        Route::delete('perfil', [PerfilController::class, 'destroy']); // limpiar datos no esenciales

        Route::apiResource('notificaciones', NotificacionController::class)
            ->parameters(['notificaciones' => 'notificacion']);
    });

    // --- Modulo: Inventario ---
    Route::prefix('inventario')->group(function () {
        //-- Proveedores, Repuestos, Stock
        Route::apiResource('proveedores', ProveedorController::class)
            ->parameters(['proveedores' => 'proveedor']);

        Route::apiResource('repuestos', RepuestoController::class);

        Route::get('stock', [RepuestoController::class, 'indexStock']);
        Route::match(['put', 'patch'], 'stock/{id}', [RepuestoController::class, 'updateStock']);
    });
});
