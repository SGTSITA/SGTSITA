<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class ClientEmpresa extends Model
{
    use HasFactory;
    use Auditable;
    protected $table = 'client_empresa';
    protected $fillable = ['id_client', 'id_empresa'];
}
