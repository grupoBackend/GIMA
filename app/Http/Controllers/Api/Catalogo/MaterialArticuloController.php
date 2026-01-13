<?php

namespace App\Http\Controllers\Api\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\MaterialArticulo;
use App\Http\Resources\MaterialArticuloResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\TipoMaterial; // <--- Importante si vas a validar el Enum

class MaterialArticuloController extends Controller
{
    /**
     * Listar todos los materiales
     */
    public function index()
    {
        $materiales = MaterialArticulo::all();
        return MaterialArticuloResource::collection($materiales);
    }

    /**
     * Crear un nuevo material
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
     * Ver uno específico
     */
    public function show(MaterialArticulo $materiales_articulo)
    {
        return new MaterialArticuloResource($materiales_articulo);
    }

    /**
     * Actualizar
     */
    public function update(Request $request, MaterialArticulo $materiales_articulo)
    {
        $validated = $request->validate([
            'articulo_id' => 'sometimes|exists:articulos,id',
            'tipo' => ['sometimes', Rule::enum(TipoMaterial::class)],
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'url' => 'nullable|url',
            'fecha_subida' => 'nullable|date',
        ]);

        $materiales_articulo->update($validated);

        return new MaterialArticuloResource($materiales_articulo);
    }

    /**
     * Eliminar
     */
    public function destroy(MaterialArticulo $materiales_articulo)
    {
        $materiales_articulo->delete();
        return response()->noContent();
    }
}