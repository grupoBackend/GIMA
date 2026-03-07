<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use App\Http\Controllers\Controller;
use App\Models\RepuestoUsado;
use App\Http\Resources\RepuestoUsadoResource; // Importar el Resource
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


/**
 * @OA\Tag(
 *     name="Mantenimiento - RepuestosUsados",
 *     description="Registros de uso de repuestos en sesiones"
 * )
 * @OA\Schema(
 *     schema="RepuestoUsado",
 *     type="object",
 *     title="RepuestoUsado",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="sesion_id", type="integer"),
 *     @OA\Property(property="repuesto_id", type="integer"),
 *     @OA\Property(property="cantidad", type="number"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */

class RepuestoUsadoController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/mantenimiento/repuestos-usados",
     *     summary="Listar usos de repuestos",
     *     tags={"Mantenimiento - RepuestosUsados"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de usos", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/RepuestoUsado")))
     * )
     */
    /**
     * Listar consumos usando el Resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RepuestoUsado::with(['sesion', 'repuesto']);

        // Filtro: Historial de un repuesto específico
        $query->when($request->repuesto_id, fn($q, $v) => $q->where('repuesto_id', $v));

        // Filtro: Repuestos usados en una sesión específica
        $query->when($request->sesion_id, fn($q, $v) => $q->where('sesion_id', $v));

        return RepuestoUsadoResource::collection($query->get());
    }

    /**
     * @OA\Post(
     *     path="/api/mantenimiento/repuestos-usados",
     *     summary="Registrar uso de repuesto",
     *     tags={"Mantenimiento - RepuestosUsados"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="sesion_id", type="integer"),
     *         @OA\Property(property="repuesto_id", type="integer"),
     *         @OA\Property(property="cantidad", type="number"),
     *         @OA\Property(property="costo_total", type="number", nullable=true)
     *     )),
     *     @OA\Response(response=201, description="Uso registrado", @OA\JsonContent(ref="#/components/schemas/RepuestoUsado")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sesion_id'   => 'required|exists:sesiones_mantenimiento,id',
            'repuesto_id' => 'required|exists:repuestos,id',
            'cantidad'    => 'required|numeric|min:0.01',
            'costo_total' => 'nullable|numeric|min:0',
        ]);

        $uso = RepuestoUsado::create($validated);

        return (new RepuestoUsadoResource($uso->load(['repuesto'])))
            ->additional(['message' => 'Repuesto registrado en la sesión']);
    }

    /**
     * @OA\Get(
     *     path="/api/mantenimiento/repuestos-usados/{id}",
     *     summary="Ver uso de repuesto",
     *     tags={"Mantenimiento - RepuestosUsados"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Uso", @OA\JsonContent(ref="#/components/schemas/RepuestoUsado")),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(RepuestoUsado $repuestoUsado): RepuestoUsadoResource
    {
        return new RepuestoUsadoResource($repuestoUsado->load(['sesion', 'repuesto']));
    }

    /**
     * @OA\Put(
     *     path="/api/mantenimiento/repuestos-usados/{id}",
     *     summary="Actualizar uso de repuesto",
     *     tags={"Mantenimiento - RepuestosUsados"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="cantidad", type="number", nullable=true),
     *         @OA\Property(property="costo_total", type="number", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Uso actualizado", @OA\JsonContent(ref="#/components/schemas/RepuestoUsado")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, RepuestoUsado $repuestoUsado)
    {
        $validated = $request->validate([
            'cantidad'    => 'sometimes|numeric|min:0.01',
            'costo_total' => 'sometimes|numeric|min:0',
        ]);

        $repuestoUsado->update($validated);

        return (new RepuestoUsadoResource($repuestoUsado))
            ->additional(['message' => 'Registro de repuesto actualizado']);
    }

    /**
     * @OA\Delete(
     *     path="/api/mantenimiento/repuestos-usados/{id}",
     *     summary="Eliminar registro de uso de repuesto",
     *     tags={"Mantenimiento - RepuestosUsados"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(RepuestoUsado $repuestoUsado)
    {
        $repuestoUsado->delete();
        return response()->noContent();
    }
}
