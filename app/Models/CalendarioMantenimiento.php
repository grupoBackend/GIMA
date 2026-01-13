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
}