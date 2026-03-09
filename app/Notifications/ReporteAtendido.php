<?php

namespace App\Notifications;

use App\Models\Reporte;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReporteAtendido extends Notification
{
    use Queueable;

    protected $reporte;
    protected $tecnico;

    public function __construct(Reporte $reporte, User $tecnico)
    {
        $this->reporte = $reporte;
        $this->tecnico = $tecnico;
    }

    public function via($notifiable)
    {
        return ['database']; 
    }

    public function toArray($notifiable)
    {
        $activoNombre = $this->reporte->activo->nombre ?? 'Activo Desconocido';

        return [
            'titulo' => 'Tu reporte está en proceso',
            'mensaje' => "Tu reporte sobre el {$activoNombre} está siendo atendido por el técnico {$this->tecnico->name}.",
            'reporte_id' => $this->reporte->id,
        ];
    }
}