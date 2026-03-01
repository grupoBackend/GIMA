<?php

namespace App\Models;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Enums\UserStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Scopes\Ordenable;


/**
 * @OA\Schema(
 * title="User",
 * description="Modelo de Usuario",
 * @OA\Xml(name="User"),
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="Juan Perez"),
 * @OA\Property(property="email", type="string", format="email", example="juan@gima.com"),
 * @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time"),
 * @OA\Property(property="telefono", type="string", example="+584141234567"),
 * @OA\Property(property="estado", type="string", example="activo"),
 * @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"admin"})
 * )
 */



class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;   //=========Para crear los roles con Spatie============
    use HasApiTokens; //=========Para usar tokens Sanctum============
    use Ordenable;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'recovery_pin',
        'telefono',
        'estado',
        'fecha_aprobacion',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'recovery_pin',
    ];


    /**
     * 🔍 SCOPES DE FILTRADO INDIVIDUALES
     */

    // 1. Búsqueda Global (Nombre, Email, Teléfono)
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($subQ) use ($term) {
            // Nota: Si usas MySQL, cambia 'ilike' por 'like'. 'ilike' es para Postgres.
            $subQ->where('name', 'ilike', "%{$term}%")
                ->orWhere('email', 'ilike', "%{$term}%")
                ->orWhere('telefono', 'like', "%{$term}%");
        });
    }

    // 2. Filtro por Rol (Spatie)
    public function scopePorRol($query, $rol)
    {
        return $query->whereHas('roles', fn($sq) => $sq->where('name', $rol));
    }

    // 3. Filtro por Estado
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'estado' => UserStatusEnum::class,
            'fecha_aprobacion' => 'datetime',
        ];
    }

    //Relación con el mismo modelo User para el campo aprobado_por
    /* 
    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function usuariosAprobados(): HasMany
    {
        return $this->hasMany(User::class, 'aprobado_por');
    }
 */


    //Relación con el modelo SesionesMantenimiento
    public function sesionesMantenimiento(): HasMany
    {
        return $this->hasMany(SesionesMantenimiento::class, 'tecnico_id');
    }

    //Relación con el modelo Auditorias
    public function auditorias(): HasMany
    {
        return $this->hasMany(HistorialLogs::class, 'usuario_id');
    }

    //Relación con el modelo Notificaciones
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    //Relación con el modelo CalendarioMantenimiento
    public function calendarioMantenimientos(): HasMany
    {
        return $this->hasMany(CalendarioMantenimiento::class, 'tecnico_asignado_id');
    }

    //Relación con el modelo Reporte
    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class, 'usuario_id');
    }

    //Relación con el modelo Mantenimiento como supervisor
    public function mantenimientosSupervisados(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'supervisor_id');
    }

    //Relación con el modelo Mantenimiento como tecnico principal
    public function mantenimientosTecnicoPrincipal(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'tecnico_principal_id');
    }
}
