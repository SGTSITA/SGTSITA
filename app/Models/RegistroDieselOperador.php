<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroDieselOperador extends Model
{
    use HasFactory;

    protected $table = 'registros_diesel_operadores';

    protected $fillable = [
        'id_asignacion',
        'id_operador',
        'latitud',
        'longitud',
        'litros',
        'costo',
        'odometro',
        'comprobante',
    ];

    public function Asignacion()
    {
        return $this->belongsTo(Asignaciones::class, 'id_asignacion');
    }

    public function Operador()
    {
        return $this->belongsTo(Operadores::class, 'id_operador');
    }
}
