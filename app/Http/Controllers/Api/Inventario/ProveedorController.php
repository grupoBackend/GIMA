<?php

namespace App\Http\Controllers\Api\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use App\Http\Resources\ProveedorResource;

/**
 * @OA\Tag(
 * name="Inventario - Proveedores",
 * description="Gestión de proveedores"
 * )
 * @OA\Schema(
 * schema="Proveedor",
 * type="object",
 * title="Proveedor",
 * @OA\Property(property="id", type="integer", format="int64"),
 * @OA\Property(property="nombre", type="string"),
 * @OA\Property(property="contacto", type="string", nullable=true),
 * @OA\Property(property="telefono", type="string", nullable=true),
 * @OA\Property(property="email", type="string", format="email", nullable=true),
 * @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 * @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */
class ProveedorController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/inventario/proveedores",
     * summary="Listar proveedores",
     * tags={"Inventario - Proveedores"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Lista de proveedores", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Proveedor")))
     * )
     */
    public function index(Request $request)
    {
        $query = Proveedor::query();

        // Aplica el scopeSearch si viene un término de búsqueda
        $query->when($request->search, fn($q, $v) => $q->search($v));

        return response()->json($query->paginate(15));
    }

    /**
     * @OA\Post(
     * path="/api/inventario/proveedores",
     * summary="Crear proveedor",
     * tags={"Inventario - Proveedores"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(@OA\JsonContent(
     * @OA\Property(property="nombre", type="string"),
     * @OA\Property(property="contacto", type="string"),
     * @OA\Property(property="telefono", type="string"),
     * @OA\Property(property="email", type="string", format="email")
     * )),
     * @OA\Response(response=201, description="Proveedor creado", @OA\JsonContent(ref="#/components/schemas/Proveedor")),
     * @OA\Response(response=422, description="Error de validación")
     * )
     */
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

    /**
     * @OA\Get(
     * path="/api/inventario/proveedores/{id}",
     * summary="Ver proveedor",
     * tags={"Inventario - Proveedores"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Proveedor", @OA\JsonContent(ref="#/components/schemas/Proveedor")),
     * @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show($id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json(['mensaje' => 'Proveedor no encontrado'], 404);
        }

        return new ProveedorResource($proveedor);
    }

    /**
     * @OA\Put(
     * path="/api/inventario/proveedores/{id}",
     * summary="Actualizar proveedor",
     * tags={"Inventario - Proveedores"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(@OA\JsonContent(
     * @OA\Property(property="nombre", type="string", nullable=true),
     * @OA\Property(property="email", type="string", format="email", nullable=true)
     * )),
     * @OA\Response(response=200, description="Proveedor actualizado", @OA\JsonContent(ref="#/components/schemas/Proveedor")),
     * @OA\Response(response=404, description="No encontrado")
     * )
     */
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

    /**
     * @OA\Delete(
     * path="/api/inventario/proveedores/{id}",
     * summary="Eliminar proveedor",
     * tags={"Inventario - Proveedores"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=204, description="Eliminado"),
     * @OA\Response(response=404, description="No encontrado")
     * )
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