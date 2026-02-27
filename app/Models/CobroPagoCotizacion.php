<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CobroPagoCotizacion extends Model
{
    use HasFactory;

    protected $table = 'cobros_pagos_cotizaciones';

    protected $fillable = [
        'cobro_pago_id',
        'cotizacion_id',
        'origen',
        'monto',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];


    public function cobroPago()
    {
        return $this->belongsTo(CobroPago::class, 'cobro_pago_id');
    }


    public function cotizacion()
    {
        return $this->belongsTo(\App\Models\Cotizaciones::class, 'cotizacion_id');
    }



    public function esBancoA()
    {
        return $this->origen === 'A';
    }


    public function esBancoB()
    {
        return $this->origen === 'B';
    }
}
