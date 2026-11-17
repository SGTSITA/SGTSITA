<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SocioConfiguracion extends Model
{
    use SoftDeletes;

    protected $table = 'socios_configuraciones';
    protected $fillable = ['id_empresa', 'socio_id', 'equipo_id', 'tipo_pago', 'valor', 'fecha_inicio', 'fecha_fin', 'activo'];

    public function socio()
    {
        return $this->belongsTo(Socio::class, 'socio_id')->withTrashed();
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id_empresa)) {
                $model->id_empresa = Auth::user()->id_empresa;
            }
        });
    }
}
