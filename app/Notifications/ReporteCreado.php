<?php

namespace App\Notifications;

use App\Models\Reporte;
use Illuminate\Notifications\Notification;

class ReporteCreado extends Notification
{
    protected $reporte;

    // La notificacion recibe el reporte para poder obtener detalles del mismo
    public function __construct(Reporte $reporte)
    {
        $this->reporte = $reporte;
    }

    // Medios por los cuales se hara llegar la notificacion (SOLO en la base de datos)
    public function via($notifiable)
    {
        // ¡Aquí está la magia! Quitamos 'mail' y dejamos solo 'database'
        return ['database'];
    }

    // Metodo para guardar la notificacion en la tabla de notifications de laravel
    public function toArray($notifiable)
    {
        // Cargamos la relación activo si no existe
        $activoNombre = $this->reporte->activo->nombre ?? 'Activo Desconocido';
        $prioridad = $this->reporte->prioridad->value ?? $this->reporte->prioridad;

        return [
            'titulo' => 'Nuevo Reporte Registrado',
            'mensaje' => "Se ha registrado una nueva falla de prioridad {$prioridad} en el {$activoNombre}. Requiere revisión para asignar un técnico.",
            'reporte_id' => $this->reporte->id,
            'prioridad' => $prioridad,
            'activo_id' => $this->reporte->activo_id,
        ];
    }
}
