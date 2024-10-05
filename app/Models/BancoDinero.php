<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BancoDinero extends Model
{
    use HasFactory;
    protected $table = 'banco_dinero';

    protected $fillable = [
        'id_cliente',
        'id_proveedor',
        'contenedores',
        'monto1',
        'metodo_pago1',
        'comprobante_pago1',
        'monto2',
        'metodo_pago2',
        'comprobante_pago2',
        'fecha_pago',
        'id_banco1',
        'id_banco2',
        'tipo',
        'id_empresa',
    ];
    public function Cliente()
    {
        return $this->belongsTo(Client::class, 'id_cliente');
    }
    public function Cliente2()
    {
        return $this->belongsTo(Proveedor::class, 'id_cliente');
    }
    public function Proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }
    public function Banco1()
    {
        return $this->belongsTo(Bancos::class, 'id_banco1');
    }
    public function Banco2()
    {
        return $this->belongsTo(Bancos::class, 'id_banco2');
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
