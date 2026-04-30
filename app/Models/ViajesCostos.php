<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class ViajesCostos extends Model
{
    // use Auditable;
    protected $table = 'viajes_costos';

    protected $fillable = [
        'viaje_id',
        'concepto',
        'monto',
        'tipo_operacion',
        'meta',
        'monto_cobrado',
        'cobrado',
        'fecha_cobro',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_cobrado' => 'decimal:2',
        'cobrado' => 'boolean',
        'fecha_cobro' => 'datetime',
        'meta' => 'array',
    ];



    public function viaje()
    {
        return $this->belongsTo(Viajes::class, 'viaje_id');
    }


    public function scopeCargos($query)
    {
        return $query->where('tipo_operacion', 'cargo');
    }

    public function scopeDescuentos($query)
    {
        return $query->where('tipo_operacion', 'descuento');
    }

    public function scopePorConcepto($query, $concepto)
    {
        return $query->where('concepto', $concepto);
    }


    public function getMontoRealAttribute()
    {
        return $this->tipo_operacion === 'descuento'
            ? -$this->monto
            : $this->monto;
    }

    public function getPendienteAttribute()
    {
        return $this->monto - $this->monto_cobrado;
    }


    public function esDescuento()
    {
        return $this->tipo_operacion === 'descuento';
    }

    public function esCobradoCompleto()
    {
        return $this->monto_cobrado >= $this->monto;
    }
}
