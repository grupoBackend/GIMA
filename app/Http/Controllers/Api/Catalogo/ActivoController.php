<?php

namespace App\Http\Controllers\Api\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use Illuminate\Http\Request;
use App\Http\Resources\ActivoResource;
use App\Enums\EstadoActivo;
use App\Enums\TipoArticulo;
use Illuminate\Validation\Rule; //Necesario para validar enums
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="Catálogo - Activos",
 *     description="Operaciones sobre activos del catálogo"
 * )
 */
/**
 * @OA\Schema(
 *     schema="Activo",
 *     type="object",
 *     title="Activo",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="articulo_id", type="integer"),
 *     @OA\Property(property="ubicacion_id", type="integer"),
 *     @OA\Property(property="estado", type="string"),
 *     @OA\Property(property="valor", type="number"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */
class ActivoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/catalogo/activos",
     *     summary="Listar activos",
     *     tags={"Catálogo - Activos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de activos", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Activo")))
     * )
     */
    public function index(Request $request)
    {
        $query = Activo::query()->with(['articulo', 'ubicacion']);

        // Aplicamos los filtros definidos por el equipo
        $query->when($request->search, fn($q, $v) => $q->search($v))->when($request->sede_id, fn($q, $v) => $q->porSede($v))
            ->when($request->estado, fn($q, $v) => $q->where('estado', $v));

        // Paginamos (permitiendo que el frontend elija cuántos, o 10 por defecto)
        $activos = $query->paginate($request->per_page ?? 10);

        return ActivoResource::collection($activos);
    }

    /**
     * @OA\Post(
     *     path="/api/catalogo/activos",
     *     summary="Crear activo",
     *     tags={"Catálogo - Activos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="articulo_id", type="integer"),
     *         @OA\Property(property="ubicacion_id", type="integer"),
     *         @OA\Property(property="estado", type="string"),
     *         @OA\Property(property="valor", type="number")
     *     )),
     *     @OA\Response(response=201, description="Activo creado", @OA\JsonContent(ref="#/components/schemas/Activo")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'articulo_id'  => 'required|exists:articulos,id',
            'ubicacion_id' => 'required|exists:ubicaciones,id',
            'estado'       => ['required', Rule::enum(EstadoActivo::class)],
            'valor'        => 'required|numeric|min:0',
        ]);

        $activo = Activo::create($validatedData);

        // Cargamos relaciones por si queremos devolverlas en la respuesta de creación
        $activo->load(['articulo', 'ubicacion']);

        // 3. USAMOS new ActivoResource para un solo objeto
        return (new ActivoResource($activo))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/catalogo/activos/{id}",
     *     summary="Ver activo",
     *     tags={"Catálogo - Activos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Activo", @OA\JsonContent(ref="#/components/schemas/Activo")),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Activo $activo)
    {
        // Cargamos relaciones incluyendo mantenimientos
        $activo->load(['articulo', 'ubicacion', 'calendarioMantenimientos', 'mantenimientos']);

        // 4. Devolvemos el resource individual
        return new ActivoResource($activo);
    }

    /**
     * @OA\Put(
     *     path="/api/catalogo/activos/{id}",
     *     summary="Actualizar activo",
     *     tags={"Catálogo - Activos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="articulo_id", type="integer", nullable=true),
     *         @OA\Property(property="ubicacion_id", type="integer", nullable=true),
     *         @OA\Property(property="estado", type="string", nullable=true),
     *         @OA\Property(property="valor", type="number", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Activo actualizado", @OA\JsonContent(ref="#/components/schemas/Activo")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, Activo $activo)
    {
        $validatedData = $request->validate([
            'articulo_id'  => 'sometimes|exists:articulos,id',
            'ubicacion_id' => 'sometimes|exists:ubicaciones,id',
            'estado'       => ['sometimes', Rule::enum(EstadoActivo::class)],
            'valor'        => 'sometimes|numeric|min:0',
        ]);

        $activo->update($validatedData);

        // 5. Devolvemos el resource actualizado
        return new ActivoResource($activo->load(['articulo', 'ubicacion']));
    }
    /**
     * PATCH /api/catalogo/activos/{activo}/status
     */
    public function changeStatus(Request $request, Activo $activo)
    {
        // Validamos usando el Enum que ya tienes importado
        $validatedData = $request->validate([
            'estado' => ['required', Rule::enum(EstadoActivo::class)],
        ]);

        $activo->update($validatedData);

        return new ActivoResource($activo->load(['articulo', 'ubicacion']));
    }

    /**
     * @OA\Delete(
     *     path="/api/catalogo/activos/{id}",
     *     summary="Eliminar activo",
     *     tags={"Catálogo - Activos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado")
     * )
     */
    public function destroy(Activo $activo)
    {
        $activo->delete();
        // Para delete no necesitamos resource, un 204 está bien
        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/api/catalogo/activos/por-categoria",
     *     summary="Listar activos por categoría",
     *     tags={"Catálogo - Activos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="tipo", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Activos filtrados", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Activo")))
     * )
     */
    public function activosPorCategoria(Request $request)
    {
        //Validar que el tipo sea un valor válido del enum TipoArticulo
        $validated = $request->validate([
            'tipo'     => ['required', Rule::enum(TipoArticulo::class)],
            'per_page' => 'sometimes|integer|min:1',
        ]);

        // Si no se especifica per_page, por defecto paginamos de 10 en 10
        $perPage = $validated['per_page'] ?? 10;

        // Filtramos los activos que tienen un articulo con el tipo especificado
        $activos = Activo::whereHas('articulo', function ($q) use ($validated) {
            $q->where('tipo', $validated['tipo']);
        })->with(['articulo', 'ubicacion'])->paginate($perPage);

        // Devolvemos la colección paginada de recursos
        return ActivoResource::collection($activos);
    }
}
