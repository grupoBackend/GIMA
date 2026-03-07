<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="General - Perfil",
 *     description="Endpoints para la gestión del perfil del usuario autenticado"
 * )
 */
class PerfilController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/general/perfil",
     *     summary="Obtener perfil del usuario autenticado",
     *     tags={"General - Perfil"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Perfil obtenido", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function show()
    {
        /** @var User $user */
        $user = Auth::user();
        return new UserResource($user->load('roles'));
    }

    /**
     * @OA\Put(
     *     path="/api/general/perfil",
     *     summary="Actualizar perfil del usuario autenticado",
     *     tags={"General - Perfil"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="telefono", type="string", nullable=true),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Perfil actualizado", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'telefono' => 'sometimes|nullable|string|max:20',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return new UserResource($user->load('roles'));
    }

    /**
     * @OA\Delete(
     *     path="/api/general/perfil",
     *     summary="Eliminar/limpiar datos del perfil autenticado",
     *     tags={"General - Perfil"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Datos eliminados correctamente"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function destroy()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'telefono' => null,
            'recovery_pin' => null,
        ]);

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }
}
