<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class ViaticosOperador extends Model
{
    use HasFactory;
    use Auditable;
    protected $table = "viaticos_operadores";
    protected $appends = ['contenedor'];

    public function getContenedorAttribute()
    {
        /* $contenedor = DocumCotizacion::where('id_cotizacion', $this->id_cotizacion)->first();
        return (!is_null($contenedor)) ? $contenedor->num_contenedor : 'S/N'; */
         return $this->Cotizacion
        ?->documCotizacion
        ?->first()
        ?->num_contenedor ?? 'S/N';
    }

    public function Cotizacion()
    {
        return $this->belongsTo(Cotizaciones::class, 'id_cotizacion', 'id');
    }

    public function getAuditoriaData($old = [], $new = [])
    {
        return [
        'referencia' => $this->contenedor,
    ];
    }
}
