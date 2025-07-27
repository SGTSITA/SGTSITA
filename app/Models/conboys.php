<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\conboysContenedores;
class conboys extends Model
{
     use HasFactory;

   protected $fillable = [
    'id','nombre', 'user_id',
     'no_conboy','fecha_inicio',
     'fecha_fin','tipo_disolucion',
     'estatus','fecha_disolucion',
     'geocerca_lat','geocerca_lng',
    'geocerca_radio'];

    // Relación: Un Conboy tiene muchos contenedores
    public function contenedores()
    {
        return $this->hasMany(conboysContenedores::class, 'conboy_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
