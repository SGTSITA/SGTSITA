<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitacoraCotizacionesEstatus extends Model
{
    use HasFactory;

    protected $table = 'bitacora_cotizaciones_estatus';

    protected $fillable = [
        'cotizaciones_id',
        'estatus_id',
        'nota',
        'user_id'
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizaciones::class, 'cotizaciones_id');
    }
    public function estatus()
    {
        return $this->belongsTo(EstatusManiobra::class, 'estatus_id');
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
