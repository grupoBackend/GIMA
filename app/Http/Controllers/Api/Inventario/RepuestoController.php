<?php

namespace App\Http\Controllers\Api\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Repuesto;
use Illuminate\Http\Request;
use App\Http\Resources\RepuestoResource;

class RepuestoController extends Controller
{
    public function index(Request $request)
    {
        $query = Repuesto::with(['proveedor', 'direccion']);

        // Búsqueda por descripción o código
        $query->when($request->search, function ($q, $v) {
            $q->where('descripcion', 'ilike', "%{$v}%")
              ->orWhere('codigo', 'ilike', "%{$v}%");
        });

        // Filtro de stock bajo
        $query->when($request->boolean('alerta_stock'), fn($q) => $q->stockBajo());

        return RepuestoResource::collection($query->get());
    }

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

        return response()->json([
            'mensaje' => 'Repuesto creado correctamente',
            // ✅ CORRECTO
            'data'    => new RepuestoResource($repuesto)
        ], 201);
    }

    public function show($id)
    {
        $repuesto = Repuesto::with(['proveedor', 'direccion'])->find($id);

        if (!$repuesto) {
            return response()->json(['mensaje' => 'Repuesto no encontrado'], 404);
        }

        return new RepuestoResource($repuesto);
    }

    public function update(Request $request, $id)
    {
        $repuesto = Repuesto::find($id);

        if (!$repuesto) {
            return response()->json(['mensaje' => 'Repuesto no encontrado'], 404);
        }

        $repuesto->update($request->all());

        return response()->json([
            'mensaje' => 'Repuesto actualizado',
            'data'    => new RepuestoResource($repuesto)
        ]);
    }

    public function destroy($id)
    {
        $repuesto = Repuesto::find($id);

        if (!$repuesto) {
            return response()->json(['mensaje' => 'Repuesto no encontrado'], 404);
        }

        $repuesto->delete();

        return response()->json(['mensaje' => 'Repuesto eliminado']);
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