<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScbUnidadModulo extends Model
{
    use HasFactory;

        protected $table = 'scb_bancos_unidades_modulo';

    protected $fillable = [
        'descripcion',
        'placas',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function detalles()
    {
        return $this->hasMany(
            ScbBancoModuloCuentaMovimientoDetalle::class,
            'unidad_id',
            'id'
        );
    }

}
