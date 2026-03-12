<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Database\Factories\Inventario\RepuestoFactory;
use Illuminate\Database\Eloquent\Builder; // <--- Importante
use App\Traits\Scopes\Ordenable;          // <--- El Trait 


class Repuesto extends Model
{
    use HasFactory;
    use Ordenable; // <--- Activamos el ordenamiento

    protected $table = 'repuestos';

    protected static function newFactory()
    {
        return RepuestoFactory::new();
    }

    protected $fillable = [
        'proveedor_id',
        'direccion_id',
        'descripcion',
        'codigo',
        'stock',
        'stock_minimo',
        'costo',
    ];

    protected $casts = [
        'stock' => 'int',
        'stock_minimo' => 'int',
        'costo' => 'decimal:2',
    ];

    /**
     * 🔍 Scope de Filtrado para Inventario
     */
    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query
            // 1. Búsqueda por Código o Descripción
            ->when($filtros['buscar'] ?? null, function ($q, $buscar) {
                $q->where(function ($subQ) use ($buscar) {
                    $subQ->where('codigo', 'ilike', "%{$buscar}%")       // Postgres ilike
                         ->orWhere('descripcion', 'ilike', "%{$buscar}%");
                });
            })
            // 2. Filtro de Alerta: "Dame lo que se está acabando"
            ->when(isset($filtros['bajo_stock']), function ($q) {
                $q->whereColumn('stock', '<=', 'stock_minimo');
            })
            // 3. Filtro por Proveedor (Para ver qué pedimos a quién)
            ->when($filtros['proveedor_id'] ?? null, function ($q, $id) {
                $q->where('proveedor_id', $id);
            })
            // 4. Filtro por Sede (Dirección)
            ->when($filtros['direccion_id'] ?? null, function ($q, $id) {
                $q->where('direccion_id', $id);
            });
    }

    //Relación con el modelo RepuestoUsado
    public function repuestoUsado(): HasMany
    {
        return $this->hasMany(RepuestoUsado::class, 'repuesto_id');
    }

    //Relación con el modelo Proveedor
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    //Relación con el modelo Direccion
    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    // --- MÉTODOS ESPECIALES DE STOCK ---
    public function scopeStockBajo(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'stock_minimo');
    }
}
