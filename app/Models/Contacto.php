<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contacto extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Tabla asociada (opcional si se llama "contactos")
    protected $table = 'contactos';

    // Campos que se pueden asignar en masa
    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'empresa',
        'foto',
        'tipo',
        'wa_id',
    ];

    // Activar timestamps
    public $timestamps = true;

    // Formato de fechas
    protected $dates = ['deleted_at'];
}
