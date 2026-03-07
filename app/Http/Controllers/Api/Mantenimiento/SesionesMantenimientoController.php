<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use App\Http\Controllers\Controller;
use App\Models\SesionesMantenimiento;
use App\Http\Resources\SesionMantenimientoResource; // Importar el nuevo Resource
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Mantenimiento - Sesiones",
 *     description="Endpoints para sesiones de mantenimiento"
 * )
 * @OA\Schema(
 *     schema="SesionMantenimiento",
 *     type="object",
 *     title="SesionMantenimiento",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="mantenimiento_id", type="integer"),
 *     @OA\Property(property="tecnico_id", type="integer"),
 *     @OA\Property(property="fecha", type="string", format="date-time"),
 *     @OA\Property(property="horas_trabajadas", type="number"),
 *     @OA\Property(property="descripcion_trabajo", type="string"),
 *     @OA\Property(property="observaciones", type="string", nullable=true),
 *     @OA\Property(property="costo_hora", type="number", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */
class SesionesMantenimientoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/mantenimiento/sesiones",
     *     summary="Listar sesiones de mantenimiento",
     *     tags={"Mantenimiento - Sesiones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="mantenimiento_id", in="query", required=false, @OA\Schema(type="integer"), description="Filtrar por mantenimiento_id"),
     *     @OA\Parameter(name="fecha_inicio", in="query", required=false, @OA\Schema(type="string", format="date"), description="Fecha inicio para rango"),
     *     @OA\Parameter(name="fecha_fin", in="query", required=false, @OA\Schema(type="string", format="date"), description="Fecha fin para rango"),
     *     @OA\Response(response=200, description="Lista de sesiones", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/SesionMantenimiento")))
     * )
     */
    public function index(Request $request): AnonymousResourceCollection // <--- SE INYECTA REQUEST
    {
        $sesiones = SesionesMantenimiento::with(['mantenimiento', 'tecnico'])
            // 1. Filtro por Mantenimiento (Sustituye a getByMantenimiento)
            ->when($request->mantenimiento_id, fn($q, $v) => $q->where('mantenimiento_id', $v))

            // 2. Filtro por Rango de Fechas
            ->when($request->fecha_inicio && $request->fecha_fin, function ($q) use ($request) {
                $q->rangoFechas($request->fecha_inicio, $request->fecha_fin);
            })
            ->get(); // Si prefieren paginación, cambia get() por paginate(15)

        return SesionMantenimientoResource::collection($sesiones);
    }

    /**
     * @OA\Post(
     *     path="/api/mantenimiento/sesiones",
     *     summary="Registrar sesión de mantenimiento",
     *     tags={"Mantenimiento - Sesiones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="mantenimiento_id", type="integer"),
     *         @OA\Property(property="tecnico_id", type="integer"),
     *         @OA\Property(property="fecha", type="string", format="date", nullable=true),
     *         @OA\Property(property="horas_trabajadas", type="number"),
     *         @OA\Property(property="descripcion_trabajo", type="string"),
     *         @OA\Property(property="observaciones", type="string", nullable=true),
     *     )),
     *     @OA\Response(response=201, description="Sesión registrada", @OA\JsonContent(ref="#/components/schemas/SesionMantenimiento")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mantenimiento_id'    => 'required|exists:mantenimientos,id',
            'tecnico_id'          => 'required|exists:users,id',
            'fecha'               => 'nullable|date',
            'horas_trabajadas'    => 'required|numeric|min:0.1',
            'descripcion_trabajo' => 'required|string|min:5',
            'observaciones'       => 'nullable|string',
            'costo_hora'         => 'nullable|numeric|min:0',
        ]);

        // Si no mandan fecha, ponemos la actual
        if (!isset($validated['fecha'])) {
            $validated['fecha'] = now();
        }

        $sesion = SesionesMantenimiento::create($validated);

        return (new SesionMantenimientoResource($sesion->load(['mantenimiento', 'tecnico'])))
            ->additional(['message' => 'Sesión de trabajo registrada']);
    }

    /**
     * @OA\Get(
     *     path="/api/mantenimiento/sesiones/{id}",
     *     summary="Ver sesión de mantenimiento",
     *     tags={"Mantenimiento - Sesiones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Sesión", @OA\JsonContent(ref="#/components/schemas/SesionMantenimiento")),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     */
    public function show(SesionesMantenimiento $sesion): SesionMantenimientoResource
    {
        return new SesionMantenimientoResource(
            $sesion->load(['mantenimiento', 'tecnico', 'repuestosUtilizados.repuesto'])
        );
    }

    /**
     * @OA\Put(
     *     path="/api/mantenimiento/sesiones/{id}",
     *     summary="Actualizar sesión",
     *     tags={"Mantenimiento - Sesiones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="fecha", type="string", format="date", nullable=true),
     *         @OA\Property(property="horas_trabajadas", type="number", nullable=true),
     *         @OA\Property(property="descripcion_trabajo", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Sesión actualizada", @OA\JsonContent(ref="#/components/schemas/SesionMantenimiento")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, SesionesMantenimiento $sesion)
    {
        $validated = $request->validate([
            'fecha'               => 'sometimes|date',
            'horas_trabajadas'    => 'sometimes|numeric|min:0.1',
            'descripcion_trabajo' => 'sometimes|string',
            'observaciones'       => 'nullable|string',
            'costo_hora'         => 'sometimes|numeric',
        ]);

        $sesion->update($validated);

        return (new SesionMantenimientoResource($sesion))
            ->additional(['message' => 'Sesión actualizada correctamente']);
    }

    /**
     * @OA\Delete(
     *     path="/api/mantenimiento/sesiones/{id}",
     *     summary="Eliminar sesión",
     *     tags={"Mantenimiento - Sesiones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(SesionesMantenimiento $sesion)
    {
        $sesion->delete();
        return response()->json(['message' => 'Sesión eliminada']);
    }
    // --- NUEVO MÉTODO FASE 3 ---

    /**
     * @OA\Patch(
     *     path="/api/mantenimiento/sesiones/{id}/finalizar",
     *     summary="Finalizar una sesión de mantenimiento",
     *     tags={"Mantenimiento - Sesiones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Sesión finalizada", @OA\JsonContent(ref="#/components/schemas/SesionMantenimiento")),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     */
    public function finalizar(Request $request, SesionesMantenimiento $sesion)
    {
        return (new SesionMantenimientoResource($sesion))
            ->additional(['message' => 'Sesión finalizada con éxito']);
    }
}
