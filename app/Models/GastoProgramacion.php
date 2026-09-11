<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GastoProgramacion extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'gasto_programaciones';

    protected $fillable = [
        'gasto_id',
        'periodo_id',
        'numero_periodo',
        'fecha_programada',
        'fecha_vencimiento',
        'monto_programado',
        'monto_pagado',
        'gasto_imputacion_id',
        'estatus',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_vencimiento' => 'date',
        'monto_programado' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
    ];

    public function gasto()
    {
        return $this->belongsTo(Gasto::class, 'gasto_id');
    }

    public function imputacion()
    {
        return $this->belongsTo(GastoImputacion::class, 'gasto_imputacion_id');
    }

    public function pagos()
    {
        return $this->hasMany(GastoPago::class, 'gasto_programacion_id');
    }
}
