<?php

namespace App\Http\Controllers\Api\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use Illuminate\Http\Request;
use App\Http\Resources\ArticuloResource;
use Illuminate\Validation\Rule;
use App\Enums\TipoArticulo;

/**
 * @OA\Tag(
 *     name="Catálogo - Artículos",
 *     description="Operaciones sobre artículos"
 * )
 */
/**
 * @OA\Schema(
 *     schema="Articulo",
 *     type="object",
 *     title="Articulo",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="tipo", type="string"),
 *     @OA\Property(property="marca", type="string"),
 *     @OA\Property(property="modelo", type="string"),
 *     @OA\Property(property="descripcion", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */

class ArticuloController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/catalogo/articulos",
     *     summary="Listar artículos",
     *     tags={"Catálogo - Artículos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de artículos", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Articulo")))
     * )
     */
    /**
     * GET /api/catalogo/articulos
     */
    public function index(Request $request)
{
    $query = Articulo::query();

    // Aplicamos solo el buscador global
    $query->when($request->search, fn($q, $v) => $q->search($v));

    return ArticuloResource::collection($query->paginate($request->per_page ?? 10));
}

    /**
     * @OA\Post(
     *     path="/api/catalogo/articulos",
     *     summary="Crear artículo",
     *     tags={"Catálogo - Artículos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="tipo", type="string"),
     *         @OA\Property(property="marca", type="string"),
     *         @OA\Property(property="modelo", type="string"),
     *         @OA\Property(property="descripcion", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=201, description="Artículo creado", @OA\JsonContent(ref="#/components/schemas/Articulo")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        // Validamos los datos entrantes
        $datosValidados = $request->validate([
            'tipo' => ['required', Rule::enum(TipoArticulo::class)],
            'marca'       => 'required|string|max:100',
            'modelo'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        // Usamos esa variable para crear
        $articulo = Articulo::create($datosValidados);

        return new ArticuloResource($articulo);
    }

    /**
     * @OA\Get(
     *     path="/api/catalogo/articulos/{id}",
     *     summary="Ver artículo",
     *     tags={"Catálogo - Artículos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Artículo", @OA\JsonContent(ref="#/components/schemas/Articulo")),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Articulo $articulo)
    {
        return new ArticuloResource($articulo);
    }

    /**
     * @OA\Put(
     *     path="/api/catalogo/articulos/{id}",
     *     summary="Actualizar artículo",
     *     tags={"Catálogo - Artículos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="tipo", type="string"),
     *         @OA\Property(property="marca", type="string"),
     *         @OA\Property(property="modelo", type="string"),
     *         @OA\Property(property="descripcion", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Artículo actualizado", @OA\JsonContent(ref="#/components/schemas/Articulo")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, Articulo $articulo)
    {
        //validamos los datos entrantes
        $datosValidados = $request->validate([
            'tipo' => ['required', Rule::enum(TipoArticulo::class)],
            'marca'       => 'sometimes|string|max:100',
            'modelo'      => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $articulo->update($datosValidados);

        return new ArticuloResource($articulo);
    }

    /**
     * @OA\Delete(
     *     path="/api/catalogo/articulos/{id}",
     *     summary="Eliminar artículo",
     *     tags={"Catálogo - Artículos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado"),
     *     @OA\Response(response=409, description="Conflicto al eliminar")
     * )
     */
    public function destroy(Articulo $articulo)
    {
        // Verificamos si tiene hijos
        if ($articulo->activos()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el artículo porque tiene activos físicos registrados.'
            ], 409);
        }
        // si no tiene hijos, procedemos a eliminar
        $articulo->delete();
        return response()->noContent();
    }
}
