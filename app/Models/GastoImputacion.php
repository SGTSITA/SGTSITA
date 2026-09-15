<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\Auditable;

class GastoImputacion extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'gasto_imputaciones';

    protected $fillable = [
        'gasto_id',
        'gasto_partida_id',
        'periodo_id',
        'fecha_imputacion',
        'tipo_imputacion',
        'imputable_type',
        'imputable_id',
        'monto_imputado',
        'origen',
    ];

    protected $casts = [
        'fecha_imputacion' => 'date',
        'monto_imputado' => 'decimal:2',
    ];

    public function gasto()
    {
        return $this->belongsTo(Gasto::class, 'gasto_id');
    }

    public function partida()
    {
        return $this->belongsTo(GastoPartida::class, 'gasto_partida_id');
    }

    public function imputable(): MorphTo
    {
        return $this->morphTo();
    }
}
