<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoPrestamo extends Model
{
    use HasFactory;
    protected $table = 'pagos_prestamos';

    protected $fillable = [
        'id_liquidacion',
        'id_prestamo',
        'saldo_anterior',
        'monto_pago'
    ];
}
