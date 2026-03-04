<?php

namespace App\Http\Controllers\Api\Catalogo;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\MaterialArticulo;
use App\Http\Resources\MaterialArticuloResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\TipoMaterial;

class MaterialArticuloController extends Controller
{
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
    public function show(MaterialArticulo $material_articulo)
    {
        return new MaterialArticuloResource($material_articulo);
    }

    /**
     * Actualizar
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
     * Eliminar
     */
    public function destroy(MaterialArticulo $material_articulo)
    {
        $material_articulo->delete();
        return response()->noContent();
    }
}
