<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direccion;
use App\Http\Resources\DireccionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="Administración - Direcciones",
 *     description="Endpoints para gestión de direcciones"
 * )
 * @OA\Schema(
 *     schema="Direccion",
 *     type="object",
 *     title="Dirección",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="estado", type="string"),
 *     @OA\Property(property="ciudad", type="string"),
 *     @OA\Property(property="sector", type="string", nullable=true),
 *     @OA\Property(property="calle", type="string", nullable=true),
 *     @OA\Property(property="sede", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class DireccionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/direcciones",
     *     summary="Listar direcciones",
     *     tags={"Administración - Direcciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de direcciones", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Direccion")))
     * )
     */
    public function index()
    {
        $direcciones = Direccion::with(['ubicaciones', 'repuestos'])->paginate(15);
        return DireccionResource::collection($direcciones);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/direcciones",
     *     summary="Crear dirección",
     *     tags={"Administración - Direcciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="estado", type="string"),
     *         @OA\Property(property="ciudad", type="string"),
     *         @OA\Property(property="sector", type="string", nullable=true),
     *         @OA\Property(property="calle", type="string", nullable=true),
     *         @OA\Property(property="sede", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=201, description="Dirección creada", @OA\JsonContent(ref="#/components/schemas/Direccion")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'estado' => 'required|string|max:100',
            'ciudad' => 'required|string|max:100',
            'sector' => 'nullable|string|max:100',
            'calle'  => 'nullable|string|max:255',
            'sede'   => 'nullable|string|max:100',
        ]);

        $direccion = Direccion::create($data);

        return (new DireccionResource($direccion))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/direcciones/{id}",
     *     summary="Ver dirección",
     *     tags={"Administración - Direcciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Dirección encontrada", @OA\JsonContent(ref="#/components/schemas/Direccion")),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     */
    public function show(Direccion $direccion)
    {
        $direccion->load(['ubicaciones', 'repuestos']);
        return new DireccionResource($direccion);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/direcciones/{id}",
     *     summary="Actualizar dirección",
     *     tags={"Administración - Direcciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="estado", type="string"),
     *         @OA\Property(property="ciudad", type="string"),
     *         @OA\Property(property="sector", type="string", nullable=true),
     *         @OA\Property(property="calle", type="string", nullable=true),
     *         @OA\Property(property="sede", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Dirección actualizada", @OA\JsonContent(ref="#/components/schemas/Direccion")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, Direccion $direccion)
    {
        $data = $request->validate([
            'estado' => 'sometimes|required|string|max:100',
            'ciudad' => 'sometimes|required|string|max:100',
            'sector' => 'nullable|string|max:100',
            'calle'  => 'nullable|string|max:255',
            'sede'   => 'nullable|string|max:100',
        ]);

        $direccion->update($data);

        return new DireccionResource($direccion);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/direcciones/{id}",
     *     summary="Eliminar dirección",
     *     tags={"Administración - Direcciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(Direccion $direccion)
    {
        $direccion->delete();
        return response()->noContent();
    }
}
