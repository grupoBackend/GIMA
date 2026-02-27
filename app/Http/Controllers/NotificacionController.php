<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificacionController extends Controller
{
    // Listar notificaciones con paginación (mejor si llegan a tener muchas)
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->paginate(15);
        return response()->json($notifications);
    }

    // Solo las no leídas
    public function unread(Request $request): JsonResponse
    {
        return response()->json($request->user()->unreadNotifications);
    }

    // Marcar una como leída
    public function read($id, Request $request): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notificación marcada como leída']);
    }

    // NUEVO: Marcar todas como leídas (muy útil para el usuario)
    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Todas las notificaciones marcadas como leídas']);
    }
}