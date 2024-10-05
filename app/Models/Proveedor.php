<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Proveedor extends Model
{
    use HasFactory;
    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'direccion',
        'rfc',
        'correo',
        'telefono',
        'regimen_fiscal',
        'fecha',
        'tipo',
        'id_empresa',
    ];

    public function CuentasBancarias()
    {
        return $this->hasMany(CuentasBancarias::class, 'id_proveedores');
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
