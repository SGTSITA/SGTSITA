<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class BalanceGeneralConceptoCuenta extends Model
{
    use HasFactory;

    protected $table = 'balance_general_conceptos_cuentas';

    protected $fillable = [
        'id_empresa',
        'seccion',
        'concepto',
        'cuenta_codigo_prefijo',
        'cuenta_id',
        'columna',
        'orden',
        'es_calculado',
        'activo',
    ];

    protected $casts = [
        'columna' => 'integer',
        'orden' => 'integer',
        'es_calculado' => 'boolean',
        'activo' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(CatCuentaContable::class, 'cuenta_id');
    }

    public function scopeParaEmpresa(Builder $query, ?int $idEmpresa = null): Builder
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        return $query->where('activo', true)
            ->where(function ($q) use ($idEmpresa) {
                if ($idEmpresa) {
                    $q->where('id_empresa', $idEmpresa)
                      ->orWhereNull('id_empresa');
                } else {
                    $q->whereNull('id_empresa');
                }
            })
            ->orderBy('seccion')
            ->orderBy('columna')
            ->orderBy('orden');
    }
}
