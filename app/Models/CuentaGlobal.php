<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaGlobal extends Model
{
    use HasFactory;

    protected $table = 'cuenta_globals';

    protected $fillable = [
        'nombre_beneficiario',
        'banco',
        'cuenta',
        'clabe',
    ];
}
