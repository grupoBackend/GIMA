<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
// use App\Http\Requests\Admin\StoreUserRequest;
// use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

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
     * path="/api/admin/users",
     * summary="Obtener una lista paginada de usuarios con filtros",
     * tags={"Administración - Usuarios"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="page", in="query", description="Número de página", @OA\Schema(type="integer")),
     * @OA\Parameter(name="search", in="query", description="Buscar por nombre, email o teléfono", required=false, @OA\Schema(type="string")),
     * @OA\Parameter(name="rol", in="query", description="Filtrar por rol exacto (ej. Admin, Tecnico)", required=false, @OA\Schema(type="string")),
     * @OA\Parameter(name="estado", in="query", description="Filtrar por estado del usuario", required=false, @OA\Schema(type="string", enum={"activo", "inactivo", "suspendido"})),
     * @OA\Response(
     * response=200,
     * description="Lista de usuarios obtenida exitosamente",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     * @OA\Property(property="links", type="object"),
     * @OA\Property(property="meta", type="object")
     * )
     * ),
     * @OA\Response(response=401, description="No autenticado"),
     * @OA\Response(response=403, description="Acceso denegado")
     * )
     */


    /**
     * GET - Listar usuarios con filtros dinámicos
     */
    public function index(Request $request)
    {
        // 1. Iniciamos la consulta cargando la relación de roles para evitar el problema de N+1
        $query = User::with('roles');

        // 2. Aplicamos los filtros dinámicos que vienen por la URL (?search=..., ?rol=..., ?estado=...)
        $query->when($request->search, fn($q, $v) => $q->search($v));
        $query->when($request->rol, fn($q, $v) => $q->porRol($v));
        $query->when($request->estado, fn($q, $v) => $q->estado($v));

        // (Opcional) Si estás usando tu Trait Ordenable, puedes agregarlo aquí antes de paginar:
        // $query->ordenar($request->sort_by, $request->sort_dir);

        // 3. Ejecutamos la paginación
        return UserResource::collection($query->paginate(15));
    }



    /**
     * @OA\Patch(
     * path="/api/admin/users/{id}/estado",
     * summary="Cambiar el estado de un usuario",
     * tags={"Administración - Usuarios"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, description="ID del usuario", @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * required=true,
     * description="Datos para actualizar el estado",
     * @OA\JsonContent(
     * required={"estado"},
     * @OA\Property(property="estado", type="string", enum={"activo", "inactivo", "suspendido"}, description="Nuevo estado a asignar")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Estado actualizado correctamente",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Estado del usuario actualizado correctamente."),
     * @OA\Property(property="data", ref="#/components/schemas/User")
     * )
     * ),
     * @OA\Response(response=401, description="No autenticado"),
     * @OA\Response(response=404, description="Usuario no encontrado"),
     * @OA\Response(response=422, description="Error de validación")
     * )
     */


    /**
     * PATCH - Cambiar el estado del usuario
     * Endpoint sugerido: PATCH /api/admin/users/{id}/estado
     */
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            // Usamos tu Enum o el Rule::in que ya tenías
            'estado' => ['required', 'string', Rule::in(['activo', 'inactivo', 'suspendido'])],
        ]);

        $user = User::findOrFail($id);
        $user->estado = $request->estado;
        $user->save();

        return response()->json([
            'message' => 'Estado del usuario actualizado correctamente.',
            'data' => new UserResource($user)
        ]);
    }




    /**
     * @OA\Patch(
     * path="/api/admin/users/{id}/roles",
     * summary="Asignar o reemplazar el rol de un usuario",
     * tags={"Administración - Usuarios"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, description="ID del usuario", @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * required=true,
     * description="Rol a asignar",
     * @OA\JsonContent(
     * required={"rol"},
     * @OA\Property(property="rol", type="string", description="Nombre del rol (ej. Admin, Supervisor, Tecnico)", example="Tecnico")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Rol asignado correctamente",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Rol asignado correctamente."),
     * @OA\Property(property="data", ref="#/components/schemas/User")
     * )
     * ),
     * @OA\Response(response=401, description="No autenticado"),
     * @OA\Response(response=404, description="Usuario no encontrado"),
     * @OA\Response(response=422, description="Error de validación")
     * )
     * */


    /**
     * PATCH - Asignar o cambiar el rol del usuario
     * Endpoint sugerido: PATCH /api/admin/users/{id}/roles
     */
    public function asignarRol(Request $request, $id)
    {
        $request->validate([
            'rol' => 'required|string|exists:roles,name', // Validamos que el rol exista en la DB
        ]);

        $user = User::findOrFail($id);

        // syncRoles es de Spatie: Elimina los roles anteriores y asigna el nuevo
        $user->syncRoles([$request->rol]);

        return response()->json([
            'message' => 'Rol asignado correctamente.',
            'data' => new UserResource($user->load('roles')) // Recargamos la relación para la respuesta
        ]);
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
            'estado' => ['sometimes', 'required', new Enum(UserStatusEnum::class)],
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
