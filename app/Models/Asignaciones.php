<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Asignaciones extends Model
{
    use HasFactory;
    protected $table = 'asignaciones';

    protected $fillable = [
        'id_camion',
        'id_chasis',
        'id_dolys',
        'id_contenedor',
        'id_operador',
        'fecha_inicio',
        'fecha_fin',
        'id_proveedor',
        'precio',
        'id_chasis2',
        'sueldo_viaje',
        'dinero_viaje',
        'id_empresa',
        'id_banco1_dinero_viaje',
        'id_banco2_dinero_viaje',
        'otro2',
        'otro3',
        'otro4',
        'otro5',
        'otro6',
        'otro7',
        'otro8',
        'otro9',
    ];

    public function Camion()
    {
        return $this->belongsTo(Equipo::class, 'id_camion');
    }
    public function Chasis()
    {
        return $this->belongsTo(Equipo::class, 'id_chasis');
    }
    public function Chasis2()
    {
        return $this->belongsTo(Equipo::class, 'id_chasis2');
    }
    public function Doly()
    {
        return $this->belongsTo(Equipo::class, 'id_dolys');
    }
    public function Contenedor()
    {
        return $this->belongsTo(DocumCotizacion::class, 'id_contenedor');
    }
    public function Operador()
    {
        return $this->belongsTo(Operador::class, 'id_operador');
    }
    public function Proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }
    public function Banco1()
    {
        return $this->belongsTo(Bancos::class, 'id_banco1_dinero_viaje');
    }
    public function Banco2()
    {
        return $this->belongsTo(Bancos::class, 'id_banco2_dinero_viaje');
    }
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($empresa) {
            $empresa->id_empresa = Auth::user()->id_empresa;
        });

        static::updating(function ($empresa) {
            $empresa->id_empresa = Auth::user()->id_empresa;
        });
    }
}

