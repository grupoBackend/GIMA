<?php

namespace App\Http\Controllers\Api\Inventario;

use App\Models\Proveedor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProveedorResource;

class ProveedorController extends Controller
{
    /**
     * Listar todos los proveedores
     */
    public function index()
    {
        // Retornamos todos los proveedores ordenados por nombre
        return response()->json(Proveedor::orderBy('nombre')->get());
    }

    /**
     * Crear un nuevo proveedor
     */
    public function store(Request $request)
    {
        // 1. Validamos que los datos vengan bien
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'contacto' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email'    => 'required|email|unique:proveedores,email', // Email único en la tabla proveedores
        ]);

        // 2. Creamos el proveedor
        $proveedor = Proveedor::create($request->all());

        // 3. Retornamos respuesta 201 (Created)
        return response()->json([
            'mensaje' => 'Proveedor registrado correctamente',
            'data'    => $proveedor
        ], 201);
    }

    /**
     * Ver un proveedor específico
     */
    public function show($id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json(['mensaje' => 'Proveedor no encontrado'], 404);
        }

        return response()->json($proveedor);
    }

    /**
     * Actualizar un proveedor
     */
    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json(['mensaje' => 'Proveedor no encontrado'], 404);
        }

        // Validamos (el email es único, pero ignoramos el ID actual para que no de error si no lo cambia)
        $request->validate([
            'nombre'   => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|email|unique:proveedores,email,' . $id,
        ]);

        $proveedor->update($request->all());

        return response()->json([
            'mensaje' => 'Proveedor actualizado',
            'data'    => $proveedor
        ]);
    }

    /**
     * Eliminar un proveedor
     */
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