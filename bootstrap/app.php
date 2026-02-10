<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Auth\AuthenticationException;
use Spatie\Permission\Middleware\RoleMiddleware as Role;
use Spatie\Permission\Middleware\PermissionMiddleware as Permission;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware as RoleOrPermission;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
->withMiddleware(function (Middleware $middleware): void {
        
        // 1. Registramos los alias de Spatie (lo que ya tenías)
        $middleware->alias([
            'role' => Role::class,
            'permission' => Permission::class,
            'role_or_permission' => RoleOrPermission::class,
        ]);
        // 2. Registramos los Logs para la API
        // Usamos 'append' para que se ejecute DESPUÉS de que el usuario esté autenticado
        $middleware->api(append: [
            \App\Http\Middleware\HistorialLogsMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 1. Manejo de error cuando Spatie bloquea (403 Forbidden)
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => 'No tienes los permisos (roles) necesarios para esta acción.',
                    'rol_requerido' => $e->getRequiredRoles(), // Útil para debugging
                ], 403);
            }
        });

        // 2. Manejo de error cuando no hay Token o es inválido (401 Unauthorized)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => 'No estás autenticado. Token faltante o inválido.'
                ], 401);
            }
        });
    })->create();
