<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocioCalculoPeriodo extends Model
{
    protected $table = 'socios_calculos_periodos';
    protected $fillable = [
        'id_empresa',
        'fecha_desde',
        'fecha_hasta',
        'total_utilidad_bruta_viajes',
        'total_gastos_periodo',
        'utilidad_neta_distribuible',
        'user_id'
    ];

    public function detalles()
    {
        return $this->hasMany(SocioCalculoDetalle::class, 'calculo_periodo_id');
    }

    public function viajesHistorico()
    {
        return $this->hasMany(SocioCalculoViajeHistorico::class, 'calculo_periodo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
