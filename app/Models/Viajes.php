<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Viajes extends Model
{
    // use Auditable;
    protected $table = 'viajes';

    protected $fillable = [
        'tipo',
        'estado'

    ];




    public function costos()
    {
        return $this->hasMany(ViajesCostos::class, 'viaje_id');
    }


    public function cotizaciones()
    {
        return $this->belongsToMany(
            Cotizaciones::class,
            'viajes_cotizacion',
            'viaje_id',
            'cotizacion_id'
        )->using(ViajesCotizacion::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeFull($query)
    {
        return $query->where('tipo', 'full');
    }

    public function scopeSencillos($query)
    {
        return $query->where('tipo', 'sencillo');
    }


    public function esFull()
    {
        return $this->tipo === 'full';
    }

    public function estaCancelado()
    {
        return $this->estado === 'cancelado';
    }

    public function total()
    {
        return $this->costos->sum->monto_real;
    }

    public function totalCargos()
    {
        return $this->costos->where('tipo_operacion', 'cargo')->sum('monto');
    }

    public function totalDescuentos()
    {
        return $this->costos->where('tipo_operacion', 'descuento')->sum('monto');
    }
}
