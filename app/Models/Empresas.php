<?php

// App\Models\Empresas.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresas extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'direccion',
        'rfc',
        'correo',
        'telefono',
        'regimen_fiscal',
        'email',
        'fecha',
        'id_configuracion',
    ];

    public function configuracion()
    {
        return $this->belongsTo(Configuracion::class, 'id_configuracion');
    }
}
