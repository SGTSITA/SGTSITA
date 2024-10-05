<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ComprobanteGastos extends Model
{
    use HasFactory;
    protected $table = 'comprobantes_gastos';

    protected $fillable = [
        'id_asignacion',
        'imagen',
        'tipo',
        'id_empresa',
    ];

    public function Asignaciones()
    {
        return $this->belongsTo(Asignaciones::class, 'id_asignacion');
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
}
