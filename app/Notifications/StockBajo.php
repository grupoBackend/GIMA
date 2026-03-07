<?php

namespace App\Notifications;

use App\Models\Repuesto;
use Illuminate\Notifications\Notification;

class StockBajo extends Notification
{
    protected $repuesto;

    public function __construct(Repuesto $repuesto)
    {
        $this->repuesto = $repuesto;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'titulo' => 'Advertencia: Stock Bajo',
            'mensaje' => "El repuesto {$this->repuesto->descripcion} ha bajado del mínimo permitido ({$this->repuesto->stock_minimo}).",
            'repuesto_id' => $this->repuesto->id,
            'stock_actual' => $this->repuesto->stock,
            'modulo' => 'Inventario'
        ];
    }
}