<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Proveedor extends Model
{
    use HasFactory;
    protected $table = 'proveedores';
    public static $forceEmpresaFromAuth = true;

    protected $fillable = [
        'nombre',
        'direccion',
        'rfc',
        'correo',
        'telefono',
        'regimen_fiscal',
        'fecha',
        'tipo',
        'id_empresa',
        'tipo_viaje'
    ];

    public function CuentasBancarias()
    {
        return $this->hasMany(CuentasBancarias::class, 'id_proveedores');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id_empresa');
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_proveedores',
            'proveedor_id',
            'user_id'
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($empresa) {
            if (static::$forceEmpresaFromAuth) {
                $empresa->id_empresa = Auth::user()->id_empresa;
            }
        });

        static::updating(function ($empresa) {
            $empresa->id_empresa = Auth::user()->id_empresa;
        });
    }


    //scopes para catalogos princiapal y local

    public function scopeCatalogoPrincipal($query)
    {
        return $query->whereIn('tipo_viaje', ['foraneo', 'local_foraneo']);
    }
    public function scopeCatalogoLocal($query)
    {
        return $query->whereIn('tipo_viaje', ['local', 'local_foraneo']);
    }
}
