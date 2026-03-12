<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MantenimientoAsignado extends Notification
{
    use Queueable;
    protected $mantenimiento;

   
public function __construct(Mantenimiento $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
    }

    public function via($notifiable)
    {
        return ['database']; // Se guarda en la tabla de notificaciones
    }

    public function toArray($notifiable)
    {
        $tipoMantenimiento = $this->mantenimiento->tipo->value ?? $this->mantenimiento->tipo;
        $activoNombre = $this->mantenimiento->activo->nombre ?? 'Activo Desconocido';

        return [
            'titulo' => 'Nueva Asignación',
            'mensaje' => "Se te ha asignado un nuevo {$tipoMantenimiento} (#{$this->mantenimiento->id}) para el {$activoNombre}. Por favor revisa tu jornada de trabajo.",
            'mantenimiento_id' => $this->mantenimiento->id,
            'tipo' => $tipoMantenimiento, 
        ];
    }


    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

}
