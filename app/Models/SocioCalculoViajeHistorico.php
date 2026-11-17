<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocioCalculoViajeHistorico extends Model
{
    protected $table = 'socios_calculos_viajes_historico';
    protected $fillable = [
        'calculo_periodo_id',
        'contenedor',
        'cliente',
        'unidad',
        'utilidad_viaje',
        'fecha_viaje'
    ];

    public function calculoPeriodo()
    {
        return $this->belongsTo(SocioCalculoPeriodo::class, 'calculo_periodo_id');
    }
}
