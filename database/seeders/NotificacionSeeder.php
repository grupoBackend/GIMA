<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notificacion;
use App\Models\User;

class NotificacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buscamos el primer usuario que exista en la BD para asignarle las notificaciones
        $usuario = User::first();

        // Si por casualidad la tabla de usuarios está vacía, no hacemos nada para evitar errores
        if (!$usuario) {
            $this->command->info('⚠️ No hay usuarios registrados. Crea un usuario primero para asignar notificaciones.');
            return;
        }

        // 2. Creamos notificaciones de prueba
        Notificacion::create([
            'usuario_id' => $usuario->id,
            'contenido'  => '¡Bienvenido al sistema GIMA! Esperamos que tu experiencia sea excelente.',
        ]);

        Notificacion::create([
            'usuario_id' => $usuario->id,
            'contenido'  => 'Recordatorio: Mañana hay mantenimiento programado de la plataforma a las 10:00 PM.',
        ]);

        Notificacion::create([
            'usuario_id' => $usuario->id,
            'contenido'  => 'Tu solicitud de cambio de dirección ha sido aprobada exitosamente.',
        ]);
    }
}