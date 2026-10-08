<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionTipo extends Model
{
    protected $table = 'notificacion_tipos';

    protected $fillable = [
        'clave',
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function reglas()
    {
        return $this->hasMany(NotificacionRegla::class, 'notificacion_tipo_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'notificacion_tipo_id');
    }
}
