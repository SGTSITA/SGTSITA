<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastosDiferidosDetalle extends Model
{
    use HasFactory;
    protected $table = 'gastos_diferidos_detalle';
    protected $primaryKey = null; // Indicar que no hay clave primaria
    public $incrementing = false;
}
