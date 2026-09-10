<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionRegla extends Model
{
    protected $table = 'notificacion_reglas';

    protected $fillable = [
        'notificacion_tipo_id',
        'empresa_id',
        'notificar_empresa',
        'notificar_cliente',
        'notificar_proveedor',
        'incluir_url_documento',
        'activo',
    ];

    protected $casts = [
        'notificar_empresa' => 'boolean',
        'notificar_cliente' => 'boolean',
        'notificar_proveedor' => 'boolean',
        'incluir_url_documento'=> 'boolean',
        'activo' => 'boolean',
    ];

    public function tipo()
    {
        return $this->belongsTo(NotificacionTipo::class, 'notificacion_tipo_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(
            User::class,
            'notificacion_regla_usuarios',
            'notificacion_regla_id',
            'user_id'
        )->withTimestamps();
    }
}
