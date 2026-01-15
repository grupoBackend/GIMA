<?php

namespace App\Http\Controllers\Api\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use Illuminate\Http\Request;
use App\Http\Resources\ActivoResource;
use App\Enums\EstadoActivo;
use Illuminate\Validation\Rule; //Necesario para validar enums

class ActivoController extends Controller
{
    /**
     * GET /api/catalogo/activos
     */
    public function index()
    {
        // Traemos los datos con relaciones
        $activos = Activo::with(['articulo', 'ubicacion'])->paginate(10);

        // 2. USAMOS ::collection para listas paginadas
        // Esto envuelve automáticamente la data y la metadata de paginación
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
     * PUT/PATCH /api/catalogo/activos/{id}
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
     * DELETE /api/catalogo/activos/{id}
     */
    public function destroy(Activo $activo)
    {
        $activo->delete();
        // Para delete no necesitamos resource, un 204 está bien
        return response()->json(null, 204);
    }
}