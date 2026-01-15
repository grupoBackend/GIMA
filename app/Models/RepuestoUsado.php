<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\Mantenimiento\RepuestoUsadoFactory;

class RepuestoUsado extends Model
{
    use HasFactory;

    protected $table = 'repuestos_usados';
    
    protected static function newFactory()
    {
        return RepuestoUsadoFactory::new();
    }

    protected $fillable = [
        'sesion_id',
        'repuesto_id',
        'cantidad',
        'costo_total',
    ];

    //Relacion inversa con el modelo SesionesMantenimiento
    public function sesion(): BelongsTo
    {
        return $this->belongsTo(SesionesMantenimiento::class, 'sesion_id');
    }

    //Relacion inversa con el modelo Repuesto
    public function repuesto(): BelongsTo
    {
        return $this->belongsTo(Repuesto::class, 'repuesto_id');
    }
}
