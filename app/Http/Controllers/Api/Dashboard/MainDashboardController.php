<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Mantenimiento;
use App\Models\Repuesto;
use App\Models\User;
use App\Models\Activo;
use App\Models\CalendarioMantenimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class MainDashboardController extends Controller
{
    public function estadisticasGenerales()
    {
        // 1. Mantenimientos Activos (12)
        $mantenimientosActivos = Mantenimiento::whereIn('estado', ['en_proceso', 'pendiente'])->count();


        // 2. Repuestos en Stock (85)
        // Sumamos la cantidad de todos los repuestos (o contamos los tipos de repuestos, según tu lógica)
        $repuestosStock = Repuesto::sum('stock');

        // 3. Técnicos Disponibles (5 / 8)
        // A. Total de técnicos registrados
        $totalTecnicos = User::porRol('tecnico')->estado('activo')->count();

        // B. Técnicos ocupados (tienen un mantenimiento activo)
        $tecnicosOcupados = User::porRol('tecnico')
            ->estado('activo')
            ->whereHas('mantenimientosTecnicoPrincipal', function ($q) {
                $q->where('estado', 'en_proceso');
            })
            ->count();

        // C. Los disponibles son la resta
        $tecnicosDisponibles = $totalTecnicos - $tecnicosOcupados;

        // Entregamos todo empaquetado al frontend
        return response()->json([
            'mantenimientos_activos' => $mantenimientosActivos,
            'repuestos_stock' => $repuestosStock,
            'tecnicos' => [
                'disponibles' => $tecnicosDisponibles,
                'total' => $totalTecnicos
            ]
        ]);
    }


    public function barraActivos()
    {

        $totalActivos = Activo::count();

        // Si no hay activos, evitamos la división por cero más adelante
        if ($totalActivos === 0) {
            return response()->json(['mensaje' => 'No hay datos suficientes']);
        }

        // =========================================================
        // 1. Estado de activos (Las barras de progreso horizontales)
        // =========================================================
        // Agrupamos cuántos activos hay por cada estado
        $estados = Activo::select('estado', DB::raw('count(id) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado'); // Crea un array clave => valor ['operativo' => 50, 'en_mantenimiento' => 10]

        $barrasEstado = [
            'operativo' => round((($estados['operativo'] ?? 0) / $totalActivos) * 100),
            'mantenimiento' => round((($estados['mantenimiento'] ?? 0) / $totalActivos) * 100),
            'fuera_servicio' => round((($estados['fuera_servicio'] ?? 0) / $totalActivos) * 100),
        ];



        // =========================================================
        // 2. Alerta Inteligente (El cuadro rojo abajo)
        // =========================================================
        $alerta = null;
        if ($barrasEstado['mantenimiento'] > 8) { // Si sube del 8% mandamos alerta
            $alerta = "Atención: El porcentaje de equipos en mantenimiento ha subido a {$barrasEstado['mantenimiento']}%. Revise las órdenes de trabajo pendientes.";
        }

        return response()->json([
            'estado_activos' => $barrasEstado,
            'alerta_sistema' => $alerta
        ]);
    }

    public function agendaProxima()
    {
        $proximos = CalendarioMantenimiento::where('estado', 'pendiente')
            ->where('fecha_programada', '>=', now())
            ->orderBy('fecha_programada', 'asc')
            ->take(3)
            ->get()
            ->map(function ($cita) {


                $tituloDinamico = 'Mantenimiento ' . ucfirst($cita->tipo->value) . ' (Activo #' . $cita->activo_id . ')';

                $tecnico = User::find($cita->tecnico_asignado_id);

                return [
                    'id' => $cita->id,
                    'titulo' => $tituloDinamico,
                    'fecha' => \Carbon\Carbon::parse($cita->fecha_programada)->format('d M - h:i A'),
                    'tecnico_nombre' => $tecnico ? $tecnico->name : 'Sin asignar',
                    'tecnico_id' => $cita->tecnico_asignado_id
                ];
            });

        return response()->json($proximos);
    }
}
