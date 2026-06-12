<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProveedores extends Model
{
    use HasFactory;

    protected $table='user_proveedores';


      protected $fillable = [
        'user_id',
            'proveedor_id',
    ];



}
