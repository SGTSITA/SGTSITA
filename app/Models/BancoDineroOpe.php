<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class BancoDineroOpe extends Model
{
    use HasFactory;
    use Auditable;
    protected $table = 'banco_dinero_operadores';

    protected $fillable = [
        'id_operador',
        'id_asignacion',
        'id_cotizacion',
        'id_banco1',
        'id_banco2',
        'monto1',
        'metodo_pago1',
        'comprobante_pago1',
        'monto2',
        'metodo_pago2',
        'comprobante_pago2',
        'fecha_pago',
        'tipo',
    ];
    public function Operador()
    {
        return $this->belongsTo(Operador::class, 'id_operador');
    }
    public function Asignacion()
    {
        return $this->belongsTo(Asignaciones::class, 'id_asignacion');
    }
    public function Cotizacion()
    {
        return $this->belongsTo(Cotizaciones::class, 'id_cotizacion');
    }
    public function Banco1()
    {
        return $this->belongsTo(Bancos::class, 'id_banco1');
    }
    public function Banco2()
    {
        return $this->belongsTo(Bancos::class, 'id_banco2');
    }
}
