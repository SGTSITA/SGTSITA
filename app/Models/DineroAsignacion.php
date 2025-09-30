<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importar SoftDeletes
use Illuminate\Support\Facades\Auth;

class DineroContenedor extends Model
{
    use HasFactory, SoftDeletes; // Usar SoftDeletes

    protected $table = 'dinero_contenedor';

   
    

    protected $dates = ['deleted_at']; // Indica que SoftDeletes usará la columna deleted_at
}