<?php

namespace App\Http\Controllers\Api\Catalogo;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\MaterialArticulo;
use App\Http\Resources\MaterialArticuloResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\TipoMaterial;

/**
 * @OA\Tag(
 *     name="Catálogo - Materiales",
 *     description="Recursos y materiales adjuntos a artículos"
 * )
 */
/**
 * @OA\Schema(
 *     schema="MaterialArticulo",
 *     type="object",
 *     title="MaterialArticulo",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="articulo_id", type="integer"),
 *     @OA\Property(property="tipo", type="string"),
 *     @OA\Property(property="titulo", type="string"),
 *     @OA\Property(property="url", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */
class MaterialArticuloController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/catalogo/materiales",
     *     summary="Listar materiales de artículos",
     *     tags={"Catálogo - Materiales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de materiales", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/MaterialArticulo")))
     * )
     */
    /**
     * Listar todos los materiales
     */

    public function index(Request $request)
    {
        $query = MaterialArticulo::query();
        $query->search($request->query('search'));
        $materiales = $query->get();

        return MaterialArticuloResource::collection($materiales);
    }

    /**
     * @OA\Post(
     *     path="/api/catalogo/materiales",
     *     summary="Crear material de artículo",
     *     tags={"Catálogo - Materiales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="articulo_id", type="integer"),
     *         @OA\Property(property="tipo", type="string"),
     *         @OA\Property(property="titulo", type="string"),
     *         @OA\Property(property="descripcion", type="string", nullable=true),
     *         @OA\Property(property="url", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=201, description="Material creado", @OA\JsonContent(ref="#/components/schemas/MaterialArticulo")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'articulo_id' => 'required|exists:articulos,id',
            // Validamos que el tipo sea uno de los permitidos en tu Enum
            'tipo' => ['required', Rule::enum(TipoMaterial::class)],
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'url' => 'nullable|url',
            'fecha_subida' => 'nullable|date',
        ]);

        // Si no mandan fecha de subida, ponemos la actual por defecto
        if (!isset($validated['fecha_subida'])) {
            $validated['fecha_subida'] = now();
        }

        $material = MaterialArticulo::create($validated);

        return new MaterialArticuloResource($material);
    }

    /**
     * @OA\Get(
     *     path="/api/catalogo/materiales/{id}",
     *     summary="Ver material",
     *     tags={"Catálogo - Materiales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Material", @OA\JsonContent(ref="#/components/schemas/MaterialArticulo")),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(MaterialArticulo $material_articulo)
    {
        return new MaterialArticuloResource($material_articulo);
    }

    /**
     * @OA\Put(
     *     path="/api/catalogo/materiales/{id}",
     *     summary="Actualizar material",
     *     tags={"Catálogo - Materiales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="tipo", type="string", nullable=true),
     *         @OA\Property(property="titulo", type="string", nullable=true),
     *         @OA\Property(property="descripcion", type="string", nullable=true),
     *         @OA\Property(property="url", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Material actualizado", @OA\JsonContent(ref="#/components/schemas/MaterialArticulo")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */

    public function download($id)
    {
        $material = MaterialArticulo::findOrFail($id);

        // 1. Validar que la URL sea válida
        if (!filter_var($material->url, FILTER_VALIDATE_URL)) {
            return response()->json(['message' => 'URL inválida'], 400);
        }

        // 2. Lógica basada en el Enum que creaste
        // Suponiendo que tu modelo tiene un campo llamado 'tipo' que usa el Enum
        if ($material->tipo === TipoMaterial::ENLACE) {
            // Si es enlace, simplemente redireccionamos
            return redirect()->away($material->url);
        }

        // 3. Si es MANUAL o DATASHEET, forzamos la descarga
        try {
            $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($material->url);

            if ($response->failed()) {
                return response()->json(['message' => 'No se pudo obtener el archivo'], 502);
            }

            // Usamos el label del Enum para el nombre del archivo si quieres
            $extension = pathinfo($material->url, PATHINFO_EXTENSION) ?: 'pdf';
            $nombreArchivo = "{$material->tipo->label()}_{$id}.{$extension}";

            return response($response->body(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$nombreArchivo}\"");
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, MaterialArticulo $material_articulo)
    {
        $validated = $request->validate([
            'articulo_id' => 'sometimes|exists:articulos,id',
            'tipo' => ['sometimes', Rule::enum(TipoMaterial::class)],
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'url' => 'nullable|url',
            'fecha_subida' => 'nullable|date',
        ]);

        $material_articulo->update($validated);

        return new MaterialArticuloResource($material_articulo);
    }

    /**
     * @OA\Delete(
     *     path="/api/catalogo/materiales/{id}",
     *     summary="Eliminar material",
     *     tags={"Catálogo - Materiales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado")
     * )
     */
    public function destroy(MaterialArticulo $material_articulo)
    {
        $material_articulo->delete();
        return response()->noContent();
    }
}
