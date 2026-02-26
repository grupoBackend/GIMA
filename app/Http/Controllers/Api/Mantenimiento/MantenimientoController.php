<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use App\Http\Controllers\Controller;
use App\Models\Mantenimiento;
use App\Http\Resources\MantenimientoResource; 
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Enums\EstadoMantenimiento;
use App\Enums\TipoMantenimiento;
use Illuminate\Validation\Rule;

class MantenimientoController extends Controller
{
    /**
     * Listar mantenimientos con sus relaciones usando el Resource.
     */
    public function index(Request $request)
    {
        $mantenimientos = Mantenimiento::with(['activo', 'reporte', 'tecnicoPrincipal']) // Eager Loading
            ->filtrar($request->all()) // <--- Filtros activados
            ->paginate(10);

        return MantenimientoResource::collection($mantenimientos);
    }

    /**
     * Guardar un nuevo mantenimiento.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'activo_id'            => 'required|exists:activos,id',
            'supervisor_id'        => 'required|exists:users,id',
            'tecnico_principal_id' => 'required|exists:users,id',
            'tipo'                 => ['required', Rule::enum(TipoMantenimiento::class)],
            'reporte_id'           => 'nullable|exists:reportes,id',
            'fecha_apertura'       => 'required|date',
            'fecha_cierre'         => 'required|date|after_or_equal:fecha_apertura',
            'estado'               => ['required', Rule::enum(EstadoMantenimiento::class)],
            'descripcion'          => 'required|string|min:10',
            'validado'             => 'required|boolean',
            'costo_total'          => 'required|numeric|min:0',
        ]);

        $mantenimiento = Mantenimiento::create($validated);

        return (new MantenimientoResource($mantenimiento->load(['activo', 'supervisor', 'tecnicoPrincipal', 'reporte'])))
            ->additional(['message' => 'Registro de mantenimiento creado exitosamente']);
    }

    /**
     * Ver un mantenimiento a detalle.
     */
    public function show(Mantenimiento $mantenimiento): MantenimientoResource
    {
        return new MantenimientoResource($mantenimiento->load([
            'activo',
            'supervisor',
            'tecnicoPrincipal',
            'reporte',
            'sesiones'
        ]));
    }

    /**
     * Actualizar datos del mantenimiento.
     */
    public function update(Request $request, Mantenimiento $mantenimiento)
    {
        $validated = $request->validate([
            'activo_id'            => 'sometimes|exists:activos,id',
            'supervisor_id'        => 'sometimes|exists:users,id',
            'tecnico_principal_id' => 'sometimes|exists:users,id',
            'tipo'                 => ['sometimes', Rule::enum(TipoMantenimiento::class)],
            'reporte_id'           => 'nullable|exists:reportes,id',
            'fecha_apertura'       => 'sometimes|date',
            'fecha_cierre'         => 'sometimes|date|after_or_equal:fecha_apertura',
            'estado'               => ['sometimes', Rule::enum(EstadoMantenimiento::class)],
            'descripcion'          => 'sometimes|string',
            'validado'             => 'sometimes|boolean',
            'costo_total'          => 'sometimes|numeric',
        ]);

        $mantenimiento->update($validated);

        return (new MantenimientoResource($mantenimiento->load(['activo', 'supervisor', 'tecnicoPrincipal', 'reporte'])))
            ->additional(['message' => 'Mantenimiento actualizado con éxito']);
    }

    public function destroy(Mantenimiento $mantenimiento)
    {
        $mantenimiento->delete();
        return response()->json(['message' => 'Registro eliminado']);
    }
}
