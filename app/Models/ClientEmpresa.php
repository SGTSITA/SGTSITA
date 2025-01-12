<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientEmpresa extends Model
{
    use HasFactory;
    protected $table = 'client_empresa';
    protected $fillable = ['id_client', 'id_empresa'];
}
