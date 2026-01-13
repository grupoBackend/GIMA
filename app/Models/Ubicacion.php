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
        'edificio',
        'piso', 
        'salon'
    ]; 

    protected static function newFactory()
    {
        return UbicacionFactory::new();
    }

    //Relación inversa con el direccion
    public function direccion() : BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    //Relación con el modelo Activo
    public function activos(): HasMany 
    {
        return $this->hasMany(Activo::class, 'ubicacion_id');
        
    }

}
