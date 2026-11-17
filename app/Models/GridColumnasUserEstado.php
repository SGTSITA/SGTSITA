<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GridColumnasUserEstado extends Model
{
    use HasFactory;
    protected $table = 'grid_columnas_user_estado';

    protected $fillable = [
        'user_id',
        'grid_key',
        'state_json'
    ];

    protected $casts = [
        'state_json' => 'array'
    ];
}
