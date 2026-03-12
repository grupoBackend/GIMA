<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Database\Factories\General\NotificacionFactory;

class Notificacion extends Model
{
    use HasFactory;
    protected $table = 'notificaciones';

    protected static function newFactory()
    {
        return NotificacionFactory::new();
    }

    protected $fillable = [
        'usuario_id',
        'contenido',
    ];
    

    //Relación inversa con el modelo Usuario 
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * IMPORTANTE: Este modelo ahora es una "capa" sobre las notificaciones de Laravel
     * No se usará para crear nuevas notificaciones, solo para leer las existentes
     * a través de una vista o consulta especial
     */

    /**
     * Scope para obtener solo las notificaciones NO leídas
     */
    public function scopeNoLeidas($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope para obtener solo las notificaciones leídas
     */
    public function scopeLeidas($query)
    {
        return $query->whereNotNull('read_at');
    }
}
