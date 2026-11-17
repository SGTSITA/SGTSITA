<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaCuentaConfig extends Model
{
    use HasFactory;

    protected $table = 'cat_empresa_cuentas_config';

    protected $fillable = [
        'id_empresa',
        'rol_clave',
        'cuenta_contable_id',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'id_empresa'         => 'integer',
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
     * Relación con la Cuenta Contable.
     */
    public function cuentaContable(): BelongsTo
    {
        return $this->belongsTo(CatCuentaContable::class, 'cuenta_contable_id');
    }

    /**
     * Scope para buscar la configuración para una empresa, con fallback global (id_empresa IS NULL).
     */
    public function scopeParaEmpresaORoot($query, int $idEmpresa)
    {
        return $query->where(function ($q) use ($idEmpresa) {
            $q->where('id_empresa', $idEmpresa)
              ->orWhereNull('id_empresa');
        })->where('activo', true)
          ->orderByRaw('id_empresa IS NULL ASC'); // Prioriza la específica (no nula)
    }
}
