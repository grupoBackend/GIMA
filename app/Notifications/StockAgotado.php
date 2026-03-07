<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Repuesto;

class StockAgotado extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(Repuesto $repuesto)
    {
        $this->repuesto = $repuesto;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable)
    {
        return ['database'];
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
           'titulo' => '¡Alerta Roja! Stock Agotado',
            'mensaje' => "El repuesto {$this->repuesto->descripcion} (Código: {$this->repuesto->codigo}) se ha agotado completamente.",
            'repuesto_id' => $this->repuesto->id,
            'stock_actual' => 0,
        ];
    }
}
