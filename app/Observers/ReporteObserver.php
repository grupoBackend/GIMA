<?php

namespace App\Observers;

use App\Models\Reporte;
use App\Models\User;
use App\Notifications\ReporteCreado; // Usaremos esta clase
use Illuminate\Support\Facades\Notification;

class ReporteObserver 
{
    public function created(Reporte $reporte): void
    {
        // Corregido: admin y supervisor (eliminado 'gerente')
        $notificables = User::role(['admin', 'supervisor'])->get();

        if ($notificables->isNotEmpty()) {
            Notification::send($notificables, new ReporteCreado($reporte));
        }
    }
}