<?php

namespace App\Notifications;

use App\Models\Reporte;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage; 

class ReporteCreado extends Notification
{
    protected $reporte;

    //La notificacion recibe el reporte para poder obtener detalles del mismo
    public function __construct(Reporte $reporte)
    {
        $this->reporte = $reporte;
    }

    //Medios por los cuales se hara llegar la notificacion (mail y en la base de datos)
    public function via($notifiable)
    {
        return ['database', 'mail']; 
    }

    //Metodo para enviar la notificacion por mail
    public function toMail($notifiable)
    {
        // Cargar relaciones si no están cargadas
        if (!$this->reporte->relationLoaded('usuario')) {
            $this->reporte->load('usuario');
        }
        
        if (!$this->reporte->relationLoaded('activo')) {
            $this->reporte->load('activo');
        }

        $usuario = $this->reporte->usuario;
        $activo = $this->reporte->activo;
        
        // URL para ver el detalle del reporte en el mail 
        $url = url('/api/mantenimiento/reportes/' . $this->reporte->id);

        //Estructura del mensaje
        return (new MailMessage)
            ->subject('🔔 Nuevo Reporte Creado #' . $this->reporte->id)
            ->greeting('Hola ' . ($notifiable->name ?? 'Usuario') . ',')
            ->line('Se ha creado un nuevo reporte en el sistema que requiere atención.')
            ->line('---')
            ->line('**📋 Detalles del reporte:**')
            ->line('🆔 **ID:** ' . $this->reporte->id)
            ->line('📝 **Descripción:** ' . $this->reporte->descripcion)
            ->line('🔴 **Prioridad:** ' . $this->reporte->prioridad->value)
            ->line('📊 **Estado:** ' . $this->reporte->estado->value)
            ->line('👤 **Reportado por:** ' . ($usuario->name ?? 'Usuario no disponible'))
            ->line('💻 **Activo:** ' . ($activo->nombre ?? 'ID: ' . $this->reporte->activo_id))
            ->line('📅 **Fecha:** ' . $this->reporte->created_at->format('d/m/Y H:i'))
            ->line('---')
            ->action('🔍 Ver Reporte', $url);
    }

    //Metodo para guardar la notificacion en la tabla de notifications de laravel
    public function toArray($notifiable)
    {
        return [
            'reporte_id' => $this->reporte->id,
            'usuario_id' => $this->reporte->usuario_id,
            'descripcion' => $this->reporte->descripcion,
            'prioridad' => $this->reporte->prioridad,
            'activo_id' => $this->reporte->activo_id,
            'mensaje' => 'Nuevo reporte creado: ' . $this->reporte->descripcion,
        ];
    }
}