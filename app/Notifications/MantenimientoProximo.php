<?php

namespace App\Notifications;

use App\Models\Mantenimiento;
use Illuminate\Notifications\Notification;

class MantenimientoProximo extends Notification
{
    protected $mantenimiento;

    public function __construct(Mantenimiento $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'titulo' => 'Recordatorio: Mantenimiento Mañana',
            'mensaje' => "Mañana tienes programado el mantenimiento de: " . $this->mantenimiento->activo->nombre,
            'mantenimiento_id' => $this->mantenimiento->id,
            'fecha' => $this->mantenimiento->fecha_apertura->format('d/m/Y')
        ];
    }
}