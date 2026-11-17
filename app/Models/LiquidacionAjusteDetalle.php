<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class LiquidacionAjusteDetalle extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'liquidacion_ajustes_detalles';

    protected $fillable = [
        'id_ajuste',
        'tipo',
        'categoria_concepto',
        'id_prestamo',
        'descripcion',
        'monto',
    ];

    protected $casts = [
        'monto' => 'float',
    ];

    public function ajuste()
    {
        return $this->belongsTo(LiquidacionAjuste::class, 'id_ajuste');
    }

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'id_prestamo');
    }

    public function getNombreConceptoAttribute(): string
    {
        return match($this->categoria_concepto) {
            'sueldo_operador' => 'Sueldo Operador',
            'dinero_viaje' => 'Dinero de Viaje / Viáticos',
            'gastos_justificados' => 'Gastos Justificados / Reembolso',
            'bono' => 'Bono / Compensación',
            'abono_prestamo' => 'Abono a Préstamo',
            'abono_adelanto' => 'Abono a Adelanto',
            'descuento' => 'Descuento Operativo',
            default => ucfirst(str_replace('_', ' ', $this->categoria_concepto ?? 'Concepto')),
        };
    }
}
