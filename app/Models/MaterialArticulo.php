<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\TipoMaterial;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Articulo;

class MaterialArticulo extends Model
{
    protected $table = 'material_articulos';

    protected $fillable = [
        'articulo_id',
        'tipo',
        'titulo',
        'descripcion',
        'url',
        'fecha_subida'
    ];

    protected $casts = [
        'tipo' => TipoMaterial::class,
        'fecha_subida' => 'datetime',
    ];

    //Relación inversa con el modelo Articulo
    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }
}
