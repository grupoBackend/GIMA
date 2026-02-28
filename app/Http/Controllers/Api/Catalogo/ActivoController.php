<?php

namespace App\Http\Controllers\Api\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use Illuminate\Http\Request;
use App\Http\Resources\ActivoResource;
use App\Enums\EstadoActivo;
use App\Enums\TipoArticulo;
use Illuminate\Validation\Rule; //Necesario para validar enums

class ActivoController extends Controller
{
    /**
     * GET /api/catalogo/activos
     */
    public function index(Request $request)
    {
        $query = Activo::query()->with(['articulo', 'ubicacion']);

        // Aplicamos los filtros definidos por el equipo
        $query->when($request->search, fn($q, $v) => $q->search($v))->when($request->ubicacion_id, fn($q, $v) => $q->porSede($v))
            ->when($request->estado, fn($q, $v) => $q->where('estado', $v));

        // Paginamos (permitiendo que el frontend elija cuántos, o 10 por defecto)
        $activos = $query->paginate($request->per_page ?? 10);

        return ActivoResource::collection($activos);
    }

    /**
     * POST /api/catalogo/activos
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
        return new ActivoResource($activo);
    }

    /**
     * GET /api/catalogo/activos/{id}
     */
    public function show(Activo $activo)
    {
        // Cargamos relaciones incluyendo mantenimientos
        $activo->load(['articulo', 'ubicacion', 'calendarioMantenimientos', 'mantenimientos']);

        // 4. Devolvemos el resource individual
        return new ActivoResource($activo);
    }

    /**
     * PUT/api/catalogo/activos/{id}
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
     * DELETE /api/catalogo/activos/{id}
     */
    public function destroy(Activo $activo)
    {
        $activo->delete();
        // Para delete no necesitamos resource, un 204 está bien
        return response()->json(null, 204);
    }

    /** Filtrar por "categoria" de Articulo
     * usando el campo tipo de la tabla articulo
     * se muestran los Activos de acuerdo al tipo
     * (mobiliario o equipo)
     * GET /api/catalogo/activos/por-categoria?tipo=VALOR&per_page=10
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
