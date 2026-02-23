<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\HistorialLogs;
use Illuminate\Support\Facades\Auth;

class HistorialLogsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300 && Auth::check()) {

            // 1. Traducimos el método HTTP a una acción humana
            $metodo = $request->method();
            $accionHumana = match ($metodo) {
                'GET'    => 'Leyendo/Consultando',
                'POST'   => 'Creando nuevo registro',
                'PUT', 'PATCH' => 'Editando/Actualizando',
                'DELETE' => 'Eliminando registro',
                default  => $metodo
            };

            // 2. Obtenemos el correo del usuario actual
            $userEmail = Auth::user()->email;

            HistorialLogs::create([
                'usuario_id'  => Auth::id(),
                'accion'      => "[$accionHumana] en " . $request->path(),
                'descripcion' => "El usuario ($userEmail) realizó la acción con los datos: " . $this->generarDescripcion($request),
                'fecha'       => now(),
            ]);
        }

        return $response;
    }

    private function generarDescripcion(Request $request)
    {
        $params = $request->except(['password', 'password_confirmation', '_token']);
        return json_encode($params);
    }
}
