<?php

namespace App\Http\Controllers\Api\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Repuesto;
use Illuminate\Http\Request;
use App\Http\Resources\RepuestoResource;

/**
 * @OA\Tag(
 *     name="Inventario - Repuestos",
 *     description="Gestión de repuestos y stock"
 * )
 * @OA\Schema(
 *     schema="Repuesto",
 *     type="object",
 *     title="Repuesto",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="descripcion", type="string"),
 *     @OA\Property(property="codigo", type="string", nullable=true),
 *     @OA\Property(property="costo", type="number"),
 *     @OA\Property(property="stock", type="number"),
 *     @OA\Property(property="stock_minimo", type="number"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */

class RepuestoController extends Controller
{
    public function index()
    {
        $repuestos = Repuesto::with(['proveedor', 'direccion'])->get();
        
        return RepuestoResource::collection($repuestos);
    }

    /**
     * @OA\Post(
     *     path="/api/inventario/repuestos",
     *     summary="Crear repuesto",
     *     tags={"Inventario - Repuestos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="proveedor_id", type="integer"),
     *         @OA\Property(property="direccion_id", type="integer", nullable=true),
     *         @OA\Property(property="descripcion", type="string"),
     *         @OA\Property(property="codigo", type="string", nullable=true),
     *         @OA\Property(property="costo", type="number"),
     *         @OA\Property(property="stock", type="number"),
     *         @OA\Property(property="stock_minimo", type="number")
     *     )),
     *     @OA\Response(response=201, description="Repuesto creado", @OA\JsonContent(ref="#/components/schemas/Repuesto")),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'direccion_id' => 'nullable|exists:direcciones,id',
            'descripcion'  => 'required|string',
            'codigo'       => 'nullable|string',
            'costo'        => 'required|numeric|min:0',
            'stock'        => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
        ]);

        $repuesto = Repuesto::create($request->all());

        $repuesto->load(['proveedor', 'direccion']);

        return (new RepuestoResource($repuesto))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/inventario/repuestos/{id}",
     *     summary="Ver repuesto",
     *     tags={"Inventario - Repuestos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Repuesto", @OA\JsonContent(ref="#/components/schemas/Repuesto")),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */

    public function show(Repuesto $repuesto)
    {
        $repuesto->load(['proveedor', 'direccion']);

        return new RepuestoResource($repuesto);
    }

    /**
     * @OA\Put(
     *     path="/api/inventario/repuestos/{id}",
     *     summary="Actualizar repuesto",
     *     tags={"Inventario - Repuestos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="descripcion", type="string", nullable=true),
     *         @OA\Property(property="costo", type="number", nullable=true),
     *         @OA\Property(property="stock", type="number", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Repuesto actualizado", @OA\JsonContent(ref="#/components/schemas/Repuesto")),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */

    public function update(Request $request, $id)
    {
        $repuesto = Repuesto::find($id);

        if (!$repuesto) {
            return response()->json(['mensaje' => 'Repuesto no encontrado'], 404);
        }

        $repuesto->update($request->all());

        return (new RepuestoResource($repuesto));
    }

    /**
     * @OA\Delete(
     *     path="/api/inventario/repuestos/{id}",
     *     summary="Eliminar repuesto",
     *     tags={"Inventario - Repuestos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Eliminado"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */

    public function destroy(Repuesto $repuesto)
    {
        $repuesto->delete();
        return response()->noContent();
    }


    // --- MÉTODOS ESPECIALES DE STOCK ---
    //Funcion indexStock eliminada, se maneja con el query param 'alerta_stock' en el index general

    //updateStock eliminada, se maneja con el nuevo método ajustarStock (PATCH) para un enfoque más RESTful
    public function updateStock(Request $request, $id)
    {
        $repuesto = Repuesto::find($id);

        if (!$repuesto) {
            return response()->json(['mensaje' => 'Repuesto no encontrado'], 404);
        }

        $request->validate(['stock' => 'required|numeric|min:0']);

        $repuesto->stock = $request->stock;
        $repuesto->save();

        $alerta = ($repuesto->stock <= $repuesto->stock_minimo)
            ? "¡Alerta! Stock bajo mínimo ({$repuesto->stock_minimo})"
            : null;

        return response()->json([
            'mensaje'     => 'Stock actualizado',
            'nuevo_stock' => $repuesto->stock,
            'alerta'      => $alerta
        ]);
    }

    // RENOMBRAR updateStock a ajustarStock (PATCH)
    public function ajustarStock(Request $request, Repuesto $repuesto) // Ahora inyectamos el modelo directo
    {
        $request->validate(['stock' => 'required|numeric|min:0']);

        $repuesto->stock = $request->stock;
        $repuesto->save();

        $alerta = ($repuesto->stock <= $repuesto->stock_minimo) 
            ? "¡Alerta! Stock bajo mínimo ({$repuesto->stock_minimo})" 
            : null;

        return response()->json([
            'mensaje'     => 'Stock ajustado correctamente',
            'data'        => new RepuestoResource($repuesto),
            'alerta'      => $alerta
        ]);
    }
}
