<?php

namespace App\Observers;

use App\Models\Repuesto;
use App\Models\User;
use App\Notifications\StockAgotado;
use App\Notifications\StockBajo;
use App\Notifications\RepuestoRecibido;
use Illuminate\Support\Facades\Notification;

class RepuestoObserver
{
    public function updated(Repuesto $repuesto): void
    {
        // 1. Lógica de Stock Agotado (Llega a 0)
        if ($repuesto->isDirty('stock') && $repuesto->stock <= 0) {
            $notificables = User::role(['admin', 'compras'])->get();
            if ($notificables->isNotEmpty()) {
                Notification::send($notificables, new StockAgotado($repuesto));
            }
            return; // Salimos para no duplicar con la de stock bajo
        }

        // 2. Lógica de Stock Bajo (Menor o igual al mínimo) 
        if ($repuesto->isDirty('stock') && $repuesto->stock <= $repuesto->stock_minimo) {
            // Notificar a Almacenista y Admin 
            $notificables = User::role(['admin', 'almacenista'])->get();
            
            if ($notificables->isNotEmpty()) {
                Notification::send($notificables, new StockBajo($repuesto));
            }
        }

        // 3. Lógica de Repuesto Recibido (Cuando aumenta el stock)
        if ($repuesto->isDirty('stock') && $repuesto->stock > $repuesto->getOriginal('stock')) {
            $cantidadRecibida = $repuesto->stock - $repuesto->getOriginal('stock');
            $tecnicos = User::role('tecnico')->get();
            if ($tecnicos->isNotEmpty()) {
                Notification::send($tecnicos, new RepuestoRecibido($repuesto, $cantidadRecibida));
            }
        }
    }
}