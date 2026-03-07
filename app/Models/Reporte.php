<?php

namespace App\Models;

use App\Enums\EstadoReporte;
use App\Enums\NivelPrioridad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Activo;
use App\Models\ReporteMantenimiento;
use Database\Factories\Mantenimiento\ReporteFactory;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'reportes';

    protected static function newFactory()
    {
        return ReporteFactory::new();
    }

    protected $fillable = [
        'usuario_id',
        'activo_id',
        'descripcion',
        'prioridad',
        'estado',
    ];


    protected $casts = [
        'prioridad' => NivelPrioridad::class,
        'estado' => EstadoReporte::class
    ];

    //Relación inversa con el modelo Usuario 
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    //Relación inversa con el modelo Activo
    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    //Relación uno a muchos con el modelo Mantenimiento
    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'reporte_id');
    }

    // Scope para la búsqueda general de reportes - Encargado de tarea: Sebastian Rodriguez (Lider: Juan Longart - Haddan Valencia)
    public function scopeSearch($query, $search): mixed
    {
        return $query->where('descripcion', 'LIKE', "%{$search}%");
    }


    //Scope para filtrar reportes por sede (a través de la ubicación del activo).
    public function scopePorSede($query, $sedeId): mixed
    {
        return $query->whereHas('activo', fn($q): mixed => $q->where('ubicacion_id', $sedeId));
    }
}
