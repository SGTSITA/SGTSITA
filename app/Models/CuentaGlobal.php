<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class CuentaGlobal extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'cuenta_globals';

    protected $fillable = [
        'nombre_beneficiario',
        'banco',
        'cuenta',
        'clabe',
    ];
}
