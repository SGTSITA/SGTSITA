<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiquidacionContenedor extends Model
{
    use HasFactory;
    protected $table = 'liquidacion_contenedor';

    public function Contenedores()
    {
        return $this->belongsTo(DocumCotizacion::class, 'id_contenedor','id_cotizacion');
    }

}
