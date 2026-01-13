<?php

namespace App\Http\Controllers\Api\Catalogo;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use Illuminate\Http\Request;
use App\Http\Resources\ArticuloResource;

class ArticuloController extends Controller
{
    /**
     * GET /api/catalogo/articulos
     */
    public function index()
    {
        $articulos = Articulo::withCount('activos')->paginate(15);
        return ArticuloResource::collection($articulos);
    }

    /**
     * POST /api/catalogo/articulos
     */
    public function store(Request $request)
    {
        // Validamos los datos entrantes
        $datosValidados = $request->validate([
            'tipo'        => 'required|string|max:255',
            'marca'       => 'required|string|max:100',
            'modelo'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        // Usamos esa variable para crear
        $articulo = Articulo::create($datosValidados);

        return new ArticuloResource($articulo);
    }

    /**
     * GET /api/catalogo/articulos/{id}
     */
    public function show(Articulo $articulo)
    {
        return new ArticuloResource($articulo);
    }

    /**
     * PUT/PATCH /api/catalogo/articulos/{id}
     */
    public function update(Request $request, Articulo $articulo)
    {
        //validamos los datos entrantes
        $datosValidados = $request->validate([
            'tipo'        => 'sometimes|string|max:255',
            'marca'       => 'sometimes|string|max:100',
            'modelo'      => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $articulo->update($datosValidados);

        return new ArticuloResource($articulo);
    }

    /**
     * DELETE /api/catalogo/articulos/{id}
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