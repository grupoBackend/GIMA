<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use App\Http\Controllers\Controller;
use App\Models\RepuestoUsado;
use App\Http\Resources\RepuestoUsadoResource; // Importar el Resource
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RepuestoUsadoController extends Controller
{
    /**
     * Listar consumos usando el Resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RepuestoUsado::with(['sesion', 'repuesto']);

        // Filtro: Historial de un repuesto específico
        $query->when($request->repuesto_id, fn($q, $v) => $q->where('repuesto_id', $v));

        // Filtro: Repuestos usados en una sesión específica
        $query->when($request->sesion_id, fn($q, $v) => $q->where('sesion_id', $v));

        return RepuestoUsadoResource::collection($query->get());
    }

    /**
     * Registrar uso y devolver Resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sesion_id'   => 'required|exists:sesiones_mantenimiento,id',
            'repuesto_id' => 'required|exists:repuestos,id',
            'cantidad'    => 'required|numeric|min:0.01',
            'costo_total' => 'nullable|numeric|min:0',
        ]);

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

