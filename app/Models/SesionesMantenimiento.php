<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Database\Factories\Mantenimiento\SesionesMantenimientoFactory;

class SesionesMantenimiento extends Model
{
    use HasFactory;

    protected $table = 'sesiones_mantenimiento';

        
    protected static function newFactory()
    {
        return SesionesMantenimientoFactory::new();
    }

    protected $fillable = [
        'mantenimiento_id',
        'tecnico_id',
        'fecha',
        'horas_trabajadas',
        'observaciones',
        'descripcion_trabajo',
        'costo_hora',
    ];


    protected $casts = [
        'fecha' => 'datetime',
        'horas_trabajadas' => 'decimal:2',
        'costo_hora' => 'decimal:2',
    ];

    
// --- SCOPES FASE 3 ---

    /**
     * Scope: Filtra por rango de fechas
     * (El documento pide created_at, pero si tu equipo prefiere buscar 
     * por la columna 'fecha' de la sesión, solo cambia 'created_at' por 'fecha')
     * Encargado de tarea F3: Fender
     */
    public function scopeRangoFechas(Builder $query, $inicio, $fin): Builder
    {
        return $query->whereBetween('created_at', [$inicio, $fin]);
    }

    // --- RELACIONES ---

    public function repuestosUtilizados() : HasMany
    {
        return $this->hasMany(RepuestoUsado::class, 'sesion_id');
    }

    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id');
    }
    
    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }
}
