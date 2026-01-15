<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Database\Factories\Inventario\RepuestoFactory;

class Repuesto extends Model
{
    use HasFactory;
    
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
        'stock' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'costo' => 'decimal:2',
    ];

    //Relación con el modelo RepuestoUsado
     public function repuestoUsado(): HasMany
    {
        return $this->HasMany(RepuestoUsado::class, 'repuesto_id'); 
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
}
