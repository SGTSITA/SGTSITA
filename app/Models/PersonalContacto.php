<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalContacto extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'personal_contacto';

    protected $fillable = [
        'id_operadores',
        'telefono',
        'direccion',
        'correo',
        'nombre',
    ];

    public function Operadores()
    {
        return $this->belongsTo(Operador::class, 'id_operadores');
    }
}
