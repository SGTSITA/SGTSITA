<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScbBancoModulo extends Model
{
    use HasFactory;

     protected $table = 'scb_bancos_modulo';

    protected $fillable = [
        'nombre',
        'clave',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function cuentas()
    {
        return $this->hasMany(ScbBancoModuloCuenta::class, 'banco_id', 'id');
    }
}
