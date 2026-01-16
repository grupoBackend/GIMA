<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Database\Factories\Admin\auditoriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Auditoria extends Model
{
        use HasFactory;
    
    protected $table = 'auditorias';

    protected static function newFactory()
    {
        return auditoriaFactory::new();
    }

    protected $fillable = [
        'usuario_id',
        'entidad',
        'entidad_id',
        'accion',
        'descripcion',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];
    
    //Relación inversa con el modelo Usuario 
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

}