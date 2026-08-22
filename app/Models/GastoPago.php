<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GastoPago extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'gasto_pagos';

    protected $fillable = [
        'gasto_id',
        'gasto_programacion_id',
        'cuenta_bancaria_id',
        'movimiento_bancario_id',
        'fecha_pago',
        'monto',
        'metodo_pago',
        'referencia',
        'comprobante',
        'estatus',
        'user_id',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto' => 'decimal:2',
    ];

    public function gasto()
    {
        return $this->belongsTo(Gasto::class, 'gasto_id');
    }

    public function programacion()
    {
        return $this->belongsTo(GastoProgramacion::class, 'gasto_programacion_id');
    }

    public function cuentaBancaria()
    {
        return $this->belongsTo(Bancos::class, 'cuenta_bancaria_id');
    }

    public function movimientoBancario()
    {
        return $this->belongsTo(CatBancoCuentasMovimientos::class, 'movimiento_bancario_id');
    }
}
