<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <- Importar
use Illuminate\Support\Facades\Auth;
use App\Traits\Auditable;

class Operador extends Model
{
    use HasFactory;
    use SoftDeletes; // <- Usar el trait
    use Auditable;

    protected $table = 'operadores';

    protected $fillable = [
        'nombre',
        'domicilio',
        'fecha_nacimiento',
        'comprobante_domicilio',
        'ine',
        'cedula_fiscal',
        'licencia_conducir',
        'acceso',
        'correo',
        'telefono',
        'tipo_sangre',
        'nss',
        'recomendacion',
        'foto',
        'id_empresa',
        'curp',
    ];


    public function proveedores()
    {
        return $this->belongsToMany(
            Proveedor::class,
            'proveedor_operador',
            'operador_id',
            'proveedor_id'
        )->withPivot('empresa_id', 'estado')
         ->withTimestamps();
    }


    public function prestamos(){
         return $this->hasMany(Prestamo::class, 'id_operador', 'id');
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
