<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\Auditable;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'id_empresa',
        'password',
        'id_cliente',
        'consecutivo_conboy',
    'es_admin',
    ];





    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'es_admin' => 'boolean',
    ];
    public function Empresa()
    {
        return $this->belongsTo(Empresas::class, 'id_empresa');
    }
    public function empresasAsignadas()
    {
        return $this->belongsToMany(Empresas::class, 'users_empresas', 'id_user', 'id_empresa');
    }

    public function proveedores()
    {
        return $this->belongsToMany(
            Proveedor::class,
            'user_proveedores',
            'user_id',
            'proveedor_id'
        );
    }

    public function clientes()
    {
        return $this->belongsToMany(
            Client::class,
            'user_clientes',
            'user_id',
            'cliente_id'
        );
    }

    public function notificaciones()
{
    return $this->hasMany(Notificacion::class, 'user_id');
}

public function notificacionesNoLeidas()
{
    return $this->hasMany(Notificacion::class, 'user_id')
        ->whereNull('leida_at');
}

public function reglasNotificacion()
{
    return $this->belongsToMany(
        NotificacionRegla::class,
        'notificacion_regla_usuarios',
        'user_id',
        'notificacion_regla_id'
    )->withTimestamps();
}

}
