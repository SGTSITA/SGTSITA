<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class Bancos extends Model
{
    use HasFactory;

    protected $table = 'bancos';

    protected $fillable = [
        'nombre_beneficiario',
        'nombre_banco',
        'cuenta_bancaria',
        'clabe',
        'saldo_inicial',
        'saldo',
        'tipo',
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
