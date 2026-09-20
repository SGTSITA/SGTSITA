<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'notificacion_tipo_id',
        'empresa_id',
        'titulo',
        'mensaje',
        'modelo_type',
        'modelo_id',
        'url',
        'data',
        'leida_at',
        'vista_at',
    ];

    protected $casts = [
        'data' => 'array',
        'leida_at' => 'datetime',
        'vista_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipo()
    {
        return $this->belongsTo(NotificacionTipo::class, 'notificacion_tipo_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }

    public function modelo()
    {
        return $this->morphTo(null, 'modelo_type', 'modelo_id');
    }

    public function scopeNoLeidas($query)
    {
        return $query->whereNull('leida_at');
    }

    public function scopeLeidas($query)
    {
        return $query->whereNotNull('leida_at');
    }

    public function scopePendientes($query)
    {
        return $query->whereNull('leida_at');
    }

    public function marcarComoLeida(): bool
    {
        return $this->update([
            'leida_at' => now(),
        ]);
    }

    public function marcarComoVista(): bool
    {
        return $this->update([
            'vista_at' => now(),
        ]);
    }

    public function estaLeida(): bool
    {
        return !is_null($this->leida_at);
    }
}
