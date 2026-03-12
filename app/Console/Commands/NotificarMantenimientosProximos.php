<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mantenimiento;
use App\Notifications\MantenimientoProximo;
use App\Notifications\MantenimientoVencido;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class NotificarMantenimientosProximos extends Command
{
    protected $signature = 'app:notificar-mantenimientos'; // Cambiamos el nombre para que sea más general
    protected $description = 'Gestiona recordatorios y alertas de vencimiento de mantenimientos';

    public function handle()
    {
        $hoy = Carbon::today();
        $manana = Carbon::tomorrow()->toDateString();

        // 1. RECORDATORIOS (24h antes)
        $proximos = Mantenimiento::whereDate('fecha_apertura', $manana)
            ->where('estado', 'Pendiente')
            ->get();

        foreach ($proximos as $m) {
            if ($m->tecnicoPrincipal) $m->tecnicoPrincipal->notify(new MantenimientoProximo($m));
            if ($m->supervisor) $m->supervisor->notify(new MantenimientoProximo($m));
        }

        // 2. VENCIDOS (Fecha pasada y sigue Pendiente)
        $vencidos = Mantenimiento::whereDate('fecha_apertura', '<', $hoy)
            ->where('estado', 'Pendiente')
            ->get();

        if ($vencidos->isNotEmpty()) {
            // Notificar a Admin y Supervisor
            $admins = User::role(['admin', 'supervisor'])->get();

            foreach ($vencidos as $v) {
                Notification::send($admins, new MantenimientoVencido($v));
            }
        }

        $this->info('Proceso de notificaciones completado.');
    }
}