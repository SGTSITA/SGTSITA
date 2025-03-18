<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DocumCotizacion extends Model
{
    use HasFactory;
    protected $table = 'docum_cotizacion';

    protected $fillable = [
        'id_cotizacion',
        'num_contenedor',
        'terminal',
        'num_autorizacion',
        'boleta_liberacion',
        'doda',
        'id_empresa',

    ];

    public function Cotizacion()
    {
        return $this->belongsTo(Cotizaciones::class, 'id_cotizacion');
    }

    public function Asignaciones()
    {
        return $this->hasOne(Asignaciones::class, 'id_contenedor');
    }

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
