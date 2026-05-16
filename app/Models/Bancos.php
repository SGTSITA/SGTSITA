<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importar SoftDeletes
use Illuminate\Support\Facades\Auth;
use App\Traits\Auditable;

class Bancos extends Model
{
    use Auditable;
    use HasFactory;
    use SoftDeletes; // Habilitar SoftDeletes para el borrado lógico

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
        'cat_banco_id', //para el nuevo banco
        'inicial_saldo' //para bancos nuevo no mesclar el anterior
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

    public function catBanco()
    {
        return $this->belongsTo(CatBanco::class, 'cat_banco_id');
    }
    public function movimientos()
    {
        return $this->hasMany(
            CatBancoCuentasMovimientos::class,
            'cuenta_bancaria_id'
        )->orderBy('fecha_movimiento')
         ->orderBy('id');
    }
    public function getSaldoActualAttribute()
    {
        $saldoInicial = (float) ($this->inicial_saldo ?? 0);

        $totalAbonos = $this->movimientos()
            ->where('tipo', 'abono')
            ->sum('monto');

        $totalCargos = $this->movimientos()
            ->where('tipo', 'cargo')
            ->sum('monto');

        return $saldoInicial + $totalAbonos - $totalCargos;
    }
    public function getTotalDepositosAttribute()
    {
        return $this->movimientos()
            ->where('tipo', 'abono')
            ->sum('monto');
    }

    public function getTotalCargosAttribute()
    {
        return $this->movimientos()
            ->where('tipo', 'cargo')
            ->sum('monto');
    }

    public function getConteoDepositosAttribute()
    {
        return $this->movimientos()
            ->where('tipo', 'abono')
            ->count();
    }

    public function getConteoCargosAttribute()
    {
        return $this->movimientos()
            ->where('tipo', 'cargo')
            ->count();
    }
    public function movimientosConSaldo()
    {
        $saldo = (float) ($this->inicial_saldo ?? 0);

        return $this->movimientos()
            ->orderBy('fecha_movimiento')
            ->get()
            ->map(function ($mov) use (&$saldo) {

                if ($mov->tipo === 'abono') {
                    $saldo += (float) $mov->monto;
                } else {
                    $saldo -= (float) $mov->monto;
                }

                $mov->saldo_resultante = $saldo;

                return $mov;
            });
    }


    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }


    public function scopeEliminados($query)
    {
        return $query->onlyTrashed();
    }
}
