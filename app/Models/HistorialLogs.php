<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Database\Factories\Admin\HistorialLogsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistorialLogs extends Model
{
    use HasFactory;

    protected $table = 'historial_logs';

    protected static function newFactory()
    {
        return HistorialLogsFactory::new();
    }

    protected $fillable = [
        'usuario_id',
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
