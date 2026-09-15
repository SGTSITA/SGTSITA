<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GastoPartida extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'gasto_partidas';

    protected $fillable = [
        'gasto_id',
        'categoria_gasto_id',
        'gasto_concepto_id',
        'concepto',
        'descripcion',
        'monto',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function gasto()
    {
        return $this->belongsTo(Gasto::class, 'gasto_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriasGastos::class, 'categoria_gasto_id');
    }

    public function conceptoCatalogo()
    {
        return $this->belongsTo(GastoConcepto::class, 'gasto_concepto_id');
    }
}
