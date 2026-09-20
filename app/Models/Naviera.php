<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Naviera extends Model
{
    protected $table = 'navieras';

    protected $fillable = [
        'naviera',
    ];


    public function documentos()
    {
        return $this->hasMany(DocumCotizacion::class, 'naviera_id');
    }
}
