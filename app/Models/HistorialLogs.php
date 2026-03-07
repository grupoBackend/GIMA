<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Database\Factories\Admin\HistorialLogsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder; // <--- Importante
use App\Traits\Scopes\Ordenable;          // <--- Nuestro Trait

class HistorialLogs extends Model
{
    use HasFactory, Ordenable;

    protected $table = 'historial_logs';

    protected static function newFactory()
    {
        return HistorialLogsFactory::new();
    }

    protected $fillable = [
        'usuario_id',
        'accion',
        'descripcion',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];
    
    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query
            // 1. Filtro por Usuario (usuario_id)
            ->when($filtros['usuario_id'] ?? null, function ($q, $userId) {
                $q->where('usuario_id', $userId);
            })
            // 2. Filtro por Acción
            ->when($filtros['accion'] ?? null, function ($q, $accion) {
                $q->where('accion', 'ilike', "%{$accion}%"); 
            })
            // 3. Filtro por Fecha (Campo 'fecha')
            ->when($filtros['fecha_desde'] ?? null, function ($q, $fecha) {
                $q->whereDate('fecha', '>=', $fecha);
            })
            ->when($filtros['fecha_hasta'] ?? null, function ($q, $fecha) {
                $q->whereDate('fecha', '<=', $fecha);
            });
    }

    //Relación inversa con el modelo Usuario 
    public function user()
        {
            return $this->belongsTo(User::class, 'usuario_id');
        }
}
