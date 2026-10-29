<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class GuiaContabilizadora extends Model
{
    use HasFactory;

    protected $table = 'guia_contabilizadora';

    protected $fillable = [
        'id_empresa',
        'evento',
        'subtipo',
        'tipo_poliza',
        'cuenta_cargo_id',
        'cuenta_abono_id',
        'regla_dinamica',
        'descripcion_asiento',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function cuentaCargo(): BelongsTo
    {
        return $this->belongsTo(CatCuentaContable::class, 'cuenta_cargo_id');
    }

    public function cuentaAbono(): BelongsTo
    {
        return $this->belongsTo(CatCuentaContable::class, 'cuenta_abono_id');
    }

    public function scopeParaEvento(Builder $query, string $evento, ?int $idEmpresa = null): Builder
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        return $query->where('evento', $evento)
            ->where('activo', true)
            ->where(function ($q) use ($idEmpresa) {
                if ($idEmpresa) {
                    $q->where('id_empresa', $idEmpresa)
                      ->orWhereNull('id_empresa');
                } else {
                    $q->whereNull('id_empresa');
                }
            })
            // Primero las reglas específicas por empresa, luego las globales
            ->orderByRaw('id_empresa IS NULL ASC');
    }
}
