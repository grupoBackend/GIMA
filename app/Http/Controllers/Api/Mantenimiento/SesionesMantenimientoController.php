<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use App\Http\Controllers\Controller;
use App\Models\SesionesMantenimiento;
use App\Http\Resources\SesionMantenimientoResource; // Importar el nuevo Resource
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SesionesMantenimientoController extends Controller
{
    /**
     * @OA\Tag(
     *     name="Mantenimiento - Sesiones",
     *     description="Sesiones de trabajo en mantenimientos"
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/mantenimiento/sesiones",
     *     summary="Listar sesiones de mantenimiento",
     *     tags={"Mantenimiento - Sesiones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de sesiones", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/SesionMantenimiento")))
     * )
     */
    /**
     * Listar sesiones usando el Resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $sesiones = SesionesMantenimiento::with(['mantenimiento', 'tecnico'])->get();
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
        return response()->noContent();
    }
}
