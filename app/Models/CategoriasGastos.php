<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class CategoriasGastos extends Model
{
    use HasFactory;
    use Auditable;
    protected $table = 'categorias_gastos';
}
