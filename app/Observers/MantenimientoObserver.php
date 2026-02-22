<?php

namespace App\Observers;

use App\Models\Mantenimiento;
use App\Notifications\MantenimientoAsignado;
use App\Notifications\MantenimientoCompletado;

class MantenimientoObserver
{
    /**
     * Se dispara cuando se crea un nuevo mantenimiento.
     */
    public function created(Mantenimiento $mantenimiento): void
    {
        // Notificar al técnico asignado al crear la orden [cite: 1]
        if ($mantenimiento->tecnico_principal_id) {
            $mantenimiento->tecnicoPrincipal->notify(new MantenimientoAsignado($mantenimiento));
        }
    }

    /**
     * Se dispara cuando se actualiza un mantenimiento existente.
     */
    public function updated(Mantenimiento $mantenimiento): void
    {
        // 1. Detección de reasignación de técnico [cite: 1, 13]
        if ($mantenimiento->isDirty('tecnico_principal_id')) {
            $mantenimiento->tecnicoPrincipal->notify(new MantenimientoAsignado($mantenimiento));
        }

        // 2. Notificación de Finalización (Técnico -> Supervisor) [cite: 1, 16, 23]
        // Se dispara si el estado cambia a 'Realizado' o 'Cerrado'
        if ($mantenimiento->isDirty('estado') && 
            in_array($mantenimiento->estado, ['Realizado', 'Cerrado'])) {
            
            $supervisor = $mantenimiento->supervisor; // Relación definida en tu modelo 
            
            if ($supervisor) {
                $supervisor->notify(new MantenimientoCompletado($mantenimiento));
            }
        }
    }
}