<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coordenadas extends Model
{
    use HasFactory;

    protected $table = 'coordenadas';

    protected $fillable = [
        'id_asignacion',
        'id_cotizacion',
        'tipo_flujo',
        'registro_puerto',
        'dentro_puerto',
        'descarga_vacio',
        'cargado_contenedor',
        'fila_fiscal',
        'modulado_tipo',
        'modulado_coordenada',
        'en_destino',
        'inicio_descarga',
        'fin_descarga',
        'recepcion_doc_firmados',
        'tipo_flujo_datatime',
        'registro_puerto_datatime',
        'dentro_puerto_datatime',
        'descarga_vacio_datatime',
        'cargado_contenedor_datatime',
        'fila_fiscal_datatime',
        'modulado_tipo_datatime',
        'modulado_coordenada_datatime',
        'en_destino_datatime',
        'inicio_descarga_datatime',
        'fin_descarga_datatime',
        'recepcion_doc_firmados_datatime',
        'cargado_patio',
        'cargado_patio_datetime',
        'descarga_patio',
        'descarga_patio_datatime',
        'tipo_c_estado' ,
        'tipo_b_estado',
        'tipo_f_estado',
    ];

    public function Cotizacion()
    {
        return $this->belongsTo(Cotizaciones::class, 'id_cotizacion');
    }

    public function Asignaciones()
    {
        return $this->belongsTo(Asignaciones::class, 'id_asignacion');
    }
}
