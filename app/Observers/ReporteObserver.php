<?php

namespace App\Observers;

use App\Models\Reporte;
use App\Models\User;
use App\Notifications\ReporteIncidente;
use Illuminate\Support\Facades\Notification;

// Cambia esto de "RepuestoObserver" a "ReporteObserver"
class ReporteObserver 
{
    public function created(Reporte $reporte): void
    {
        $notificables = User::role(['admin', 'gerente'])->get();

        if ($notificables->isNotEmpty()) {
            Notification::send($notificables, new ReporteIncidente($reporte));
        }
    }
}