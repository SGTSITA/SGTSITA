<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Empresas;
use App\Models\User;
use App\Traits\Auditable;

class UserEmpresa extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'users_empresas';

    protected $fillable = [
        'id_user',
        'id_empresa',
        'empresaInicial',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }


    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id_empresa');
    }
}
