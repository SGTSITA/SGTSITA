<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GpsCompany extends Model
{
    use SoftDeletes;

    protected $table = 'gps_company';

    protected $fillable = [
        'nombre',
        'url',
        'url_conexion',
        'telefono',
        'correo',
        'contacto'
    ];

    public function serviciosGps()
    {
        return $this->hasMany(ServicioGps::class, 'id_gps_company');
    }
}
