<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class CatCuentaContable extends Model
{
    use HasFactory;

    protected $table = 'cat_cuentas_contables';

    protected $fillable = [
        'id_empresa',
        'codigo',
        'nombre',
        'nivel',
        'cuenta_padre_id',
        'rubro',
        'naturaleza',
        'es_afectable',
        'activo',
    ];

    protected $casts = [
        'nivel' => 'integer',
        'es_afectable' => 'boolean',
        'activo' => 'boolean',
    ];

    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cuenta_padre_id');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(self::class, 'cuenta_padre_id')->orderBy('codigo');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(PolizaMovimiento::class, 'cuenta_contable_id');
    }

    public function scopeAfectables(Builder $query): Builder
    {
        return $query->where('es_afectable', true);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeDisponiblesParaEmpresa(Builder $query, ?int $idEmpresa = null): Builder
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        return $query->where(function ($q) use ($idEmpresa) {
            $q->whereNull('id_empresa');
            if ($idEmpresa) {
                $q->orWhere('id_empresa', $idEmpresa);
            }
        });
    }

    public function getTextoSelectAttribute(): string
    {
        return "{$this->codigo} - {$this->nombre}";
    }
}
