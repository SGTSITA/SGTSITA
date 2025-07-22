<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostosViajesExtra extends Model
{
    use HasFactory;

    protected $table = 'costos_viajes_extra';

    protected $fillable = ['viaje_id', 'base1', 'base2'];

    public function viaje()
    {
        return $this->belongsTo(Cotizaciones::class, 'viaje_id');
    }
}
