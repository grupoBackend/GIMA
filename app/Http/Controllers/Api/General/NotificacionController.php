<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Http\Resources\NotificacionResource; 
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificacionController extends Controller
{
    /**
     * Listar todas las notificaciones del usuario autenticado
     */
    public function index(Request $request)
    {
        $usuario = $request->user();
        
        // Opcional: filtros por query params
        $filtro = $request->get('filtro', 'todas'); // 'todas', 'no_leidas', 'leidas'
        
        $notificacionesQuery = $usuario->notifications();
        
        if ($filtro === 'no_leidas') {
            $notificacionesQuery = $usuario->unreadNotifications();
        } elseif ($filtro === 'leidas') {
            $notificacionesQuery = $usuario->notifications()->whereNotNull('read_at');
        }
        
        $notificaciones = $notificacionesQuery->paginate(15);
        
        return NotificacionResource::collection($notificaciones);
    }

    /**
     * Crear una nueva notificación (para pruebas o notificaciones manuales)
     * NOTA: En un sistema real, las notificaciones se crean automáticamente
     * desde otros controladores usando Notification::send()
     */
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'contenido'  => 'required|string|max:500',
        ]);

        $usuario = User::find($datosValidados['usuario_id']);
        
        // Crear una notificación "ad-hoc" usando el sistema de Laravel
        $usuario->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => $request->get('tipo', 'App\\Notifications\\NotificacionManual'),
            'data' => [
                'message' => $datosValidados['contenido'],
                'contenido' => $datosValidados['contenido'], // para compatibilidad
            ],
        ]);

        // Obtener la notificación recién creada para devolverla
        $notificacion = $usuario->notifications()->latest()->first();
        
        return new NotificacionResource($notificacion);
    }

    /**
     * Ver una notificación específica y marcarla como leída
     */
    public function show(Request $request, string $id)
    {
        $usuario = $request->user();
        
        // Buscar la notificación del usuario
        $notificacion = $usuario->notifications()->findOrFail($id);
        
        // Opcional: marcar como leída al verla
        if ($request->get('marcar_leida', true)) {
            $notificacion->markAsRead();
        }
        
        return new NotificacionResource($notificacion);
    }

    /**
     * Marcar una notificación como leída
     */
    public function marcarLeida(Request $request, string $id)
    {
        $usuario = $request->user();
        $notificacion = $usuario->notifications()->findOrFail($id);
        
        $notificacion->markAsRead();
        
        return response()->json([
            'message' => 'Notificación marcada como leída',
            'notificacion' => new NotificacionResource($notificacion)
        ]);
    }

    /**
     * Marcar TODAS las notificaciones como leídas
     */
    public function marcarTodasLeidas(Request $request)
    {
        $usuario = $request->user();
        $usuario->unreadNotifications->markAsRead();
        
        return response()->json([
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    /**
     * Eliminar una notificación
     */
    public function destroy(Request $request, string $id)
    {
        $usuario = $request->user();
        $notificacion = $usuario->notifications()->findOrFail($id);
        
        $notificacion->delete();
        
        return response()->json([
            'message' => 'Notificación eliminada'
        ]);
    }

    /**
     * Obtener conteo de notificaciones no leídas
     */
    public function conteo(Request $request)
    {
        $usuario = $request->user();
        
        return response()->json([
            'no_leidas' => $usuario->unreadNotifications->count(),
            'total' => $usuario->notifications()->count(),
        ]);
    }
}
