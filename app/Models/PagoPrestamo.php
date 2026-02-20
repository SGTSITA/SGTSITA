<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class PagoPrestamo extends Model
{
    use HasFactory;
    use Auditable;
    protected $table = 'pagos_prestamos';

    protected $fillable = [
        'id_liquidacion',
        'id_prestamo',
        'saldo_anterior',
        'monto_pago',
        'tipo_origen',
        'id_banco'  ,
        'referencia' ,
        'fecha_pago'
    ];
}
