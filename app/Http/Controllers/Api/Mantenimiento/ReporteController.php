<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use App\Http\Resources\ReporteResource; // 1. IMPORTAR EL NUEVO RESOURCE
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use App\Enums\EstadoReporte;
use App\Enums\NivelPrioridad;

class ReporteController extends Controller
{
    /**
     * Listar reportes usando el Resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $reportes = Reporte::with(['usuario', 'activo'])->get();
        return ReporteResource::collection($reportes);
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