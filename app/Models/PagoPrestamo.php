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

     protected $casts = [
    'fecha_pago' => 'date',
];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'id_prestamo');
    }


     public function liquidacion()
    {
        return $this->belongsTo(Liquidaciones::class, 'id_liquidacion', 'id');
    }

     public function banco()
    {
        return $this->belongsTo(Bancos::class, 'id_banco', 'id');
    }
}
