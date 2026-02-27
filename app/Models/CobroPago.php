<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CobroPago extends Model
{
    use HasFactory;

    protected $table = 'cobros_pagos';

    protected $fillable = [
        'tipo',
        'cliente_id',
        'proveedor_id',
        'bancoA_id',
        'monto_A',
        'fechaAplicacion1',
        'banco_proveedor_idA',
        'bancoB_id',
        'monto_B',
        'fechaAplicacion2',
        'banco_proveedor_idB',
        'user_id',
        'observaciones',
    ];

    protected $casts = [
        'monto_A' => 'decimal:2',
        'monto_B' => 'decimal:2',
        'fechaAplicacion1' => 'date',
        'fechaAplicacion2' => 'date',
    ];


    public function cliente()
    {
        return $this->belongsTo(\App\Models\Client::class, 'cliente_id');
    }


    public function proveedor()
    {
        return $this->belongsTo(\App\Models\Proveedor::class, 'proveedor_id');
    }


    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }


    public function bancoA()
    {
        return $this->belongsTo(Bancos::class, 'bancoA_id');
    }


    public function bancoB()
    {
        return $this->belongsTo(Bancos::class, 'bancoB_id');
    }


    public function bancoProveedorA()
    {
        return $this->belongsTo(CuentasBancarias::class, 'banco_proveedor_idA');
    }


    public function bancoProveedorB()
    {
        return $this->belongsTo(CuentasBancarias::class, 'banco_proveedor_idB');
    }


    public function detalles()
    {
        return $this->hasMany(CobroPagoCotizacion::class, 'cobro_pago_id');
    }




    public function getTotalCalculadoAttribute()
    {
        return $this->monto_A + $this->monto_B;
    }


    public function esCxc()
    {
        return $this->tipo === 'cxc';
    }


    public function esCxp()
    {
        return $this->tipo === 'cxp';
    }
}
