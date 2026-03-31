<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\Auditable;

class Equipo extends Model
{
    use HasFactory;
    use Auditable;
    protected $table = 'equipos';
    protected $appends = ['estado_gps','gps_info'];

    protected $fillable = [
        'tipo',
        'pies',
        'marca',
        'year',
        'motor',
        'num_serie',
        'modelo',
        'acceso',
        'tarjeta_circulacion',
        'poliza_seguro',
        'folio',
        'fecha',
        'id_equipo',
        'id_empresa',
        'placas',
        'imei',
        'user_id',
        'gps_company_id',
        'usar_config_global',
        'credenciales_gps'
    ];

    public function gps()
    {
        return $this->belongsTo(GpsCompany::class, 'gps_company_id');
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
    public function getEstadoGpsAttribute()
    {
        if ($this->usar_config_global) {
            return 'Global';
        }

        if (!empty($this->credenciales_gps)) {
            return 'Configurado';
        }

        return 'Sin config';
    }
    public function getGpsInfoAttribute()
    {
        $tipo =  $this->usar_config_global ? 'sistema' : 'personalizado';

        $tieneConfig = false;

        if ($this->usar_config_global) {
            $tieneConfig = !empty($this->gps_company_id);
        } else {
            $tieneConfig = !empty($this->credenciales_gps);
        }

        return [
            'tipo' => $tipo,
            'conectado' => $tieneConfig,
        ];
    }

}
