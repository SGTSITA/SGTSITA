<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class BancoSaldoDiario extends Model
{
    use HasFactory;
    use Auditable;
    protected $table = 'banco_saldo_diario';
    protected $fillable = ['id_banco','fecha','saldo_inicial','saldo_final'];
}
