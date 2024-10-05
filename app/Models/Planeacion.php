<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Planeacion extends Model
{
    use HasFactory;
    protected $table = 'planeacion';

    protected $fillable = [
        'id_cotizacion',
        'camion_servicio',
        'chasis_servicio',
        'id_proveedor',
        'precio',
        'id_empresa',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($empresa) {
            $empresa->id_empresa = Auth::user()->id_empresa;
        });

        static::updating(function ($empresa) {
            $empresa->id_empresa = Auth::user()->id_empresa;
        });
    }
}
