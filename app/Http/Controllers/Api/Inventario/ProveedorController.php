<?php

namespace App\Http\Controllers\Api\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use App\Http\Resources\ProveedorResource;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::orderBy('nombre')->get();
        return ProveedorResource::collection($proveedores);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'contacto' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email'    => 'required|email|unique:proveedores,email',
        ]);

        $proveedor = Proveedor::create($request->all());

        return response()->json([
            'mensaje' => 'Proveedor registrado correctamente',
            'data'    => new ProveedorResource($proveedor)
        ], 201);
    }

    public function show($id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json(['mensaje' => 'Proveedor no encontrado'], 404);
        }

        return new ProveedorResource($proveedor);
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json(['mensaje' => 'Proveedor no encontrado'], 404);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'email'  => 'sometimes|required|email|unique:proveedores,email,' . $id,
        ]);

        $proveedor->update($request->all());

        return response()->json([
            'mensaje' => 'Proveedor actualizado',
            'data'    => new ProveedorResource($proveedor)
        ]);
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json(['mensaje' => 'Proveedor no encontrado'], 404);
        }

        $proveedor->delete();

        return response()->json(['mensaje' => 'Proveedor eliminado correctamente']);
    }
}
