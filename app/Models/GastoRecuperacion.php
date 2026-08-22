<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GastoRecuperacion extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'gasto_recuperaciones';

    protected $fillable = [
        'gasto_id',
        'cotizacion_id',
        'docum_cotizacion_id',
        'concepto',
        'monto_costo',
        'monto_cobrar',
        'estatus_cobro',
    ];

    protected $casts = [
        'monto_costo' => 'decimal:2',
        'monto_cobrar' => 'decimal:2',
    ];

    public function gasto()
    {
        return $this->belongsTo(Gasto::class, 'gasto_id');
    }
}
