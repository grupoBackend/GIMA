<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Ubicacion;
use App\Http\Resources\UbicacionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="Administración - Ubicaciones",
 *     description="Endpoints para gestión de ubicaciones"
 * )
 * @OA\Schema(
 *     schema="Ubicacion",
 *     type="object",
 *     title="Ubicacion",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="edificio", type="string"),
 *     @OA\Property(property="piso", type="string"),
 *     @OA\Property(property="salon", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */
class UbicacionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/ubicaciones",
     *     summary="Listar ubicaciones",
     *     tags={"Administración - Ubicaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de ubicaciones", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Ubicacion")))
     * )
     */
    public function index()
    {
        $ubicaciones = Ubicacion::with(['direccion', 'activos'])->paginate(15);
        return UbicacionResource::collection($ubicaciones);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ubicaciones",
     *     summary="Crear ubicacion",
     *     tags={"Administración - Ubicaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="edificio", type="string"),
     *         @OA\Property(property="piso", type="string"),
     *         @OA\Property(property="salon", type="string")
     *     )),
     *     @OA\Response(response=201, description="Ubicación creada", @OA\JsonContent(ref="#/components/schemas/Ubicacion")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'edificio' => 'required|string|max:100',
            'piso' => 'required|string|max:100',
            'salon' => 'required|string|max:100',
        ]);

        $ubicacion = Ubicacion::create($data);

        return (new UbicacionResource($ubicacion))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ubicaciones/{id}",
     *     summary="Ver ubicacion",
     *     tags={"Administración - Ubicaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Ubicación encontrada", @OA\JsonContent(ref="#/components/schemas/Ubicacion")),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     */
    public function show(Ubicacion $ubicacion)
    {
        $ubicacion->load(['direccion', 'activos']);
        return new UbicacionResource($ubicacion);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/ubicaciones/{id}",
     *     summary="Actualizar ubicacion",
     *     tags={"Administración - Ubicaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="edificio", type="string"),
     *         @OA\Property(property="piso", type="string"),
     *         @OA\Property(property="salon", type="string")
     *     )),
     *     @OA\Response(response=200, description="Ubicación actualizada", @OA\JsonContent(ref="#/components/schemas/Ubicacion")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, Ubicacion $ubicacion)
    {
        $data = $request->validate([
            'edificio' => 'sometimes|required|string|max:100',
            'piso' => 'sometimes|required|string|max:100',
            'salon' => 'sometimes|required|string|max:100',

        ]);

        $ubicacion->update($data);

        return new UbicacionResource($ubicacion);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/ubicaciones/{id}",
     *     summary="Eliminar ubicacion",
     *     tags={"Administración - Ubicaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(Ubicacion $ubicacion)
    {
        $ubicacion->delete();
        return response()->noContent();
    }
}
