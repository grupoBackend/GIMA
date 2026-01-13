<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Repuesto;
use Database\Factories\Inventario\ProveedorFactory;

class Proveedor extends Model
{
    use HasFactory;
    protected $table = 'proveedores';

    protected static function newFactory()
    {
        return ProveedorFactory::new();
    }

    protected $fillable = [
        'nombre', 
        'contacto', 
        'telefono', 
        'email'
    ]; 

    //Relación con el modelo Repuesto
    public function repuestos(): HasMany 
    {
        return $this->hasMany(Repuesto::class, 'proveedor_id');
    }
}
