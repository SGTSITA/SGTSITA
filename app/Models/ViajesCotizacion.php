<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Traits\Auditable;

class ViajesCotizacion extends Pivot
{
     use Auditable;
    protected $table = 'viajes_cotizacion';

    public $timestamps = false;

    protected $fillable = [
        'viaje_id',
        'cotizacion_id',
    ];



    public function viaje()
    {
        return $this->belongsTo(Viajes::class, 'viaje_id');
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizaciones::class, 'cotizacion_id');
    }
}
