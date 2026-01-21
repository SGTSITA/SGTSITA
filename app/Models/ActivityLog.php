<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\AuditoriaCifrado;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $fillable = [
        'model',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'user_id',
        'ip',
        'user_agent',
    ];


    public function setOldValuesAttribute($value)
    {
        $this->attributes['old_values'] = $value
            ? AuditoriaCifrado::encrypt($value)
            : null;
    }

    public function setNewValuesAttribute($value)
    {
        $this->attributes['new_values'] = $value
            ? AuditoriaCifrado::encrypt($value)
            : null;
    }


    public function getOldValuesAttribute($value)
    {
        return $value
            ? AuditoriaCifrado::decrypt($value)
            : null;
    }

    public function getNewValuesAttribute($value)
    {
        return $value
            ? AuditoriaCifrado::decrypt($value)
            : null;
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
