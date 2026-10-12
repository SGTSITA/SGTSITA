<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class CatBancoCuentasMovimientos extends Model
{
    use HasFactory;
    use Auditable;

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

    public function pagosGastos(): HasMany
    {
        return $this->hasMany(GastoPago::class, 'movimiento_bancario_id');
    }

    public function scopeDeUnidad($query, int $equipoId)
    {
        return $query->where(function ($q) use ($equipoId) {
            // 1. A través de pagos de gastos asociados
            $q->whereHas('pagosGastos.gasto', function ($gq) use ($equipoId) {
                $gq->where('id_equipo', $equipoId)
                   ->orWhereHas('vinculos', function ($vq) use ($equipoId) {
                       $vq->where('tipo_vinculo', 'unidad')
                          ->where('vinculable_id', $equipoId);
                   })
                   ->orWhereHas('vinculos', function ($vq) use ($equipoId) {
                       $vq->where('tipo_vinculo', 'asignacion')
                          ->whereIn('vinculable_id', function ($sub) use ($equipoId) {
                              $sub->select('id')->from('asignaciones')->where('id_camion', $equipoId);
                          });
                   });
            })
            // 2. A través de referenciaable directo a Gasto
            ->orWhere(function ($gq) use ($equipoId) {
                $gq->where('referenciaable_type', Gasto::class)
                   ->whereIn('referenciaable_id', function ($sub) use ($equipoId) {
                       $sub->select('id')->from('gastos')->where('id_equipo', $equipoId);
                   });
            })
            // 3. A través de liquidaciones (referenciaable)
            ->orWhere(function ($lq) use ($equipoId) {
                $lq->where('referenciaable_type', \App\Models\Liquidaciones::class)
                   ->whereIn('referenciaable_id', function ($sub) use ($equipoId) {
                       $sub->select('lc.id_liquidacion')
                           ->from('liquidacion_contenedor as lc')
                           ->join('asignaciones as a', 'a.id_contenedor', '=', 'lc.id_contenedor')
                           ->where('a.id_camion', $equipoId);
                   });
            })
            // 4. A través de asignaciones directas (anticipos/dinero de viaje)
            ->orWhere(function ($aq) use ($equipoId) {
                $aq->where('referenciaable_type', \App\Models\Asignaciones::class)
                   ->whereIn('referenciaable_id', function ($sub) use ($equipoId) {
                       $sub->select('id')->from('asignaciones')->where('id_camion', $equipoId);
                   });
            })
            // 5. En la columna detalles JSON (para items con id_equipo)
            ->orWhereJsonContains('detalles', ['id_equipo' => (int) $equipoId])
            ->orWhereJsonContains('detalles', ['id_equipo' => (string) $equipoId]);
        });
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
