<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\TipoMantenimiento;
use Illuminate\Http\JsonResponse;
use App\Enums\EstadoMantenimiento;
use App\Http\Controllers\Controller;
use App\Models\CalendarioMantenimiento;
use App\Http\Resources\CalendarioMantenimientoResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CalendarioMantenimientoController extends Controller
{
    /**
     * Listar todos los eventos programados.
     */
    public function index(): AnonymousResourceCollection
    {
        $eventos = CalendarioMantenimiento::with(['activo', 'tecnicoAsignado'])->get();
        // Usamos el Resource para la colección
        return CalendarioMantenimientoResource::collection($eventos);
    }

    /**
     * Mostrar un evento específico.
     */
    public function show(CalendarioMantenimiento $calendarioMantenimiento): CalendarioMantenimientoResource
    {
        // Cargamos relaciones y devolvemos el Resource
        return new CalendarioMantenimientoResource($calendarioMantenimiento->load(['activo', 'tecnicoAsignado']));
    }

    /**
     * Crear un nuevo evento.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'activo_id'           => 'required|exists:activos,id',
            'tecnico_asignado_id' => 'required|exists:users,id',
            'tipo'                => ['required', Rule::enum(TipoMantenimiento::class)],
            'fecha_programada'    => 'required|date|after_or_equal:today',
            'estado'              => ['required', Rule::enum(EstadoMantenimiento::class)],
        ]);

        $evento = CalendarioMantenimiento::create($validated);

        return (new CalendarioMantenimientoResource($evento->load(['activo', 'tecnicoAsignado'])))
            ->additional(['message' => 'Evento programado exitosamente']);
    }

    /**
     * Actualizar un evento existente.
     */
    public function update(Request $request, CalendarioMantenimiento $calendarioMantenimiento)
    {
        $validated = $request->validate([
            'activo_id'           => 'sometimes|exists:activos,id',
            'tecnico_asignado_id' => 'sometimes|exists:users,id',
            'tipo'                => ['sometimes', Rule::enum(TipoMantenimiento::class)],
            'fecha_programada'    => 'sometimes|date',
            'estado'              => ['sometimes', Rule::enum(EstadoMantenimiento::class)],
        ]);

        $calendarioMantenimiento->update($validated);

        return (new CalendarioMantenimientoResource($calendarioMantenimiento->load(['activo', 'tecnicoAsignado'])))
            ->additional(['message' => 'Evento actualizado correctamente']);
    }


    /**
     * Eliminar un evento del sistema.
     * DELETE /api/mantenimiento/calendario/{calendarioMantenimiento}
     */
    public function destroy(CalendarioMantenimiento $calendarioMantenimiento): JsonResponse
    {
        $calendarioMantenimiento->delete();
        return response()->json([
            'message' => 'Evento eliminado del calendario con éxito'
        ]);
    }
}
