<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Contacto extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

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
