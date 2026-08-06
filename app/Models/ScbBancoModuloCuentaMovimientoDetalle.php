<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class ScbBancoModuloCuentaMovimientoDetalle extends Model
{
    use HasFactory;
      use Auditable;

    protected $table = 'scb_bancos_modulo_cuentas_movimientos_detalles';

    protected $fillable = [
        'movimiento_id',
        'descripcion',
        'referencia',
        'unidad_id',
        'monto',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function movimiento()
    {
        return $this->belongsTo(
            ScbBancoModuloCuentaMovimiento::class,
            'movimiento_id',
            'id'
        );
    }

    public function unidad()
    {
        return $this->belongsTo(
            ScbUnidadModulo::class,
            'unidad_id',
            'id'
        );
    }

}
