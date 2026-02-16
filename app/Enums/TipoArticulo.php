<?php

namespace App\Enums;

enum TipoArticulo: string
{
    case MOBILIARIO = 'mobiliario';
    case EQUIPO = 'equipo';

    // Etiquetas legibles para cada estado
    public function label(): string
    {
        return match ($this) {
            self::MOBILIARIO => 'mobiliario',
            self::EQUIPO => 'equipo',
        };
    }
}
