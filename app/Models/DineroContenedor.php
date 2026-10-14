<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Traits\Auditable;

class DineroContenedor extends Model
{
    use HasFactory; // Usar SoftDeletes
    use Auditable;

    protected $table = 'dinero_contenedor';

    protected $fillable = [
        'id_contenedor',
        'id_banco',
        'motivo',
        'monto',
        'fecha_entrega_monto',
        'es_principal',
        'id_movimiento_bancario',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'monto' => 'decimal:2',
        'fecha_entrega_monto' => 'date',
    ];

    public function DocCotizacion()
    {
        return $this->belongsTo(
            DocumCotizacion::class,
            'id_contenedor',
            'id'
        );
    }

    public function banco()
    {
        return $this->belongsTo(Bancos::class, 'id_banco');
    }

    public function movimientoBancario()
    {
        return $this->belongsTo(CatBancoCuentasMovimientos::class, 'id_movimiento_bancario');
    }

    public function getAuditoriaData($old = [], $new = [])
    {
        $this->loadMissing('DocCotizacion');

        return [
            'referencia' => $this->DocCotizacion?->num_contenedor,
        ];
    }
}
