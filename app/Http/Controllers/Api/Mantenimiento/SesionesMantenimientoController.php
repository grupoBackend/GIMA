<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use App\Http\Controllers\Controller;
use App\Models\SesionesMantenimiento;
use App\Http\Resources\SesionMantenimientoResource; // Importar el nuevo Resource
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SesionesMantenimientoController extends Controller
{
/**
     * Listar sesiones usando el Resource y filtros dinámicos (Fase 3).
     * Encargado de tarea F3: Fender
     */
    public function index(Request $request): AnonymousResourceCollection // <--- SE INYECTA REQUEST
    {
        $sesiones = SesionesMantenimiento::with(['mantenimiento', 'tecnico'])
            // 1. Filtro por Mantenimiento (Sustituye a getByMantenimiento)
            ->when($request->mantenimiento_id, fn($q, $v) => $q->where('mantenimiento_id', $v))
            
            // 2. Filtro por Rango de Fechas
            ->when($request->fecha_inicio && $request->fecha_fin, function($q) use ($request) {
                $q->rangoFechas($request->fecha_inicio, $request->fecha_fin);
            })
            ->get(); // Si prefieren paginación, cambia get() por paginate(15)

        return SesionMantenimientoResource::collection($sesiones);
    }

    /**
     * Registrar sesión y devolver Resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mantenimiento_id'    => 'required|exists:mantenimientos,id',
            'tecnico_id'          => 'required|exists:users,id',
            'fecha'               => 'nullable|date',
            'horas_trabajadas'    => 'required|numeric|min:0.1',
            'descripcion_trabajo' => 'required|string|min:5',
            'observaciones'       => 'nullable|string',
            'costo_hora'         => 'nullable|numeric|min:0',
        ]);

        // Si no mandan fecha, ponemos la actual
        if (!isset($validated['fecha'])) {
            $validated['fecha'] = now();
        }

        $sesion = SesionesMantenimiento::create($validated);

        return (new SesionMantenimientoResource($sesion->load(['mantenimiento', 'tecnico'])))
            ->additional(['message' => 'Sesión de trabajo registrada']);
    }

    /**
     * Ver detalle con repuestos incluidos.
     */
    public function show(SesionesMantenimiento $sesion): SesionMantenimientoResource
    {
        return new SesionMantenimientoResource(
            $sesion->load(['mantenimiento', 'tecnico', 'repuestosUtilizados.repuesto'])
        );
    }

    public function update(Request $request, SesionesMantenimiento $sesion)
    {
        $validated = $request->validate([
            'fecha'               => 'sometimes|date',
            'horas_trabajadas'    => 'sometimes|numeric|min:0.1',
            'descripcion_trabajo' => 'sometimes|string',
            'observaciones'       => 'nullable|string',
            'costo_hora'         => 'sometimes|numeric',
        ]);

        $sesion->update($validated);

        return (new SesionMantenimientoResource($sesion))
            ->additional(['message' => 'Sesión actualizada correctamente']);
    }

    public function destroy(SesionesMantenimiento $sesion)
    {
        $sesion->delete();
        return response()->json(['message' => 'Sesión eliminada']);
    }
    // --- NUEVO MÉTODO FASE 3 ---

    /**
     * Finalizar una sesión (PATCH)
     * Encargado de tarea F3: Luismer
     */
    public function finalizar(Request $request, SesionesMantenimiento $sesion)
    {
        return (new SesionMantenimientoResource($sesion))
            ->additional(['message' => 'Sesión finalizada con éxito']);
    }
}
