<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mantenimiento;
use Illuminate\Support\Facades\Auth;

class TecnicoDashboardController extends Controller
{

    /**
     * @OA\Get(
     * path="/api/dashboard/tecnico",
     * summary="Obtener datos del dashboard del técnico",
     * description="Retorna los KPIs (Alertas, En proceso, Completados) y la lista de próximos mantenimientos para el técnico logueado.",
     * operationId="getTecnicoDashboard",
     * tags={"Dashboard Técnico"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Operación exitosa",
     * @OA\JsonContent(
     * @OA\Property(property="tecnico", type="object",
     * @OA\Property(property="nombre", type="string", example="Juan Perez")
     * ),
     * @OA\Property(property="kpis", type="object",
     * @OA\Property(property="alertas_criticas", type="integer", example=10),
     * @OA\Property(property="en_proceso", type="integer", example=20),
     * @OA\Property(property="completados", type="integer", example=40)
     * ),
     * @OA\Property(property="lista_mantenimientos", type="array",
     * @OA\Items(
     * @OA\Property(property="codigo", type="string", example="#1234"),
     * @OA\Property(property="equipo", type="string", example="ACTIVO 145"),
     * @OA\Property(property="descripcion", type="string", example="Restauración de conectores"),
     * @OA\Property(property="prioridad", type="string", example="ALTA"),
     * @OA\Property(property="estado", type="string", example="EN_PROCESO"),
     * @OA\Property(property="responsable", type="string", example="Pedro Perez")
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="No autorizado"
     * )
     * )
     */

    public function index(Request $request)
    {
        // 1. Obtenemos el ID del técnico que inició sesión
        $tecnicoId = Auth::id() ?? 1;

        // =========================================================
        // BLOQUE 1: KPIs (Tarjetas Superiores)
        // =========================================================

        // Contamos los en proceso
        $enProceso = Mantenimiento::where('tecnico_principal_id', $tecnicoId)
            ->where('estado', 'en_proceso')
            ->count();

        // Contamos los completados
        $completados = Mantenimiento::where('tecnico_principal_id', $tecnicoId)
            ->where('estado', 'completado')
            ->count();

        // Alertas Críticas: Mantenimientos NO completados que provienen de un Reporte con prioridad 'alta'
        $alertasCriticas = Mantenimiento::where('tecnico_principal_id', $tecnicoId)
            ->where('estado', '!=', 'completado')
            ->whereHas('reporte', function ($query) {
                $query->where('prioridad', 'alta'); //
            })->count();


        // =========================================================
        // BLOQUE 2: Lista formateada con map()
        // =========================================================
        $mantenimientos = Mantenimiento::with(['activo', 'reporte', 'tecnicoPrincipal'])
            ->where('tecnico_principal_id', $tecnicoId)
            ->whereIn('estado', ['pendiente', 'en_proceso', 'completado'])

            // --- ¡ESTE ES EL CAMBIO PARA POSTGRESQL! ---
            ->orderByRaw("
                CASE estado 
                    WHEN 'en_proceso' THEN 1 
                    WHEN 'pendiente' THEN 2 
                    WHEN 'completado' THEN 3 
                    ELSE 4 
                END
            ")
            // -------------------------------------------

            ->take(10)
            ->get()
            ->map(function ($mant) {
                return [
                    'codigo' => '#' . str_pad($mant->id, 4, '0', STR_PAD_LEFT),
                    'equipo' => 'ACTIVO ' . $mant->activo_id,
                    'descripcion' => $mant->descripcion ?? 'Mantenimiento ' . ucfirst($mant->tipo),
                    'prioridad' => $mant->reporte ? strtoupper($mant->reporte->prioridad) : 'MEDIA',
                    'estado' => strtoupper($mant->estado->value),
                    'responsable' => $mant->tecnicoPrincipal->name ?? 'Sin asignar'
                ];
            });


        // =========================================================
        // RESPUESTA FINAL JSON PARA EL FRONTEND
        // =========================================================
        return response()->json([
            'tecnico' => [
                'nombre' => Auth::user()->name ?? 'Técnico de Prueba'
            ],
            'kpis' => [
                'alertas_criticas' => $alertasCriticas,
                'en_proceso' => $enProceso,
                'completados' => $completados
            ],
            'lista_mantenimientos' => $mantenimientos
        ]);
    }
}
