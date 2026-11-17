<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContabilidadEntidadCuenta extends Model
{
    use HasFactory;

    protected $table = 'contabilidad_entidades_cuentas';

    protected $fillable = [
        'id_empresa',
        'modulo',
        'entidad_id',
        'cuenta_contable_id',
        'activo',
    ];

    protected $casts = [
        'id_empresa'         => 'integer',
        'entidad_id'         => 'integer',
        'cuenta_contable_id' => 'integer',
        'activo'             => 'boolean',
    ];

    /**
     * Relación con la Empresa.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresas::class, 'id_empresa');
    }

    /**
     * Relación con la Cuenta Contable analítica.
     */
    public function cuentaContable(): BelongsTo
    {
        return $this->belongsTo(CatCuentaContable::class, 'cuenta_contable_id');
    }

    /**
     * Scope para buscar mapeo específico de una entidad en un módulo y empresa.
     */
    public function scopeParaEntidad($query, int $idEmpresa, string $modulo, int $entidadId)
    {
        return $query->where('id_empresa', $idEmpresa)
                     ->where('modulo', $modulo)
                     ->where('entidad_id', $entidadId)
                     ->where('activo', true);
    }
}
