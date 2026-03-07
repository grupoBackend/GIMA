<?php

namespace App\Notifications;

use App\Models\Reporte;
use Illuminate\Notifications\Notification;

class ReporteIncidente extends Notification
{
    protected $reporte;

    public function __construct(Reporte $reporte)
    {
        $this->reporte = $reporte;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'titulo' => 'Nuevo Incidente Reportado',
            'mensaje' => "Se ha reportado una falla en el activo: " . $this->reporte->activo->nombre,
            'prioridad' => $this->reporte->prioridad,
            'reporte_id' => $this->reporte->id,
            'usuario' => $this->reporte->usuario->name
        ];
    }
}