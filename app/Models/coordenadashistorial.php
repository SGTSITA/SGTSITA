<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class coordenadashistorial extends Model
{
    protected $table = 'coordenadas_historial';
    public $timestamps = false;

    protected $fillable = [
        'latitud',
        'longitud',
        'registrado_en',
        'user_id',
        'ubicacionable_id',
        'ubicacionable_type',
        'tipo',
        'id_convoy',

    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
