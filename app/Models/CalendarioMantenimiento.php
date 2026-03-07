<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// Importar Enums 
use App\Enums\EstadoMantenimiento;
use App\Enums\TipoMantenimiento;
use App\Models\Activo;
use App\Models\User;
use Database\Factories\Mantenimiento\CalendarioMantenimientoFactory;
use Illuminate\Database\Eloquent\Builder;

class CalendarioMantenimiento extends Model
{
    use HasFactory;

    protected $table = 'calendario_mantenimientos';

    protected static function newFactory()
    {
        return CalendarioMantenimientoFactory::new();
    }

    protected $fillable = [
        'activo_id',
        'tecnico_asignado_id',
        'tipo',
        'fecha_programada',
        'estado',
    ];

    /**
     * Casting de tipos para fechas y Enums
     */
    protected $casts = [
        'fecha_programada' => 'datetime',
        'tipo' => TipoMantenimiento::class,
        'estado' => EstadoMantenimiento::class,
    ];

    //Relacion inversa con el modelo Activo
    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    //relación inversa con el modelo User como técnico asignado
    public function tecnicoAsignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_asignado_id');
    }

    // --- SCOPES PARA FILTRADO AVANZADO ---
    // Scope para próximos mantenimientos
    public function scopeProximos(Builder $query): Builder
    {
        return $query->where('fecha_programada', '>=', now());
    }

    // Filtro por Sede (asumiendo que Activo tiene relación con Ubicación y esta con Sede)
    public function scopePorSede(Builder $query, $sedeId): Builder
    {
        // Asumiendo que Activo tiene relacion 'ubicacion' y Ubicacion tiene 'sede_id'
        return $query->whereHas('activo.ubicacion', function ($q) use ($sedeId) {
            $q->where('sede_id', $sedeId);
        });
    }
}