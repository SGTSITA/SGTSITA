<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    use HasFactory;
    protected $table = 'catalogo';

    protected $fillable = [
        'id_cliente',
        'id_subcliente',
        'destino',
        'tamano',
        'peso_contenedor',
        'precio_viaje',
        'burreo',
        'maniobra',
        'estadia',
        'otro',
        'iva',
        'retencion',
        'sobrepeso',
        'peso_reglamentario',
        'peso_kg',
        'precio_sobre_peso',
        'precio_tonelada',
        'id_empresa',
    ];
    public function Cliente()
    {
        return $this->belongsTo(Client::class, 'id_cliente');
    }

    public function Subcliente()
    {
        return $this->belongsTo(Subclientes::class, 'id_subcliente');
    }

    public function Empresa()
    {
        return $this->belongsTo(Empresas::class, 'id_empresa');
    }
}
