<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatBancoCuentasMovimientos extends Model
{
    use HasFactory;

    protected $table = 'cat_bancos_cuentas_movimientos';

    protected $fillable = [
        'cuenta_bancaria_id',
        'fecha_movimiento',
        'concepto',
        'referencia',
        'tipo',
        'monto',
        'origen',
        'referenciaable_type',
        'referenciaable_id',
        'cancelado',
        'fecha_cancelacion',
        'user_id',
        'observaciones',
        'detalles',
    ];

    protected $casts = [
        'fecha_movimiento'  => 'date',
        'fecha_cancelacion' => 'datetime',
        'monto'             => 'decimal:2',
        'cancelado'         => 'boolean',
         'detalles' => 'array',
    ];

    public function cuentaBancaria(): BelongsTo
    {
        return $this->belongsTo(Bancos::class, 'cuenta_bancaria_id');
    }


    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function referenciaable(): MorphTo
    {
        return $this->morphTo();
    }



    public function scopeAbonos($query)
    {
        return $query->where('tipo', 'abono');
    }

    public function scopeCargos($query)
    {
        return $query->where('tipo', 'cargo');
    }

    public function scopeActivos($query)
    {
        return $query->where('cancelado', false);
    }

    public function scopePorCuenta($query, $cuentaId)
    {
        return $query->where('cuenta_bancaria_id', $cuentaId);
    }

}
