<?php

namespace App\Notifications;

use App\Models\Mantenimiento;
use Illuminate\Notifications\Notification;

class MantenimientoVencido extends Notification
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
            'titulo' => '¡ALERTA ROJA! Mantenimiento Vencido',
            'mensaje' => "El mantenimiento del activo {$this->mantenimiento->activo->nombre} está vencido desde el " . $this->mantenimiento->fecha_apertura->format('d/m/Y'),
            'mantenimiento_id' => $this->mantenimiento->id,
            'urgencia' => 'Alta'
        ];
    }
}