<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\Admin\DireccionFactory;
use App\Models\Ubicacion;
use App\Models\Repuesto;

class Direccion extends Model
{
    use HasFactory;
    protected $table = 'direcciones';

    protected static function newFactory()
    {
        return DireccionFactory::new();
    }

    protected $fillable = [
        'estado',
        'ciudad',
        'sector',
        'calle',
        'sede'
    ];

    
   //Relación con el modelo Ubicacion
    public function ubicaciones(): HasMany 
    {
        return $this->hasMany(Ubicacion::class, 'direccion_id');
    }

    //Relación con el modelo Repuesto
     public function repuestos(): HasMany 
    {
        return $this->hasMany(Repuesto::class, 'direccion_id');
    }
}
