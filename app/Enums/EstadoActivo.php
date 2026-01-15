<?php

namespace App\Enums;

enum EstadoActivo : string
{
    case OPERATIVO = 'operativo';
    case MANTENIMIENTO = 'mantenimiento'; // Cambien la "i" por "I" porque es mayúscula en toda la palabra
    case FUERA_SERVICIO = 'fuera_servicio';
    case BAJA = 'baja';

    // Etiquetas legibles para cada estado
    public function label(): string
    {
        return match($this) {
            self::OPERATIVO => 'Operativo',
            self::MANTENIMIENTO => 'En mantenimiento',
            self::FUERA_SERVICIO => 'Fuera de Servicio',
            self::BAJA => 'Dado de baja'
        };
    }
}
