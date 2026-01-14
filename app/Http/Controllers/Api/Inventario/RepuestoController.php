<?php

namespace App\Http\Controllers\Api\Inventario;

use App\Models\Repuesto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RepuestoResource;

class RepuestoController extends Controller
{
    // ==========================================
    // PARTE 1: CRUD DE REPUESTOS (Estándar)
    // ==========================================

    public function index()
    {
        // Traemos los repuestos e incluimos la información de su proveedor
        $repuestos = Repuesto::with('proveedor')->get();
        return response()->json($repuestos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id', // El proveedor debe existir
            'descripcion'  => 'required|string',
            'codigo'       => 'nullable|string',
            'costo'        => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
        ]);

        $repuesto = Repuesto::create($request->all());

        return response()->json([
            'mensaje' => 'Repuesto creado correctamente',
            'data'    => $repuesto
        ], 201);
    }

    public function show($id)
    {
        $repuesto = Repuesto::with('proveedor')->find($id);

        if (!$repuesto) {
            return response()->json(['mensaje' => 'Repuesto no encontrado'], 404);
        }

        return response()->json($repuesto);
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
            'data'    => $repuesto
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

    // ==========================================
    // PARTE 2: GESTIÓN DE STOCK (Especial)
    // ==========================================

    /**
     * GET /api/inventario/stock
     * Lista rápida solo para ver cantidades disponibles
     */
    public function indexStock()
    {
        // Seleccionamos solo lo necesario para una vista de inventario rápido
        $stock = Repuesto::select('id', 'descripcion', 'codigo', 'stock', 'stock_minimo')->get();
        return response()->json($stock);
    }

    /**
     * PUT/PATCH /api/inventario/stock/{id}
     * Actualizar SOLO la cantidad de stock (ej. Ajuste de inventario manual)
     */
    public function updateStock(Request $request, $id)
    {
        $repuesto = Repuesto::find($id);

        if (!$repuesto) {
            return response()->json(['mensaje' => 'Repuesto no encontrado'], 404);
        }

        // Validamos que solo nos envíen la cantidad
        $request->validate([
            'stock' => 'required|integer|min:0'
        ]);

        // Actualizamos solo el campo stock
        $repuesto->stock = $request->stock;
        $repuesto->save();

        // Verificamos si quedó por debajo del mínimo para avisar (Opcional)
        $alerta = null;
        if ($repuesto->stock <= $repuesto->stock_minimo) {
            $alerta = "¡Atención! El stock está por debajo del mínimo ({$repuesto->stock_minimo}).";
        }

        return response()->json([
            'mensaje' => 'Stock actualizado correctamente',
            'nuevo_stock' => $repuesto->stock,
            'alerta' => $alerta
        ]);
    }
}