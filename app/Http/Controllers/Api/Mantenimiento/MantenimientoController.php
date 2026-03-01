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
            $mantenimientos = Mantenimiento::with(['activo', 'tecnicoPrincipal']) // Ajusta tus relaciones
                ->when($request->search, fn($q, $v) => $q->search($v))
                ->when($request->sede_id, fn($q, $v) => $q->porSede($v))
                ->when($request->solo_activos, fn($q) => $q->activos())
                ->when($request->tecnico_id, fn($q, $v) => $q->where('tecnico_principal_id', $v))
                ->paginate(15);

            return response()->json($mantenimientos);
        }
    //Encargada de tarea: Sani
    public function asignarTecnico(Request $request, $id)
    {
        $request->validate(['tecnico_id' => 'required|exists:users,id']);
        
        $mantenimiento = Mantenimiento::findOrFail($id);
        // Usamos el nombre real de tu columna en BD
        $mantenimiento->tecnico_principal_id = $request->tecnico_id; 
        $mantenimiento->save();

        return response()->json(['message' => 'Técnico asignado correctamente', 'data' => $mantenimiento]);
    }
    //Encargada de tarea: Jhoanny
    public function cambiarEstado(Request $request, $id)
    {
        // Validamos usando tu Enum para máxima seguridad
        $request->validate([
            'estado' => ['required', Rule::enum(EstadoMantenimiento::class)]
        ]);
        
        $mantenimiento = Mantenimiento::findOrFail($id);
        $mantenimiento->estado = $request->estado;
        $mantenimiento->save();

        return response()->json(['message' => 'Estado actualizado', 'data' => $mantenimiento]);
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
