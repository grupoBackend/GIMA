<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CalendarioMantenimiento;
use App\Enums\EstadoActivo; 
use App\Models\Articulos;
use App\Models\Ubicacion; 

class Activo extends Model
{
    use HasFactory;

    protected $table = 'activos'; 

    protected $fillable = [
        'articulo_id',
        'ubicacion_id',
        'estado',      
        'valor',       
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos o Enums.
     */
    protected $casts = [
        'estado' => EstadoActivo::class, 
        'valor' => 'float',
    ];


    
    //Relación inversa con el modelo Articulos
    public function articulo() : BelongsTo
    {
        // Se modificó 'Articulos' a 'Articulo' para coincidir con el nombre del modelo
        // Por error de nombre me estaba dando problemas
        return $this->belongsTo(Articulo::class, 'articulo_id'); 
    }

    //Relación inversa con el modelo Ubicacion
    public function ubicacion() : BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id'); 
    }

    //Relación con el modelo CalendarioMantenimiento
    public function calendarioMantenimientos() : HasMany
    {
        return $this->hasMany(CalendarioMantenimiento::class, 'activo_id');
    }

    // Relación con el modelo Mantenimiento
    public function mantenimientos() : HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'activo_id');
    }

    //Relación con el modelo Reporte
    public function reportes() : HasMany
    {
        return $this->hasMany(Reporte::class, 'activo_id');
    }
}