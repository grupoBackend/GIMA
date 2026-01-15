<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Http\Resources\NotificacionResource; // <--- Importamos el Resource
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Listar todas las notificaciones
     */
    public function index()
    {
        // Traemos todas las notificaciones
        $notificaciones = Notificacion::all();
        
        // Las devolvemos formateadas
        return NotificacionResource::collection($notificaciones);
    }

    /**
     * Crear una nueva notificación
     */
    public function store(Request $request)
    {
        // 1. Validamos que nos manden el ID del usuario y el contenido
        $datosValidados = $request->validate([
            'usuario_id' => 'required|exists:users,id', // Verifica que el usuario exista
            'contenido'  => 'required|string|max:500',  // Límite de texto prudente
        ]);

        // 2. Creamos la notificación en BD
        $notificacion = Notificacion::create($datosValidados);

        // 3. Devolvemos el objeto creado con código 201
        return new NotificacionResource($notificacion);
    }

    /**
     * Ver una notificación específica
     */
    public function show(Notificacion $notificacion)
    {
        return new NotificacionResource($notificacion);
    }

    
}
