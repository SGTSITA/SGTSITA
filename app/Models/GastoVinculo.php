<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GastoVinculo extends Model
{
    use HasFactory;

    protected $table = 'gasto_vinculos';

    protected $fillable = [
        'gasto_id',
        'gasto_partida_id',
        'tipo_vinculo',
        'vinculable_type',
        'vinculable_id',
        'periodo_id',
        'observaciones',
    ];

    public function gasto()
    {
        return $this->belongsTo(Gasto::class, 'gasto_id');
    }

    public function partida()
    {
        return $this->belongsTo(GastoPartida::class, 'gasto_partida_id');
    }

    public function vinculable(): MorphTo
    {
        return $this->morphTo();
    }
}
