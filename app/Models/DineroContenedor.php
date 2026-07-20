<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Traits\Auditable;

class DineroContenedor extends Model
{
    use HasFactory; // Usar SoftDeletes
    use Auditable;

    protected $table = 'dinero_contenedor';

    # protected $dates = ['deleted_at']; // Indica que SoftDeletes usará la columna deleted_at

   public function DocCotizacion()
{
    return $this->belongsTo(
        DocumCotizacion::class,
        'id_contenedor',
        'id'
    );
}

    public function getAuditoriaData($old = [], $new = [])
    {
        $this->loadMissing('DocCotizacion');

        return [
            'referencia' => $this->DocCotizacion?->num_contenedor,
        ];
    }
}
