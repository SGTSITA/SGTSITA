<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Estado_Cuenta extends Model
{
    use Auditable;

    protected $table = 'estado_cuenta';

    protected $fillable = [
        'numero',
        'created_by',
        'id_empresa'
    ];

    /**
     * Usuario que creó / modificó el número
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Cotizaciones asociadas a este estado de cuenta
     */
    public function cotizaciones()
    {
        return $this->hasMany(Estado_Cuenta_Cotizaciones::class, 'estado_cuenta_id');
    }
    public function empresa()
    {
        return $this->belongsTo(
            Empresas::class,
            'id_empresa'
        );
    }
}
