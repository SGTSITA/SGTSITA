<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $table = 'prestamos'; 
    protected $appends = ['total_pagado'];

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

    public function banco()
    {
        return $this->belongsTo(Bancos::class, 'id_banco');
    }
    public function pagoprestamos()
    {
        return $this->hasMany(PagoPrestamo::class, 'id_prestamo');
    }

    public function getTotalPagadoAttribute()
    {
         return (float) $this->pagoprestamos->sum('monto_pago');
    }
    public function getSaldoCalculadoAttribute()
    {
        return max($this->cantidad - $this->total_pagado, 0);
    }
}
