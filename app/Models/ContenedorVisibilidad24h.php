<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContenedorVisibilidad24h extends Model
{
    use HasFactory;

    protected $table = 'contenedor_visibilidad_24h';

    protected $fillable = [
        'id_contenedor',
        'id_cotizacion',
        'id_empresa',
        'fecha_inicio_visibilidad',
        'fecha_fin_visibilidad',
        'visible'
    ];

    protected $casts = [
        'fecha_inicio_visibilidad' => 'datetime',
        'fecha_fin_visibilidad' => 'datetime',
        'visible' => 'boolean',
    ];

    public function DocCotizacion()
    {
        return $this->belongsTo(DocumCotizacion::class, 'id_contenedor');
    }

    public function Cotizacion()
    {
        return $this->belongsTo(Cotizaciones::class, 'id_cotizacion');
    }

    public function Empresa()
    {
        return $this->belongsTo(Empresas::class, 'id_empresa');
    }
}
