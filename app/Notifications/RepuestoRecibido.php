<?php

namespace App\Notifications;

use App\Models\Repuesto;
use Illuminate\Notifications\Notification;

class RepuestoRecibido extends Notification
{
    protected $repuesto;
    protected $cantidad;

    public function __construct(Repuesto $repuesto, $cantidad)
    {
        $this->repuesto = $repuesto;
        $this->cantidad = $cantidad;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'titulo' => 'Nuevo Ingreso de Repuesto',
            'mensaje' => "Se han recibido {$this->cantidad} unidades de: {$this->repuesto->descripcion}.",
            'repuesto_id' => $this->repuesto->id,
            'codigo' => $this->repuesto->codigo
        ];
    }
}