<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GpsCompanyProveedor extends Model
{
    use Auditable;
    protected $table = 'gps_company_proveedores';

    protected $fillable = [
        'id_empresa',
        'id_proveedor',
        'id_gps_company',
        'account_info',
        'estado',
    ];

    protected $casts = [
        'account_info' => 'array',
        'estado'       => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id_empresa');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function gpsCompany()
    {
        return $this->belongsTo(GpsCompany::class, 'id_gps_company');
    }
}
