<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Http\Resources\NotificacionResource; // <--- Importamos el Resource
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="General - Notificaciones",
 *     description="Endpoints para gestionar notificaciones"
 * )
 */
class NotificacionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/general/notificaciones",
     *     summary="Listar notificaciones",
     *     tags={"General - Notificaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de notificaciones", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Notificacion")))
     * )
     */
    public function index()
    {
        // Traemos todas las notificaciones
        $notificaciones = Notificacion::all();

        // Las devolvemos formateadas
        return NotificacionResource::collection($notificaciones);
    }

    /**
     * @OA\Post(
     *     path="/api/general/notificaciones",
     *     summary="Crear notificación",
     *     tags={"General - Notificaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="usuario_id", type="integer"),
     *         @OA\Property(property="contenido", type="string")
     *     )),
     *     @OA\Response(response=201, description="Notificación creada", @OA\JsonContent(ref="#/components/schemas/Notificacion")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
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
        return (new NotificacionResource($notificacion))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/general/notificaciones/{id}",
     *     summary="Ver notificación",
     *     tags={"General - Notificaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Notificación encontrada", @OA\JsonContent(ref="#/components/schemas/Notificacion")),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     */
    public function show(Notificacion $notificacion)
    {
        return new NotificacionResource($notificacion);
    }
}
