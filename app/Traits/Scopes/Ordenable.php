<?php

namespace App\Traits\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait Ordenable
{
    /**
     * Scope global para ordenar siempre por fecha de creación descendente.
     * Úsalo como: Modelo::recientes()->get();
     */
    public function scopeRecientes(Builder $query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Ordenamiento dinámico seguro.
     * Úsalo como: Modelo::ordenar('nombre', 'asc');
     */
    public function scopeOrdenar(Builder $query, $columna = 'created_at', $direccion = 'desc')
    {
        // Lista blanca de direcciones permitidas para evitar inyecciones SQL raras
        $direccion = in_array(strtolower($direccion), ['asc', 'desc']) ? $direccion : 'desc';
        
        return $query->orderBy($columna, $direccion);
    }
}