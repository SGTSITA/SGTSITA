<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class MovimientoBancario extends Model
{
    use HasFactory;
    use Auditable;
    protected $table = 'movimientos_bancarios';
}
