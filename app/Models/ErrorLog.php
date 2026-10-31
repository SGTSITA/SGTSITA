<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
     use HasFactory;
    protected $table = 'error_log';

    // Los campos que se pueden guardar
    protected $fillable = [
        'type',
        'message',
        'file',
        'line',
        'trace',
        'user_id',
        'ip',
        'request'
    ];

    // Para convertir automáticamente "request" en array al obtenerlo
    protected $casts = [
        'request' => 'array',
    ];

    // Opcional: relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
