<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesionesMantenimiento extends Model
{
    use HasFactory;

    protected $table = 'sesiones_mantenimiento';

    protected $fillable = [
        'mantenimiento_id',
        'tecnico_id',
        'fecha',
        'horas_trabajadas',
        'observaciones',
        'descripcion_trabajo',
        'costo_total',
    ];


    protected $casts = [
        'fecha' => 'datetime',
        'horas_trabajadas' => 'decimal:2',
        'costo_hora' => 'decimal:2',
    ];

    //Relación con el modelo RepuestoUsado
    public function repuestosUtilizados() : HasMany
    {
        return $this->hasMany(RepuestoUsado::class, 'sesion_id');
    }

    //Relación inversa con el modelo Mantenimiento
    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id');
    }
    //Relación inversa con el modelo Usuario (tecnico)
    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }
}
