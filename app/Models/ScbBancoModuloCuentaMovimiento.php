<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class ScbBancoModuloCuentaMovimiento extends Model
{
    use HasFactory;
    use Auditable;

      protected $table = 'scb_bancos_modulo_cuentas_movimientos';

    protected $fillable = [
        'cuenta_id',
        'tipo',
        'fecha_movimiento',
        'concepto',
        'referencia_bancaria',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'fecha_movimiento' => 'date',
    ];

    public function cuenta()
    {
        return $this->belongsTo(ScbBancoModuloCuenta::class, 'cuenta_id', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(ScbBancoModuloCuentaMovimientoDetalle::class, 'movimiento_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getTotalAttribute()
    {
        return $this->detalles->sum('monto');
    }

    public function scopeCargos($query)
    {
        return $query->where('tipo', 'cargo');
    }

    public function scopeAbonos($query)
    {
        return $query->where('tipo', 'abono');
    }
}
