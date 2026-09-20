<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class EstatusManiobra extends Model
{
    use HasFactory;
    use Auditable;
    protected $table = 'estatus_maniobras';

    public function cotizaciones()
    {
        return $this->hasMany(Cotizaciones::class, 'estatus_maniobra_id');
    }
}
