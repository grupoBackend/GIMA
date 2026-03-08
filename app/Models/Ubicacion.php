<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Activo;
use Database\Factories\Admin\UbicacionFactory;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    use HasFactory;

    protected $fillable = [
        'direccion_id',
        'edificio',
        'piso',
        'salon'
    ];

    protected static function newFactory()
    {
        return UbicacionFactory::new();
    }

    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    public function activos(): HasMany
    {
        return $this->hasMany(Activo::class, 'ubicacion_id');
    }

    // Scope para filtrar por dirección
    public function scopeDirecciones($query, $direccionId)
    {
        return $query->where('direccion_id', $direccionId);
    }
}