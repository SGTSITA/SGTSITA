<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class LiquidacionAjuste extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'liquidacion_ajustes';

    protected $fillable = [
        'id_empresa',
        'id_liquidacion',
        'id_operador',
        'id_banco',
        'fecha',
        'folio',
        'motivo',
        'total_percepciones',
        'total_deducciones',
        'total_neto',
        'estatus',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'total_percepciones' => 'float',
        'total_deducciones' => 'float',
        'total_neto' => 'float',
    ];

    public function liquidacion()
    {
        return $this->belongsTo(Liquidaciones::class, 'id_liquidacion');
    }

    public function operador()
    {
        return $this->belongsTo(Operador::class, 'id_operador');
    }

    public function banco()
    {
        return $this->belongsTo(Bancos::class, 'id_banco');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(LiquidacionAjusteDetalle::class, 'id_ajuste');
    }

    public function percepciones()
    {
        return $this->hasMany(LiquidacionAjusteDetalle::class, 'id_ajuste')->where('tipo', 'percepcion');
    }

    public function deducciones()
    {
        return $this->hasMany(LiquidacionAjusteDetalle::class, 'id_ajuste')->where('tipo', 'deduccion');
    }
}
