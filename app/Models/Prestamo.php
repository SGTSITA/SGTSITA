<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $table = 'prestamos'; // opcional, si la tabla se llama distinto

    protected $fillable = [
        'id_operador',
        'id_banco',
        'cantidad',
        'pagos',
        'saldo_actual'
    ];

    // Relación: un préstamo pertenece a un operador
    public function operador()
    {
        return $this->belongsTo(Operador::class, 'id_operador');
    }
}
