<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Ubicacion;
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
 *     @OA\Property(property="direccion_id", type="integer"),
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
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string"), description="Término de búsqueda"),
     *     @OA\Parameter(name="solo_direcciones", in="query", required=false, @OA\Schema(type="boolean"), description="Filtrar solo por direcciones"),
     *     @OA\Response(response=200, description="Lista de ubicaciones", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Ubicacion")))
     * )
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ubicacion::with(['direccion', 'activos'])->get();
        $query->when($request->search, fn($q, $v) => $q->search($v));
        $query->when($request->solo_direcciones, fn($q) => $q->direcciones());
        return response()->json($query, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/ubicaciones",
     *     summary="Crear ubicación",
     *     tags={"Administración - Ubicaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="direccion_id", type="integer", nullable=true),
     *         @OA\Property(property="edificio", type="string"),
     *         @OA\Property(property="piso", type="string"),
     *         @OA\Property(property="salon", type="string")
     *     )),
     *     @OA\Response(response=201, description="Ubicación creada", @OA\JsonContent(ref="#/components/schemas/Ubicacion")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'edificio' => 'required|string|max:100',
            'piso' => 'required|string|max:100',
            'salon' => 'required|string|max:100',
        ]);

        $ubicacion = Ubicacion::create($data);

        return response()->json($ubicacion, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ubicaciones/{id}",
     *     summary="Ver ubicación",
     *     tags={"Administración - Ubicaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Ubicación encontrada", @OA\JsonContent(ref="#/components/schemas/Ubicacion")),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     * Display the specified resource.
     */
    public function show(Ubicacion $ubicacion)
    {
        $ubicacion->load(['ubicaciones', 'repuestos']);
        return response()->json($ubicacion, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/ubicaciones/{id}",
     *     summary="Actualizar ubicación",
     *     tags={"Administración - Ubicaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="edificio", type="string", nullable=true),
     *         @OA\Property(property="piso", type="string", nullable=true),
     *         @OA\Property(property="salon", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Ubicación actualizada", @OA\JsonContent(ref="#/components/schemas/Ubicacion")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ubicacion $ubicacion)
    {
        $data = $request->validate([
            'edificio' => 'sometimes|required|string|max:100',
            'piso' => 'sometimes|required|string|max:100',
            'salon' => 'sometimes|required|string|max:100',

        ]);

        $ubicacion->update($data);
        return response()->json($ubicacion, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/ubicaciones/{id}",
     *     summary="Eliminar ubicación",
     *     tags={"Administración - Ubicaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     * Remove the specified resource from storage.
     */
    public function destroy(Ubicacion $ubicacion)
    {
        $ubicacion->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
