<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\TipoMantenimiento;
use Illuminate\Http\JsonResponse;
use App\Enums\EstadoMantenimiento;
use App\Http\Controllers\Controller;
use App\Models\CalendarioMantenimiento;
use App\Http\Resources\CalendarioMantenimientoResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CalendarioMantenimientoController extends Controller
{
    /**
     * @OA\Tag(
     *     name="Mantenimiento - Calendario",
     *     description="Eventos programados y calendario de mantenimiento"
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/mantenimiento/calendario",
     *     summary="Listar eventos programados",
     *     tags={"Mantenimiento - Calendario"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de eventos", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/CalendarioMantenimiento")))
     * )
     */
    /**
     * Listar todos los eventos programados.
     */
    public function index(): AnonymousResourceCollection
    {
        $eventos = CalendarioMantenimiento::with(['activo', 'tecnicoAsignado'])->get();
        // Usamos el Resource para la colección
        return CalendarioMantenimientoResource::collection($eventos);
    }

    /**
     * @OA\Get(
     *     path="/api/mantenimiento/calendario/{id}",
     *     summary="Ver evento de calendario",
     *     tags={"Mantenimiento - Calendario"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Evento", @OA\JsonContent(ref="#/components/schemas/CalendarioMantenimiento")),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(CalendarioMantenimiento $calendarioMantenimiento): CalendarioMantenimientoResource
    {
        // Cargamos relaciones y devolvemos el Resource
        return new CalendarioMantenimientoResource($calendarioMantenimiento->load(['activo', 'tecnicoAsignado']));
    }

    /**
     * @OA\Post(
     *     path="/api/mantenimiento/calendario",
     *     summary="Crear evento de calendario",
     *     tags={"Mantenimiento - Calendario"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="activo_id", type="integer"),
     *         @OA\Property(property="tecnico_asignado_id", type="integer"),
     *         @OA\Property(property="tipo", type="string"),
     *         @OA\Property(property="fecha_programada", type="string", format="date"),
     *         @OA\Property(property="estado", type="string")
     *     )),
     *     @OA\Response(response=201, description="Evento creado", @OA\JsonContent(ref="#/components/schemas/CalendarioMantenimiento")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'activo_id'           => 'required|exists:activos,id',
            'tecnico_asignado_id' => 'required|exists:users,id',
            'tipo'                => ['required', Rule::enum(TipoMantenimiento::class)],
            'fecha_programada'    => 'required|date|after_or_equal:today',
            'estado'              => ['required', Rule::enum(EstadoMantenimiento::class)],
        ]);

        $evento = CalendarioMantenimiento::create($validated);

        return (new CalendarioMantenimientoResource($evento->load(['activo', 'tecnicoAsignado'])))
            ->additional(['message' => 'Evento programado exitosamente']);
    }

    /**
     * @OA\Put(
     *     path="/api/mantenimiento/calendario/{id}",
     *     summary="Actualizar evento de calendario",
     *     tags={"Mantenimiento - Calendario"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="tipo", type="string", nullable=true),
     *         @OA\Property(property="fecha_programada", type="string", format="date", nullable=true),
     *         @OA\Property(property="estado", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Evento actualizado", @OA\JsonContent(ref="#/components/schemas/CalendarioMantenimiento")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, CalendarioMantenimiento $calendarioMantenimiento)
    {
        $validated = $request->validate([
            'activo_id'           => 'sometimes|exists:activos,id',
            'tecnico_asignado_id' => 'sometimes|exists:users,id',
            'tipo'                => ['sometimes', Rule::enum(TipoMantenimiento::class)],
            'fecha_programada'    => 'sometimes|date',
            'estado'              => ['sometimes', Rule::enum(EstadoMantenimiento::class)],
        ]);

        $calendarioMantenimiento->update($validated);

        return (new CalendarioMantenimientoResource($calendarioMantenimiento->load(['activo', 'tecnicoAsignado'])))
            ->additional(['message' => 'Evento actualizado correctamente']);
    }


    /**
     * @OA\Delete(
     *     path="/api/mantenimiento/calendario/{id}",
     *     summary="Eliminar evento de calendario",
     *     tags={"Mantenimiento - Calendario"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(CalendarioMantenimiento $calendarioMantenimiento): JsonResponse
    {
        $calendarioMantenimiento->delete();
        return response()->json([
            'message' => 'Evento eliminado del calendario con éxito'
        ]);
    }
}
