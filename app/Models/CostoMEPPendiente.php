<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostoMEPPendiente extends Model
{
    protected $table = 'costos_mep_pendientes';

    protected $fillable = [
        'id_asignacion',
        'precio_viaje',
        'burreo',
        'maniobra',
        'estadia',
        'otro',
        'iva',
        'retencion',
        'base1',
        'base2',
        'sobrepeso',
        'precio_sobrepeso',
        'estatus',
        'total',
        'motivo_cambio', 
    ];

    public function asignacion()
    {
        return $this->belongsTo(Asignaciones::class, 'id_asignacion');
    }
}
