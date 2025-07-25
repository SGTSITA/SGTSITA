<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Conboys;

class conboysContenedores extends Model
{
    use HasFactory;
    

    protected $fillable = ['conboy_id', 'id_contenedor','es_primero', 'usuario','imei'];

    // Relación: Un contenedor pertenece a un conboy
    public function conboy()
    {
        return $this->belongsTo(Conboys::class);
    }
}
