<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    protected array $exclude = [
        'updated_at',
        'created_at',
        'remember_token',
        'password',
    ];

    public function created(Model $model)
    {
        $this->log('created', $model, null, $model->getAttributes());
    }

    public function updated(Model $model)
    {
        ActivityLog::create([
            'model'      => class_basename($model),
            'model_id'   => $model->id,
            'action'     => 'updated',
            'old_values' => $model->getOriginal(),
            'new_values' => $model->getAttributes(),
            'user_id'    => auth()->id(),
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function deleted(Model $model)
    {
        $this->log('deleted', $model, $model->getOriginal(), null);
    }

    protected function log($action, Model $model, $old, $new)
    {
        ActivityLog::create([
            'model'      => class_basename($model),
            'model_id'   => $model->id,
            'action'     => $action,
            'old_values' => $old,
            'new_values' => $new,
            'user_id'    => auth()->id(),
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
