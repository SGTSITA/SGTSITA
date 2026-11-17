<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolizaMovimiento extends Model
{
    use HasFactory;

    protected $table = 'poliza_movimientos';

    protected $fillable = [
        'poliza_id',
        'cuenta_contable_id',
        'id_equipo',
        'linea',
        'referencia',
        'concepto_detalle',
        'debe',
        'haber',
    ];

    protected $casts = [
        'linea' => 'integer',
        'debe' => 'decimal:2',
        'haber' => 'decimal:2',
    ];

    public function poliza(): BelongsTo
    {
        return $this->belongsTo(Poliza::class, 'poliza_id');
    }

    public function cuentaContable(): BelongsTo
    {
        return $this->belongsTo(CatCuentaContable::class, 'cuenta_contable_id');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'id_equipo');
    }
}
