<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatBanco extends Model
{
    use HasFactory;

    protected $table = 'cat_bancos';

    protected $fillable = [
        'nombre',
        'codigo',
        'razon_social',
        'logo',
        'color',
        'color_secundario',
        'orden',
        'moneda',
        'pais',
        'activo',
        'id_empresa',
        'catalog_key',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function cuentas()
    {
        return $this->hasMany(Bancos::class, 'cat_banco_id');
    }

    /* =========================
     |  SCOPES
     ========================= */

    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }
}
