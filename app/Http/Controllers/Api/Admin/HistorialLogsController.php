<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistorialLogs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\HistorialLogsResource;

/**
 * @OA\Tag(
 *     name="Administración - HistorialLogs",
 *     description="Registros de actividad y auditoría"
 * )
 * @OA\Schema(
 *     schema="HistorialLogs",
 *     type="object",
 *     title="HistorialLogs",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="usuario_id", type="integer"),
 *     @OA\Property(property="entidad", type="string"),
 *     @OA\Property(property="entidad_id", type="integer"),
 *     @OA\Property(property="accion", type="string"),
 *     @OA\Property(property="descripcion", type="string", nullable=true),
 *     @OA\Property(property="fecha", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */
class HistorialLogsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/historial-logs",
     *     summary="Listar historial de logs",
     *     tags={"Administración - HistorialLogs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="fecha_inicio", in="query", required=false, @OA\Schema(type="string", format="date"), description="Fecha inicio para rango"),
     *     @OA\Parameter(name="fecha_fin", in="query", required=false, @OA\Schema(type="string", format="date"), description="Fecha fin para rango"),
     *     @OA\Parameter(name="accion", in="query", required=false, @OA\Schema(type="string"), description="Filtrar por acción"),
     *     @OA\Parameter(name="rol", in="query", required=false, @OA\Schema(type="string"), description="Filtrar por rol del usuario"),
     *     @OA\Response(response=200, description="Lista de logs", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/HistorialLogs")))
     * )
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $logs = HistorialLogs::with('user')
            ->when(
                $request->fecha_inicio && $request->fecha_fin,
                fn($q) => $q->rangoFechas($request->fecha_inicio, $request->fecha_fin)
            )
            ->when($request->accion, fn($q, $v) => $q->where('accion', $v))
            ->when($request->rol, fn($q, $v) => $q->porRol($v))
            ->recientes()
            ->paginate(20);

        return HistorialLogsResource::collection($logs);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/historial-logs",
     *     summary="Crear registro de historial",
     *     tags={"Administración - HistorialLogs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="usuario_id", type="integer"),
     *         @OA\Property(property="entidad", type="string"),
     *         @OA\Property(property="entidad_id", type="integer"),
     *         @OA\Property(property="accion", type="string"),
     *         @OA\Property(property="descripcion", type="string", nullable=true),
     *         @OA\Property(property="fecha", type="string", format="date-time", nullable=true)
     *     )),
     *     @OA\Response(response=201, description="Registro creado", @OA\JsonContent(ref="#/components/schemas/HistorialLogs")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     * Store a newly created resource in storage.
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

        return new HistorialLogsResource($historialLogs);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/historial-logs/{id}",
     *     summary="Ver registro de historial",
     *     tags={"Administración - HistorialLogs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Registro", @OA\JsonContent(ref="#/components/schemas/HistorialLogs")),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     * Display the specified resource.
     */
    public function show(HistorialLogs $historialLogs)
    {
        $historialLogs->load(['user']);

        return new HistorialLogsResource($historialLogs);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/historial-logs/{id}",
     *     summary="Actualizar registro de historial",
     *     tags={"Administración - HistorialLogs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="usuario_id", type="integer", nullable=true),
     *         @OA\Property(property="entidad", type="string", nullable=true),
     *         @OA\Property(property="entidad_id", type="integer", nullable=true),
     *         @OA\Property(property="accion", type="string", nullable=true),
     *         @OA\Property(property="descripcion", type="string", nullable=true),
     *         @OA\Property(property="fecha", type="string", format="date-time", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Registro actualizado", @OA\JsonContent(ref="#/components/schemas/HistorialLogs")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     * Update the specified resource in storage.
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

        return new HistorialLogsResource($historialLogs);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/historial-logs/{id}",
     *     summary="Eliminar registro de historial",
     *     tags={"Administración - HistorialLogs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     * Remove the specified resource from storage.
     */
    public function destroy(HistorialLogs $historialLogs)
    {
        $historialLogs->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
