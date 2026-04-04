<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GastosExtras extends Model
{
    use HasFactory;
    use Auditable;
    protected $table = 'gastos_extras';

    protected $fillable = [
        'id_cotizacion',
        'descripcion',
        'monto',
    ];

    public function Cotizacion()
    {
        return $this->belongsTo(Cotizaciones::class, 'id_cotizacion');
    }

    public function getAuditoriaData($old = [], $new = [])
    {
        $this->loadMissing('Cotizacion.DocCotizacion');

        return [
            'referencia' => $this->cotizacion?->DocCotizacion?->num_contenedor,
        ];
    }
}
