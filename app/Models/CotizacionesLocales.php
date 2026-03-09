<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CotizacionesLocales extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones_locales';

    protected $fillable = [
        'id_contenedor',
        'id_subcliente',
        'id_empresa',
        'id_proveedor',
        'id_transportista',
        'origen',
        'tamano',
        'peso_contenedor',
        'fecha_modulacion',
        'puerto',
        'fecha_ingreso_puerto',
        'fecha_salida_puerto',
        'dias_estadia',
        'dias_pernocta',
        'tarifa_estadia',
        'tarifa_pernocta',
        'total_estadia',
        'total_pernocta',
        'total_general',
        'motivo_demora',
        'liberado',
        'fecha_liberacion',
        'responsable',
        'observaciones',
        'bloque',
        'bloque_hora_i',
        'bloque_hora_f',
        'num_pedimento',
        'tipo'
    ];

    public function contenedor()
    {
        return $this->belongsTo(DocumCotizacion::class, 'id_contenedor');
    }
}
