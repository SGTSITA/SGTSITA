<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Subclientes extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'subclientes';

    protected $fillable = [
        'id_cliente',
        'nombre',
        'direccion',
        'rfc',
        'correo',
        'telefono',
        'regimen_fiscal',
        'email',
        'nombre_empresa',
        'fecha',
        'id_empresa',

    ];

    public function Cliente()
    {
        return $this->belongsTo(Client::class, 'id_cliente');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            $client->id_empresa = Auth::user()->id_empresa;
        });

        static::updating(function ($client) {
            $client->id_empresa = Auth::user()->id_empresa;
        });
    }
}
