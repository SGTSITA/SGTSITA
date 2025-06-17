<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastosOperadores extends Model
{
    use HasFactory;
    protected $table = 'gastos_operadores';
      public $timestamps = true;

    protected $fillable = [
        'id_asignacion',
        'id_operador',
        'id_cotizacion',
        'cantidad',
        'tipo',
        'comprobante',
    ];

    public function Asignaciones()
    {
        return $this->belongsTo(Asignaciones::class, 'id_asignacion');
    }

    public function Operador()
    {
        return $this->belongsTo(Operador::class, 'id_operador');
    }

    public function Cotizacion()
    {
        return $this->belongsTo(Cotizaciones::class, 'id_cotizacion');
    }
}
