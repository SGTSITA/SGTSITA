<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class DocumCotizacionAcceso extends Model
{
    use Auditable;
    protected $table = 'docum_cotizacion_accesos';

    protected $fillable = [
        'documento_id',
        'proveedor_id',
        'token',
        'password_hash',
        'shared_files',
        'activo',
        'expires_at',
        'last_access_at',
        'last_ip',
        'user_agent',
        'user_id',
        'proveedor_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'expires_at' => 'datetime',
        'last_access_at' => 'datetime',
        'shared_files' => 'array',

    ];


    public function documento()
    {
        return $this->belongsTo(DocumCotizacion::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}
