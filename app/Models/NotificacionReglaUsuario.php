<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionReglaUsuario extends Model
{
    protected $table = 'notificacion_regla_usuarios';

    protected $fillable = [
        'notificacion_regla_id',
        'user_id',
    ];

    public function regla()
    {
        return $this->belongsTo(NotificacionRegla::class, 'notificacion_regla_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
