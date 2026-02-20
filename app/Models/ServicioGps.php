<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class ServicioGps extends Model
{
    use HasFactory;
    use Auditable;
    protected $table = 'servicio_gps_empresa';

    protected $fillable = [
        'id_empresa',
        'id_gps_company'
    ];
}
