<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Socio extends Model
{
    use SoftDeletes;

    protected $table = 'socios';
    protected $fillable = ['id_empresa', 'nombre', 'rfc', 'telefono', 'email', 'activo'];

    public function configurations()
    {
        return $this->hasMany(SocioConfiguracion::class, 'socio_id');
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
