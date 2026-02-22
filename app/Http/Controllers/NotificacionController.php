<?php

// app/Http/Controllers/NotificacionController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    // Obtener todas las notificaciones (leídas y no leídas)
    public function index(Request $request)
    {
        return response()->json($request->user()->notifications);
    }

    // Obtener solo las NO leídas (para el contador del icono de campana)
    public function unread(Request $request)
    {
        return response()->json($request->user()->unreadNotifications);
    }

    // Marcar una notificación como leída
    public function read($id, Request $request)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notificación leída']);
    }
}