<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\Auditable;

class Cotizaciones extends Model
{
    use HasFactory;
    use Auditable;
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
        'bloque',
        'bloque_hora_i',
        'bloque_hora_f',
        'latitud',
        'longitud',
        'direccion_mapa',
        'fecha_seleccion_ubicacion',
        'fecha_seleccion',
        'puerto',
        'cp_pedimento',
        'cp_clase_ped',
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
      'estatus_pago',
        'observaciones',
        'tipo_viaje_seleccion',
        'origen_local',
        'destino_local',
        'costo_maniobra_local',
        'estado_contenedor',
        'fecha_modulacion_local',
        'empresa_local',
        'sub_cliente_local',
        'transportista_local',
        'bloque_local',
        'bloque_hora_i_local',
        'bloque_hora_f_local',
        'en_patio',
        'fecha_en_patio',
        'origen_captura',
        'user_id',
        'estatus_maniobra_id',
        'confirmacion_sello',
        'nuevo_sello',
        'agente_aduanal'

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

    public function estatusManiobra() //para local y estatus
    {
        return $this->belongsTo(EstatusManiobra::class, 'estatus_maniobra_id');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($empresa) {
            $empresa->id_empresa = Auth::user()->id_empresa;
        });


    }
}
