<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceGeneralConfig extends Model
{
    protected $table = 'balance_general_configs';

    protected $fillable = [
        'id_empresa',
        'grupo',
        'concepto',
        'tipo_calculo',
        'valor_manual',
        'detalles_calculo',
        'orden',
    ];

    protected $casts = [
        'valor_manual' => 'decimal:2',
        'detalles_calculo' => 'array',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }
}
