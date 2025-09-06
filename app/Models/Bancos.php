<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importar SoftDeletes
use Illuminate\Support\Facades\Auth;

class Bancos extends Model
{
    use HasFactory, SoftDeletes; // Habilitar SoftDeletes para el borrado lógico

    protected $table = 'bancos';

    protected $fillable = [
        'nombre_beneficiario',
        'nombre_banco',
        'cuenta_bancaria',
        'clabe',
        'saldo_inicial',
        'saldo',
        'tipo',
        'id_empresa',
        'estado', // Nuevo campo agregado para activar/desactivar
        'banco_1',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($banco) {
            $banco->id_empresa = Auth::user()->id_empresa;
        });

        static::updating(function ($banco) {
            $banco->id_empresa = Auth::user()->id_empresa;
        });
    }

    // Método para obtener solo los bancos activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    // Método para obtener solo los bancos eliminados lógicamente
    public function scopeEliminados($query)
    {
        return $query->onlyTrashed();
    }
}
