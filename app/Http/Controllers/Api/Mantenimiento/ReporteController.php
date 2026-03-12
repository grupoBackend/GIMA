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
<<<<<<< Updated upstream
        $reportes = Reporte::with(['usuario', 'activo'])->get();
=======
        $query = Reporte::with(['usuario', 'activo.ubicacion']); // Se coloco el '.ubicacion' para cargar la relación de ubicación del activo, lo que nos permitirá mostrar la sede en el listado de reportes.

        //Filtro por busqueda
        $query->when($request->search, fn($q, $v) => $q->search($v));

        //Filtro por sede
        $query->when($request->sede_id, fn($q, $v) => $q->porSede($v));

        $usuario = Auth::user();

        /** @var User $usuario */
        $esUsuarioNormal = $usuario->hasRole('reporter');

        if ($esUsuarioNormal) {
            $query->where('usuario_id', Auth::id());
        }

        $reportes = $query->get();
>>>>>>> Stashed changes
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
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        if ($usuario->hasRole('reporter') && $reporte->usuario_id !== $usuario->id) {
            abort(403, 'Acceso denegado. Este reporte no te pertenece.');
        }

        return new ReporteResource($reporte->load(['usuario', 'activo', 'mantenimientos']));
    }

<<<<<<< Updated upstream
=======



    public function verMisReportes(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $direction = $request->query('direction', 'desc');
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : 'desc';

        $query = Reporte::with([
            'usuario',
            'activo.ubicacion',
            'mantenimientos.tecnicoPrincipal',
            'mantenimientos.supervisor',
        ])
            ->where('usuario_id', Auth::id());

        // Filtros: búsqueda libre, estado y prioridad
        $query->when($request->search, fn($q, $v) => $q->search($v));
        $query->when($request->estado, fn($q, $v) => $q->where(Rule::enum(EstadoReporte::class), $v));
        $query->when($request->prioridad, fn($q, $v) => $q->where(Rule::enum(NivelPrioridad::class), $v));

        // Ordenar por fecha de creación (default: desc)
        $query->orderBy('created_at', $direction);

        return ReporteResource::collection($query->paginate($perPage));
    }

>>>>>>> Stashed changes
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
