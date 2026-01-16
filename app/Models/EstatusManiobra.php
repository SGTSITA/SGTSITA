<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstatusManiobra extends Model
{
    protected $table = 'estatus_maniobras';

    public function cotizaciones()
    {
        return $this->hasMany(Cotizaciones::class, 'estatus_maniobra_id');
    }
}
