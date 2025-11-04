<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
   use HasFactory, Notifiable, HasRoles;

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

}
