<?php

namespace App\Models;

use App\Enums\EstadoMantenimiento;
use App\Enums\TipoMantenimiento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Activo;
use App\Models\User;
use App\Models\Reporte;
use Database\Factories\Mantenimiento\MantenimientoFactory;
use Illuminate\Database\Eloquent\Builder; // <--- Importante
use App\Traits\Scopes\Ordenable;          // <--- Trait Global

class Mantenimiento extends Model
{
    use HasFactory, Ordenable;

    protected $table = 'mantenimientos';

        protected static function newFactory()
    {
        return MantenimientoFactory::new();
    }

    protected $fillable = [
        'activo_id',
        'supervisor_id',
        'tecnico_principal_id',
        'tipo',
        'reporte_id',
        'fecha_apertura',
        'fecha_cierre',
        'estado',
        'descripcion',
        'validado',
        'costo_total',
    ];

    protected $casts = [
        'estado' => EstadoMantenimiento::class,
        'tipo' => TipoMantenimiento::class,
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'validado' => 'boolean',
        'costo_total' => 'decimal:2',
    ];

    /**
     * 🏰 Scope: Filtrar por Sede (Dirección)
     * La ruta es: Mantenimiento -> Activo -> Ubicacion -> Direccion
     */
    public function scopePorSede(Builder $query, $direccionId)
    {
        return $query->when($direccionId, function ($q, $id) {
            $q->whereHas('activo.ubicacion', function ($subQ) use ($id) {
                $subQ->where('direccion_id', $id);
            });
        });
    }

    /**
     * Scope Maestro de Filtros
     */
    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query
            // 1. Filtro Complejo de Sede
            ->porSede($filtros['sede_id'] ?? null)

            // 2. Filtro por Estado (Pendiente, En Progreso, etc.)
            ->when($filtros['estado'] ?? null, function ($q, $estado) {
                $q->where('estado', $estado);
            })

            // 3. Filtro por Prioridad (Viene del Reporte asociado)
            // OJO: Usamos whereHas porque 'prioridad' está en la tabla 'reportes'
            ->when($filtros['prioridad'] ?? null, function ($q, $prioridad) {
                $q->whereHas('reporte', fn($sq) => $sq->where('prioridad', $prioridad));
            });
    }
    //Relación inversa con el modelo Activo
    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    //Relación inversa con el modelo User como supervisor
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    //Relación inversa con el modelo User como técnico principal
    public function tecnicoPrincipal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_principal_id');
    }

    //Relación con el modelo SesionesMantenimiento
    public function sesiones(): HasMany
    {
        return $this->hasMany(SesionesMantenimiento::class, 'mantenimiento_id');
    }
    // Relación: Un mantenimiento pertenece a un reporte
    public function reporte(): BelongsTo
    {
        return $this->belongsTo(Reporte::class, 'reporte_id');
    }


}
