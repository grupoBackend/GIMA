<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistorialLogs;
use App\Http\Resources\AuditoriaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="Administración - Auditoría",
 *     description="Endpoints para historial de logs / auditoría"
 * )
 */
class HistorialLogsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/auditoria",
     *     summary="Listar logs de auditoría",
     *     tags={"Administración - Auditoría"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de logs", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Auditoria")))
     * )
     */
    public function index(Request $request)
    {
        $logs = HistorialLogs::with('usuario')
            ->filtrar($request->all())
            ->recientes()
            ->paginate(20);

        return AuditoriaResource::collection($logs);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/auditoria",
     *     summary="Crear entrada de auditoría",
     *     tags={"Administración - Auditoría"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="usuario_id", type="integer"),
     *         @OA\Property(property="entidad", type="string"),
     *         @OA\Property(property="entidad_id", type="integer"),
     *         @OA\Property(property="accion", type="string"),
     *         @OA\Property(property="descripcion", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=201, description="Entrada creada", @OA\JsonContent(ref="#/components/schemas/Auditoria")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'entidad'    => 'required|string|max:100',
            'entidad_id' => 'required|integer',
            'accion'     => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'fecha'      => 'nullable|date',
        ]);

        $historialLogs = HistorialLogs::create($data);

        return (new AuditoriaResource($historialLogs))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/auditoria/{id}",
     *     summary="Ver entrada de auditoría",
     *     tags={"Administración - Auditoría"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Entrada encontrada", @OA\JsonContent(ref="#/components/schemas/Auditoria")),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     */
    public function show(HistorialLogs $historialLogs)
    {
        $historialLogs->load(['usuario']);
        return new AuditoriaResource($historialLogs);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/auditoria/{id}",
     *     summary="Actualizar entrada de auditoría",
     *     tags={"Administración - Auditoría"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="entidad", type="string"),
     *         @OA\Property(property="entidad_id", type="integer"),
     *         @OA\Property(property="accion", type="string"),
     *         @OA\Property(property="descripcion", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Entrada actualizada", @OA\JsonContent(ref="#/components/schemas/Auditoria")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, HistorialLogs $historialLogs)
    {
        $data = $request->validate([
            'usuario_id' => 'sometimes|required|exists:users,id',
            'entidad'    => 'sometimes|required|string|max:100',
            'entidad_id' => 'sometimes|required|integer',
            'accion'     => 'sometimes|required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'fecha'      => 'nullable|date',
        ]);

        $historialLogs->update($data);

        return new AuditoriaResource($historialLogs);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/auditoria/{id}",
     *     summary="Eliminar entrada de auditoría",
     *     tags={"Administración - Auditoría"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(HistorialLogs $historialLogs)
    {
        $historialLogs->delete();
        return response()->noContent();
    }
}
