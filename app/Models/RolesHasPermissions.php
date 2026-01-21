<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class RolesHasPermissions extends Model
{
    use HasFactory;
    use Auditable;

    public $timestamps = false;

    protected $table = "role_has_permissions";


    protected $fillable = [
        'permission_id', 'role_id'
    ];

}
