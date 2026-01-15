<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 * name="Autenticación",
 * description="Endpoints para el acceso y gestión de sesiones de usuario"
 * )
 */
class AuthController extends Controller
{

    /**
     * @OA\Post(
     * path="/api/autenticacion/iniciar-sesion",
     * summary="Iniciar sesión de usuario",
     * tags={"Autenticación"},
     * @OA\RequestBody(
     * required=true,
     * description="Credenciales de acceso",
     * @OA\JsonContent(
     * required={"email", "password"},
     * @OA\Property(property="email", type="string", format="email", example="usuario@ejemplo.com"),
     * @OA\Property(property="password", type="string", format="password", example="secret123")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Login exitoso",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="estado", type="string", example="exito"),
     * @OA\Property(property="mensaje", type="string", example="Login correcto"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="usuario", type="string", example="Juan Perez"),
     * @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"admin"}),
     * @OA\Property(property="token", type="string", example="1|abc123token...")
     * )
     * )
     * ),
     * @OA\Response(response=401, description="Credenciales inválidas"),
     * @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function login(Request $request)
    {

        // Loguear el contenido de la solicitud
        Log::info('Datos recibidos en login:', $request->all());

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Credenciales inválidas',
                'recibe' => $request->all()
            ], 401);
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $token = $user->createToken('token-acceso-api')->plainTextToken;

        return response()->json([
            'estado' => 'exito',
            'mensaje' => 'Login correcto',
            'data' => [
                'usuario' => $user->name,
                'roles' => $user->getRoleNames(),
                'token' => $token
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     * path="/api/autenticacion/registro",
     * summary="Registrar un nuevo usuario",
     * tags={"Autenticación"},
     * @OA\RequestBody(
     * required=true,
     * description="Datos necesarios para el registro",
     * @OA\JsonContent(
     * required={"name", "email", "password", "password_confirmation"},
     * @OA\Property(property="name", type="string", example="Nuevo Usuario"),
     * @OA\Property(property="email", type="string", format="email", example="nuevo@ejemplo.com"),
     * @OA\Property(property="password", type="string", format="password", minLength=8),
     * @OA\Property(property="password_confirmation", type="string", format="password", minLength=8)
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Usuario registrado correctamente",
     * @OA\JsonContent(
     * @OA\Property(property="estado", type="string", example="exito"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="usuario", type="string"),
     * @OA\Property(property="email", type="string")
     * )
     * )
     * ),
     * @OA\Response(response=422, description="Error de validación (email ya existe o password no coincide)")
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'estado' => 'exito',
            'mensaje' => 'Usuario registrado correctamente',
            'data' => [
                'usuario' => $user->name,
                'email' => $user->email,
            ]
        ], 201);
    }

    /**
     * @OA\Get(
     * path="/api/autenticacion/perfil",
     * summary="Obtener información del perfil autenticado",
     * tags={"Autenticación"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Datos del usuario obtenidos exitosamente",
     * @OA\JsonContent(
     * @OA\Property(property="estado", type="string", example="exito"),
     * @OA\Property(property="data", ref="#/components/schemas/User"),
     * @OA\Property(property="roles_asignados", type="array", @OA\Items(type="string"))
     * )
     * ),
     * @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function perfil(Request $request)
    {
        return response()->json([
            'estado' => 'exito',
            'data' => $request->user(),
            'roles_asignados' => $request->user()->getRoleNames()
        ]);
    }
}
