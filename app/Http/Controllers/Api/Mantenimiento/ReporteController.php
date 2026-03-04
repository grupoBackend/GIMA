<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use App\Models\Mantenimiento;
use App\Http\Resources\ReporteResource; // 1. IMPORTAR EL NUEVO RESOURCE
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Enums\EstadoReporte;
use App\Enums\NivelPrioridad;
use App\Enums\TipoMantenimiento;

class ReporteController extends Controller
{
    /**
     * Listar reportes usando el Resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $query = Reporte::with(['usuario', 'activo']);

        //Filtro por busqueda
        $query->when($request->search, fn($q, $v) => $q->search($v));

        //Filtro por sede
        $query->when($request->sede_id, fn($q, $v) => $q->porSede($v));

        $usuario = Auth::user();

        $esUsuarioNormal = $usuario->hasRole('usuario');

        if ($esUsuarioNormal) {
            $query->where('usuario_id', Auth::id());
        }

        $reportes = $query->get();
        return ReporteResource::collection($reportes);
    }

    //Actualizar estado del reporte - Encargado de tarea: Sebastian Rodriguez (Lider: Juan Longart - Haddan Valencia)
    public function updateEstado(Request $request, $id)
    {
        $reporte = Reporte::findOrFail($id);

        $validated = $request->validate([
            'estado' => ['required', Rule::enum(EstadoReporte::class)],
        ]);

        $reporte->estado = $validated['estado'];
        $reporte->save();


        return (new ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Estado del reporte actualizado']);
    }

    //Actualizar prioridad del reporte - Encargado de tarea: Sebastian Rodriguez (Lider: Juan Longart - Haddan Valencia)
    public function updatePrioridad(Request $request, $id)
    {
        $reporte = Reporte::findOrFail($id);

        $validated = $request->validate([
            'prioridad' => ['required', Rule::enum(NivelPrioridad::class)],
        ]);

        $reporte->prioridad = $validated['prioridad'];
        $reporte->save();

        return (new ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Prioridad del reporte actualizada']);
    }

    //Asignar mantenimiento a un reporte - Encargado de tarea: Sebastian Rodriguez (Lider: Juan Longart - Haddan Valencia)
    public function asignarMantenimiento(Request $request, $id)
    {
        // Encontrar el reporte por ID
        $reporte = Reporte::findOrFail($id);

        // Validar los datos de entrada para asignar el mantenimiento
        $validated = $request->validate([
            'tecnico_id' => 'required|exists:users,id',
            'supervisor_id' => 'required|exists:users,id',
            'descripcion' => 'required|string|min:10',
            'tipo' => ['required', Rule::enum(TipoMantenimiento::class)],
        ]);

        // Crear un nuevo mantenimiento asociado al reporte
        \App\Models\Mantenimiento::create([
            'activo_id' => $reporte->activo_id,
            'reporte_id' => $reporte->id,
            'tecnico_principal_id' => $validated['tecnico_id'], // Se asigna el mismo tecnico como el principal
            'supervisor_id' => $validated['supervisor_id'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'fecha_apertura' => now(),
            'validado' => false, // El mantenimiento aún no ha sido validado
            'costo_total' => 0, // El costo se actualizará una vez que el mantenimiento se complete y se validen los costos reales
            'fecha_cierre' => now()->addDays(15), // Se asigna una fecha de cierre provisional de 15 dias a partir de la fecha de apertura.
                
                // Como en la tabla de mantenimientos, la fecha de cierre es obligatoria, 
                // pero se puede actualizar posteriormente cuando el mantenimiento se complete.
                // Por ahora, se asigna una fecha de cierre provisional
        ]);

        // Actualizar el estado del reporte a "asignado"
        $reporte->estado = EstadoReporte::ASIGNADO->value;
        $reporte->save();

        // Asignar el técnico al reporte 
        // (esto podría implicar crear un nuevo mantenimiento o actualizar el reporte)
        return (new ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Mantenimiento asignado al técnico']);
    }

    /**
     * Crear reporte y devolverlo transformado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'activo_id'   => 'required|exists:activos,id',
            'descripcion' => 'required|string|min:10',
            'prioridad'   => ['required', Rule::enum(NivelPrioridad::class)],
            'estado'      => ['required', Rule::enum(EstadoReporte::class)],
        ]);

        $reporte = Reporte::create([
            ...$validated,
            'usuario_id' => $request->user()->id,
        ]);

        return (new ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Reporte creado exitosamente']);
    }

    /**
     * Ver detalle con historial de mantenimientos.
     */
    public function show(Reporte $reporte): ReporteResource
    {
        return new ReporteResource($reporte->load(['usuario', 'activo', 'mantenimientos']));
    }

    /**
     * Actualizar reporte.
     */
    public function update(Request $request, Reporte $reporte)
    {
        $validated = $request->validate([
            'activo_id'   => 'sometimes|exists:activos,id',
            'descripcion' => 'sometimes|string|min:10',
            'prioridad'   => ['sometimes', Rule::enum(NivelPrioridad::class)],
            'estado'      => ['sometimes', Rule::enum(EstadoReporte::class)],
        ]);

        $reporte->update($validated);

        return (new ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Reporte actualizado']);
    }

    public function destroy(Reporte $reporte)
    {
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado']);
    }
}
