<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Cotizaciones extends Model
{
    use HasFactory;
    protected $table = 'cotizaciones';

    protected $fillable = [
        'id_cliente',
        'id_subcliente',
        'origen',
        'destino',
        'tamano',
        'peso_contenedor',
        'precio_viaje',
        'burreo',
        'maniobra',
        'estadia',
        'otro',
        'fecha_modulacion',
        'fecha_entrega',
        'iva',
        'retencion',
        'estatus',
        'sobrepeso',
        'peso_reglamentario',
        'peso_kg',
        'precio_sobre_peso',
        'precio_tonelada',
        'id_banco1',
        'id_banco2',
        'id_empresa',
        'prove_restante',
        'id_cuenta_prov',
        'id_cuenta_prov2',
        'latitud',
        'longitud',
        'direccion_mapa',
        'fecha_seleccion_ubicacion',
        'fecha_seleccion',

    ];

    public function Cliente()
    {
        return $this->belongsTo(Client::class, 'id_cliente');
    }

    public function Subcliente()
    {
        return $this->belongsTo(Subclientes::class, 'id_subcliente');
    }

    public function DocCotizacion()
    {
        return $this->hasOne(DocumCotizacion::class, 'id_cotizacion');
    }

    public function Bancos1()
    {
        return $this->hasOne(Bancos::class, 'id_banco1');
    }

    public function Bancos2()
    {
        return $this->hasOne(Bancos::class, 'id_banco2');
    }

    public function BancoProv()
    {
        return $this->hasOne(CuentasBancarias::class, 'id_cuenta_prov');
    }

    public function BancoProv2()
    {
        return $this->hasOne(CuentasBancarias::class, 'id_cuenta_prov2');
    }

    public function Empresa()
    {
        return $this->hasOne(Empresas::class, 'id_empresa');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($empresa) {
            $empresa->id_empresa = Auth::user()->id_empresa;
        });

        
    }
}
