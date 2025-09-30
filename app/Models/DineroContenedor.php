<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Support\Facades\Auth;

class DineroContenedor extends Model
{
    use HasFactory; // Usar SoftDeletes

    protected $table = 'dinero_contenedor';

   # protected $dates = ['deleted_at']; // Indica que SoftDeletes usará la columna deleted_at

    public function DocCotizacion()
    {
        return $this->hasOne(DocumCotizacion::class,'id_cotizacion', 'id_contenedor');
    }
}