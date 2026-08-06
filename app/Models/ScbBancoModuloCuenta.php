<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScbBancoModuloCuenta extends Model
{
    use HasFactory;

    protected $table = 'scb_bancos_modulo_cuentas';

    protected $fillable = [
        'banco_id',
        'beneficiario',
        'numero_cuenta',
        'clabe',
        'moneda',
        'saldo_inicial',
        'activo',
    ];

    protected $casts = [
        'saldo_inicial' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function banco()
    {
        return $this->belongsTo(ScbBancoModulo::class, 'banco_id', 'id');
    }

    public function movimientos()
    {
        return $this->hasMany(ScbBancoModuloCuentaMovimiento::class, 'cuenta_id', 'id');
    }
}
