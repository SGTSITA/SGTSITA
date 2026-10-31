<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Correo extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'correo',
        'tipo_correo',
        'referencia',
        'cotizacion_nueva',
        'cancelacion_viaje',
        'nuevo_documento',
        'viaje_modificado',
    ];
}
