<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'configuracion';

    protected $fillable = [
        'nombre_sistema',
        'color_principal',
        'logo','favicon',
        'color_iconos_sidebar',
        'color_iconos_cards',
        'color_boton_add',
        'icon_boton_add',
        'color_boton_save',
        'icon_boton_save',
        'color_boton_close',
        'icon_boton_close',
    ];
}
