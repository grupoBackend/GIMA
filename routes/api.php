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
use App\Http\Controllers\Api\Dashboard\TecnicoDashboardController;


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
    Route::middleware(['role:admin|supervisor'])->prefix('dashboard/main')->group(function () {

        Route::get('/estadisticas', [MainDashboardController::class, 'estadisticasGenerales']);
        Route::get('/activos-estado', [MainDashboardController::class, 'barraActivos']);
        Route::get('/agenda', [MainDashboardController::class, 'agendaProxima']);
    });

    // 2. Dashboard para Técnico
    Route::middleware(['role:tecnico'])->group(function () {

        Route::get('/dashboard/tecnico', [TecnicoDashboardController::class, 'index']);
    });

    // -- Modulo GIMA: Admin ---
    Route::prefix('admin')->group(function () {
        //Direcciones, His, Ubicaciones, Usuarios
        Route::apiResource('direcciones', DireccionController::class)
            ->parameters(['direcciones' => 'direccion']);

        Route::apiResource('historial-logs', HistorialLogsController::class)
            ->parameters(['historial-logs' => 'historial-log']);

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

        //Reportes - Encargado de tarea: Sebastian Rodriguez (Lider: Juan Longart - Haddan Valencia)
        Route::patch('reportes/{id}/estado', [ReporteController::class, 'updateEstado']);
        Route::patch('reportes/{id}/prioridad', [ReporteController::class, 'updatePrioridad']);
        Route::post('reportes/{id}/asignar-mantenimiento', [ReporteController::class, 'asignarMantenimiento']);
        Route::get('reportes/mios', [ReporteController::class, 'verMisReportes']);

        //Reportes (index, store, show, update, destroy)
        Route::apiResource('reportes', ReporteController::class);

        //-- Calendario, Reportes, Gestión, Sesiones, Repuestos Usados
        Route::apiResource('calendario', CalendarioMantenimientoController::class);

        // NUEVA RUTA: Acción específica para ejecutar un mantenimiento
        Route::post('calendario/{calendarioMantenimiento}/ejecutar', [CalendarioMantenimientoController::class, 'ejecutarProgramado']);

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
        Route::patch('activos/{activo}/status', [ActivoController::class, 'changeStatus']);

        Route::apiResource('activos', ActivoController::class)
            ->parameters(['activos' => 'activo']);

        Route::apiResource('materiales-articulo', MaterialArticuloController::class)
            ->parameters(['materiales-articulo' => 'material_articulo']);

        // Ruta para descargar material de artículo - Anthony Medina (Lider: Juan Longart - Haddan Valencia)
        Route::get('materiales-articulo/{id}/download', [MaterialArticuloController::class, 'download']);
    });


    // --- General ---
    Route::prefix('general')->group(function () {
        // --- Notificaciones --- //
        Route::get('/notificaciones', [NotificacionController::class, 'index']);
        Route::post('/notificaciones', [NotificacionController::class, 'store']);
        Route::get('/notificaciones/conteo', [NotificacionController::class, 'conteo']);
        Route::get('/notificaciones/{id}', [NotificacionController::class, 'show']);
        Route::post('/notificaciones/{id}/marcar-leida', [NotificacionController::class, 'marcarLeida']);
        Route::post('/notificaciones/marcar-todas-leidas', [NotificacionController::class, 'marcarTodasLeidas']);
        Route::delete('/notificaciones/{id}', [NotificacionController::class, 'destroy']);

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

        //indexStock eliminada, se maneja con el query param 'alerta_stock' en el index general
        //Route::get('stock', [RepuestoController::class, 'indexStock']);

        // RUTA MODIFICADA: Acción específica para modificar el stock
        Route::patch('repuestos/{repuesto}/stock', [RepuestoController::class, 'ajustarStock']);
    });

    // --- Modulo: Notificaciones ---
    Route::get('/notificaciones', [NotificacionController::class, 'index']);
    Route::get('/notificaciones/sin-leer', [NotificacionController::class, 'unread']);
    Route::post('/notificaciones/{id}/leer', [NotificacionController::class, 'read']);
    Route::post('/notificaciones/leer-todas', [NotificacionController::class, 'readAll']);
});