<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GastoConcepto extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'gasto_conceptos';

    protected $fillable = [
        'categoria_gasto_id',
        'nombre',
        'clave',
        'tipo_default',
        'afecta_utilidad',
        'permite_diferir',
        'es_recuperable_cliente',
        'is_active',
    ];

    protected $casts = [
        'afecta_utilidad' => 'boolean',
        'permite_diferir' => 'boolean',
        'es_recuperable_cliente' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriasGastos::class, 'categoria_gasto_id');
    }
}
