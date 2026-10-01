<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Gasto extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    protected $table = 'gastos';

    protected $fillable = [
        'id_empresa',
        'categoria_gasto_id',
        'gasto_concepto_id',
        'folio',
        'concepto',
        'descripcion',
        'monto_total',
        'moneda',
        'fecha_gasto',
        'fecha_operacion',
        'tipo_gasto',
        'metodo_imputacion',
        'estatus',
        'origen_modulo',
        'origen_legacy',
        'origen_legacy_id',
        'user_id',
    ];

    protected $casts = [
        'fecha_gasto' => 'date',
        'fecha_operacion' => 'date',
        'monto_total' => 'decimal:2',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriasGastos::class, 'categoria_gasto_id');
    }

    public function conceptoCatalogo()
    {
        return $this->belongsTo(GastoConcepto::class, 'gasto_concepto_id');
    }

    public function partidas()
    {
        return $this->hasMany(GastoPartida::class, 'gasto_id');
    }

    public function imputaciones()
    {
        return $this->hasMany(GastoImputacion::class, 'gasto_id');
    }

    public function programaciones()
    {
        return $this->hasMany(GastoProgramacion::class, 'gasto_id');
    }

    public function pagos()
    {
        return $this->hasMany(GastoPago::class, 'gasto_id');
    }

    public function recuperaciones()
    {
        return $this->hasMany(GastoRecuperacion::class, 'gasto_id');
    }

    public function vinculos()
    {
        return $this->hasMany(GastoVinculo::class, 'gasto_id');
    }

    public function getMontoPagadoAttribute(): float
    {
        return (float) $this->pagos()
            ->where('estatus', 'aplicado')
            ->sum('monto');
    }

    public function getSaldoPendienteAttribute(): float
    {
        return max(0, (float) $this->monto_total - $this->monto_pagado);
    }
}
