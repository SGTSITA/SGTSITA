<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Poliza extends Model
{
    use HasFactory;

    protected $table = 'polizas';

    protected $fillable = [
        'id_empresa',
        'id_equipo',
        'tipo',
        'es_apertura',
        'numero',
        'fecha',
        'concepto',
        'origen_tipo',
        'origen_id',
        'estatus',
        'total_debe',
        'total_haber',
        'creado_por',
    ];

    protected $casts = [
        'es_apertura' => 'boolean',
        'fecha' => 'date',
        'total_debe' => 'decimal:2',
        'total_haber' => 'decimal:2',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(PolizaMovimiento::class, 'poliza_id')->orderBy('linea');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'id_equipo');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function origen(): MorphTo
    {
        return $this->morphTo(null, 'origen_tipo', 'origen_id');
    }

    /**
     * Valida que la póliza esté cuadrada (Partida Doble).
     */
    public function estaCuadrada(): bool
    {
        return round((float) $this->total_debe, 2) === round((float) $this->total_haber, 2);
    }
}
