<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceGeneralSaldoInicial extends Model
{
    protected $table = 'balance_general_saldos_iniciales';

    protected $fillable = [
        'id_empresa',
        'config_id',
        'ejercicio',
        'fecha_inicio',
        'monto',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_inicio' => 'date',
    ];

    public function config()
    {
        return $this->belongsTo(BalanceGeneralConfig::class, 'config_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }
}
