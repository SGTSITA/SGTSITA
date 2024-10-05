<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Operador extends Model
{
    use HasFactory;
    protected $table = 'operadores';

    protected $fillable = [
        'nombre',
        'domicilio',
        'fecha_nacimiento',
        'comprobante_domicilio',
        'ine',
        'cedula_fiscal',
        'licencia_conducir',
        'acceso',
        'correo',
        'telefono',
        'tipo_sangre',
        'nss',
        'recomendacion',
        'foto',
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
