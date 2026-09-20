<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SocioPago extends Model
{
    use SoftDeletes;

    protected $table = 'socio_pagos';
    protected $fillable = [
        'id_empresa',
        'socio_id',
        'monto',
        'banco_id',
        'fecha_aplicacion',
        'user_id'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_aplicacion' => 'date'
    ];

    public function socio()
    {
        return $this->belongsTo(Socio::class, 'socio_id')->withTrashed();
    }

    public function banco()
    {
        return $this->belongsTo(Bancos::class, 'banco_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
