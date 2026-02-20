<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Estado_Cuenta_Cotizaciones extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'estado_cuenta_cotizaciones';

    protected $fillable = [
        'cotizacion_id',
        'estado_cuenta_id',
        'assigned_by',
    ];

    /**
     * Estado de cuenta asignado
     */
    public function estadoCuenta()
    {
        return $this->belongsTo(Estado_Cuenta::class, 'estado_cuenta_id');
    }

    /**
     * Cotización relacionada
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizaciones::class, 'cotizacion_id');
    }

    /**
     * Usuario que hizo la asignación o cambio
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
