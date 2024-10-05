<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastosGenerales extends Model
{
    use HasFactory;
    protected $table = 'gastos_generales';

    protected $fillable = [
        'motivo',
        'monto1',
        'metodo_pago1',
        'monto2',
        'metodo_pago2',
        'fecha',
        'id_banco1',
        'id_banco2',
        'id_empresa',
    ];

    public function Banco1()
    {
        return $this->belongsTo(Bancos::class, 'id_banco1');
    }

}
