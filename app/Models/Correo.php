<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correo extends Model
{
    use HasFactory;

    protected $fillable = [
        'correo',
        'tipo_correo',
        'referencia',
        'notificacion_nueva',
        'cancelacion_viaje',
        'nuevo_documento',
        'viaje_modificado',
    ];
}