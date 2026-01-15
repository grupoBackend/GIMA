<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Administración - Usuarios",
 *     description="Endpoints para la gestión de usuarios"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/users",
     *     summary="Obtener una lista paginada de usuarios",
     *     tags={"Administración - Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", description="Número de página", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Acceso denegado")
     * )
     */
    public function index()
    {
        // Eager load roles to prevent N+1 queries
        return UserResource::collection(User::with('roles')->paginate(15));
    }

    /**
     * @OA\Post(
     *     path="/api/admin/users",
     *     summary="Crear un nuevo usuario",
     *     tags={"Administración - Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del usuario a crear",
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8),
     *             @OA\Property(property="password_confirmation", type="string", format="password", minLength=8),
     *             @OA\Property(property="telefono", type="string", nullable=true),
     *             @OA\Property(property="estado", type="string", enum={"activo", "inactivo", "pendiente", "rechazado"}),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string"), description="Array de nombres de roles a asignar", example={"Admin", "Tecnico"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario creado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Acceso denegado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:20',
            'estado' => 'required|in:activo,inactivo,pendiente,rechazado',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        // Assign roles if provided
        if ($request->has('roles')) {
            $user->assignRole($request->input('roles'));
        }

        return new UserResource($user->load('roles'));
    }

    /**
     * @OA\Get(
     *     path="/api/admin/users/{id}",
     *     summary="Obtener un usuario por ID",
     *     tags={"Administración - Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del usuario", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Detalle del usuario",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Acceso denegado"),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function show(User $user)
    {
        return new UserResource($user->load('roles'));
    }

    /**
     * @OA\Put(
     *     path="/api/admin/users/{id}",
     *     summary="Actualizar un usuario existente",
     *     tags={"Administración - Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del usuario", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del usuario a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, nullable=true),
     *             @OA\Property(property="password_confirmation", type="string", format="password", minLength=8, nullable=true),
     *             @OA\Property(property="telefono", type="string", nullable=true),
     *             @OA\Property(property="estado", type="string", enum={"activo", "inactivo", "pendiente", "rechazado"}),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string"), description="Array de nombres de roles a sincronizar", example={"Tecnico"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario actualizado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Acceso denegado"),
     *     @OA\Response(response=404, description="Usuario no encontrado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'telefono' => 'sometimes|nullable|string|max:20',
            'estado' => 'sometimes|required|in:activo,inactivo,pendiente,rechazado',
            'roles' => 'sometimes|array',
            'roles.*' => 'string|exists:roles,name',
        ]);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        // Sync roles if provided
        if ($request->has('roles')) {
            $user->syncRoles($request->input('roles'));
        }
        return new UserResource($user->load('roles'));
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/users/{id}",
     *     summary="Eliminar un usuario",
     *     tags={"Administración - Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del usuario", @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Usuario eliminado exitosamente"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Acceso denegado"),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function destroy(User $user)
    {
        // Add safety checks if needed, e.g., don't delete self or admin
        $user->delete();
        return response()->noContent();
    }
}
