<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Liquidaciones extends Model
{
    use HasFactory;
    protected $table = 'liquidaciones';

    public function Operadores()
    {
        return $this->belongsTo(Operador::class, 'id_operador');
    }

    public function Banco()
    {
        return $this->belongsTo(Bancos::class, 'id_banco');
    }

    public function Viajes()
    {
        return $this->hasMany(LiquidacionContenedor::class, 'id_liquidacion');
    }

}
