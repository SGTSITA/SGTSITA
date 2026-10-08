<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocioCalculoDetalle extends Model
{
    protected $table = 'socios_calculos_detalles';
    protected $fillable = [
        'calculo_periodo_id',
        'socio_id',
        'tipo_pago',
        'valor_pactado',
        'monto_distribuido'
    ];

    public function calculoPeriodo()
    {
        return $this->belongsTo(SocioCalculoPeriodo::class, 'calculo_periodo_id');
    }

    public function socio()
    {
        return $this->belongsTo(Socio::class, 'socio_id')->withTrashed();
    }
}
