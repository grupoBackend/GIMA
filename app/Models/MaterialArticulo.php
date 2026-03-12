<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\TipoMaterial;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Articulo;
use Database\Factories\Catalogo\MaterialArticuloFactory;

class MaterialArticulo extends Model
{
    use HasFactory;
    protected $table = 'material_articulos';

        protected static function newFactory()
    {
        return MaterialArticuloFactory::new();
    }

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

    public function scopeSearch($query, $term)
    {
        // 1. Validamos que el término no esté vacío
        if (empty($term)) {
            return $query;
        }

        $term = "%$term%";

        // 2. Usamos una función para agrupar los OR (esto pone paréntesis en el SQL)
        return $query->where(function ($q) use ($term) {
            $q->where('articulo_id', 'like', $term)
            ->orWhere('titulo', 'like', $term)
            ->orWhere('descripcion', 'like', $term)
            ->orWhere('tipo', 'like', $term)
            ->orWhere('fecha_subida', 'like', $term); 
        });
    }

}
