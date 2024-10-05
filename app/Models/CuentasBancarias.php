<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CuentasBancarias extends Model
{
    use HasFactory;
    protected $table = 'cuentas_bancarias';

    protected $fillable = [
        'id_proveedores',
        'cuenta_bancaria',
        'nombre_banco',
        'cuenta_clabe',
        'id_empresa',

    ];

    public function Proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedores');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cuentasBancarias) {
            $cuentasBancarias->id_empresa = Auth::user()->id_empresa;
        });

        static::updating(function ($cuentasBancarias) {
            $cuentasBancarias->id_empresa = Auth::user()->id_empresa;
        });
    }
}
