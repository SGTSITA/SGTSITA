<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BancoSaldoDiario extends Model
{
    use HasFactory;
    protected $table = 'banco_saldo_diario';
    protected $fillable = ['id_banco','fecha','saldo_inicial','saldo_final'];
}
