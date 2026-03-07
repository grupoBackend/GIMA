<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Models\User; // Añadido para que User::find() funcione en el método store
use App\Http\Resources\NotificacionResource; 
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;

/**
 * @OA\Tag(
 * name="General - Notificaciones",
 * description="Endpoints para gestionar notificaciones"
 * )
 */
/**
 * @OA\Schema(
 * schema="Notificacion",
 * type="object",
 * title="Notificacion",
 * @OA\Property(property="id", type="string", format="uuid"),
 * @OA\Property(property="usuario_id", type="integer"),
 * @OA\Property(property="contenido", type="string"),
 * @OA\Property(property="leido", type="boolean", nullable=true),
 * @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 * @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */
class NotificacionController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/general/notificaciones",
     * summary="Listar todas las notificaciones del usuario autenticado",
     * tags={"General - Notificaciones"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Lista de notificaciones", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Notificacion")))
     * )
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
     * @OA\Post(
     * path="/api/general/notificaciones",
     * summary="Crear notificación manual",
     * tags={"General - Notificaciones"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(required=true, @OA\JsonContent(
     * @OA\Property(property="usuario_id", type="integer"),
     * @OA\Property(property="contenido", type="string")
     * )),
     * @OA\Response(response=201, description="Notificación creada", @OA\JsonContent(ref="#/components/schemas/Notificacion")),
     * @OA\Response(response=422, description="Error de validación")
     * )
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
        
        return (new NotificacionResource($notificacion))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     * path="/api/general/notificaciones/{id}",
     * summary="Ver notificación específica",
     * tags={"General - Notificaciones"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     * @OA\Response(response=200, description="Notificación encontrada", @OA\JsonContent(ref="#/components/schemas/Notificacion")),
     * @OA\Response(response=404, description="No encontrada")
     * )
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