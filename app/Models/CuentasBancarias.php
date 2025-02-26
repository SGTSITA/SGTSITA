<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importar SoftDeletes
use Illuminate\Support\Facades\Auth;

class CuentasBancarias extends Model
{
    use HasFactory, SoftDeletes; // Usar SoftDeletes

    protected $table = 'cuentas_bancarias';

    protected $fillable = [
        'id_proveedores',
        'nombre_beneficiario',
        'cuenta_bancaria',
        'nombre_banco',
        'cuenta_clabe',
        'id_empresa',
        'activo' // Nuevo campo de activación
    ];

    protected $dates = ['deleted_at']; // Indica que SoftDeletes usará la columna deleted_at

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedores');
    }

    // 🔹 Scope para obtener solo cuentas activas
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    // 🔹 Agregar automáticamente el ID de la empresa
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cuentasBancarias) {
            $cuentasBancarias->id_empresa = Auth::user()->id_empresa;
        });

        static::updating(function ($cuentasBancarias) {
            $cuentasBancarias->id_empresa = Auth::user()->id_empresa;
        });
    }
}
