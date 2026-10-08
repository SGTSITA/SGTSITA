<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class EmpresaGps extends Model
{
    use SoftDeletes;
    use Auditable;
    protected $table = 'servicio_gps_empresa';


    public function serviciosGps()
    {
        return $this->hasMany(GpsCompany::class, 'id', 'id_gps_company');
    }
}
