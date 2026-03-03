<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use App\Http\Resources\ReporteResource; // 1. IMPORTAR EL NUEVO RESOURCE
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use App\Enums\EstadoReporte;
use App\Enums\NivelPrioridad;

/**
 * @OA\Tag(
 *     name="Mantenimiento - Reportes",
 *     description="Gestión de reportes"
 * )
 */
/**
 * @OA\Schema(
 *     schema="Reporte",
 *     type="object",
 *     title="Reporte",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="activo_id", type="integer"),
 *     @OA\Property(property="descripcion", type="string"),
 *     @OA\Property(property="prioridad", type="string"),
 *     @OA\Property(property="estado", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */


class ReporteController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/mantenimiento/reportes",
     *     summary="Listar reportes",
     *     tags={"Mantenimiento - Reportes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de reportes", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Reporte")))
     * )
     */
    /**
     * Listar reportes usando el Resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $reportes = Reporte::with(['usuario', 'activo'])->get();
        return ReporteResource::collection($reportes);
    }

    /**
     * @OA\Post(
     *     path="/api/mantenimiento/reportes",
     *     summary="Crear reporte",
     *     tags={"Mantenimiento - Reportes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="activo_id", type="integer"),
     *         @OA\Property(property="descripcion", type="string"),
     *         @OA\Property(property="prioridad", type="string"),
     *         @OA\Property(property="estado", type="string")
     *     )),
     *     @OA\Response(response=201, description="Reporte creado", @OA\JsonContent(ref="#/components/schemas/Reporte")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'activo_id'   => 'required|exists:activos,id',
            'descripcion' => 'required|string|min:10',
            'prioridad'   => ['required', Rule::enum(NivelPrioridad::class)],
            'estado'      => ['required', Rule::enum(EstadoReporte::class)],
        ]);

        $reporte = Reporte::create([
            ...$validated,
            'usuario_id' => $request->user()->id,
        ]);

        return (new ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Reporte creado exitosamente']);
    }

    /**
     * @OA\Get(
     *     path="/api/mantenimiento/reportes/{id}",
     *     summary="Ver reporte",
     *     tags={"Mantenimiento - Reportes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Reporte", @OA\JsonContent(ref="#/components/schemas/Reporte")),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Reporte $reporte): ReporteResource
    {
        return new ReporteResource($reporte->load(['usuario', 'activo', 'mantenimientos']));
    }

    /**
     * @OA\Put(
     *     path="/api/mantenimiento/reportes/{id}",
     *     summary="Actualizar reporte",
     *     tags={"Mantenimiento - Reportes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="descripcion", type="string", nullable=true),
     *         @OA\Property(property="prioridad", type="string", nullable=true),
     *         @OA\Property(property="estado", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Reporte actualizado", @OA\JsonContent(ref="#/components/schemas/Reporte")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, Reporte $reporte)
    {
        $validated = $request->validate([
            'activo_id'   => 'sometimes|exists:activos,id',
            'descripcion' => 'sometimes|string|min:10',
            'prioridad'   => ['sometimes', Rule::enum(NivelPrioridad::class)],
            'estado'      => ['sometimes', Rule::enum(EstadoReporte::class)],
        ]);

        $reporte->update($validated);

        return (new ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Reporte actualizado']);
    }

    /**
     * @OA\Delete(
     *     path="/api/mantenimiento/reportes/{id}",
     *     summary="Eliminar reporte",
     *     tags={"Mantenimiento - Reportes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(Reporte $reporte)
    {
        $reporte->delete();
        return response()->noContent();
    }
}
