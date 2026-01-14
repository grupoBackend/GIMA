<?php

namespace App\Http\Controllers\Api\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Repuesto;
use Illuminate\Http\Request;
use App\Http\Resources\RepuestoResource;

class RepuestoController extends Controller
{
    public function index()
    {
        $repuestos = Repuesto::with(['proveedor', 'direccion'])->get();
        
        return RepuestoResource::collection($repuestos);
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
    public function indexStock()
    {
        $stock = Repuesto::select('id', 'descripcion', 'codigo', 'stock', 'stock_minimo')->get();
        return response()->json($stock);
    }

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
}