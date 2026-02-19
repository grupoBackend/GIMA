<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use App\Http\Controllers\Controller;
use App\Models\RepuestoUsado;
<<<<<<< HEAD
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // Importamos el validador
=======
use App\Http\Resources\RepuestoUsadoResource; // Importar el Resource
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
>>>>>>> 25dc4c84fe3576aa6731c747f50f86a6c24a832f

class RepuestoUsadoController extends Controller
{
    /**
<<<<<<< HEAD
     * Display a listing of the resource.
     * Muestra todos los repuestos usados (opcionalmente filtrados).
     */
    public function index()
    {
        // Traemos todos los registros cargando la relación 'repuesto' para ver detalles
        // Esto es útil para listar todo lo consumido.
        $repuestosUsados = RepuestoUsado::with('repuesto')->get();

        return response()->json([
            'status' => true,
            'data' => $repuestosUsados,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     * Guarda un nuevo registro de repuesto usado.
     */
    public function store(Request $request)
    {
        // 1. Validación según tu guía: "La cantidad debe ser mayor a 0"
        $validator = Validator::make($request->all(), [
=======
     * Listar consumos usando el Resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $usos = RepuestoUsado::with(['sesion', 'repuesto'])->get();
        return RepuestoUsadoResource::collection($usos);
    }

    /**
     * Registrar uso y devolver Resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
>>>>>>> 25dc4c84fe3576aa6731c747f50f86a6c24a832f
            'sesion_id'   => 'required|exists:sesiones_mantenimiento,id',
            'repuesto_id' => 'required|exists:repuestos,id',
            'cantidad'    => 'required|numeric|min:0.01',
            'costo_total' => 'nullable|numeric|min:0',
        ]);

<<<<<<< HEAD
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Crear el registro
        $repuestoUsado = RepuestoUsado::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Repuesto usado registrado exitosamente.',
            'data' => $repuestoUsado
        ], 201);
    }

    /**
     * Display the specified resource.
     * Muestra un registro específico.
     */
    public function show(RepuestoUsado $repuestoUsado)
    {
        // REQUISITO DE LA GUÍA: Cargar siempre la relación repuesto
        // para conocer nombre y código.
        $repuestoUsado->load('repuesto');

        return response()->json([
            'status' => true,
            'data' => $repuestoUsado
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     * Actualiza la cantidad usada.
     */
    public function update(Request $request, RepuestoUsado $repuestoUsado)
    {
        // Validamos nuevamente que la cantidad sea lógica
        $validator = Validator::make($request->all(), [
            'cantidad' => 'integer|min:1', // REQUISITO DE LA GUÍA
            'repuesto_id' => 'exists:repuestos,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Actualizamos
        $repuestoUsado->update($request->all());

        // Recargamos la relación para mostrar la data completa actualizada
        $repuestoUsado->load('repuesto');

        return response()->json([
            'status' => true,
            'message' => 'Registro de repuesto actualizado.',
            'data' => $repuestoUsado
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * Elimina el registro.
     */
    public function destroy(RepuestoUsado $repuestoUsado)
    {
        // Integridad: La guía menciona onDelete('cascade') en la migración.
        // Aquí borramos el registro individualmente.
        $repuestoUsado->delete();

        return response()->json([
            'status' => true,
            'message' => 'Registro de repuesto usado eliminado correctamente.'
        ], 200); // O 204 si prefieres no devolver contenido
    }
}
=======
        $uso = RepuestoUsado::create($validated);

        return (new RepuestoUsadoResource($uso->load(['repuesto'])))
                ->additional(['message' => 'Repuesto registrado en la sesión']);
    }

    public function show(RepuestoUsado $repuestoUsado): RepuestoUsadoResource
    {
        return new RepuestoUsadoResource($repuestoUsado->load(['sesion', 'repuesto']));
    }

    public function update(Request $request, RepuestoUsado $repuestoUsado)
    {
        $validated = $request->validate([
            'cantidad'    => 'sometimes|numeric|min:0.01',
            'costo_total' => 'sometimes|numeric|min:0',
        ]);

        $repuestoUsado->update($validated);

        return (new RepuestoUsadoResource($repuestoUsado))
                ->additional(['message' => 'Registro de repuesto actualizado']);
    }

    public function destroy(RepuestoUsado $repuestoUsado)
    {
        $repuestoUsado->delete();
        return response()->json(['message' => 'Registro de uso eliminado']);
    }
}

>>>>>>> 25dc4c84fe3576aa6731c747f50f86a6c24a832f
