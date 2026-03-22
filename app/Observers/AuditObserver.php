<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use App\Services\AuditoriaCifrado;
use App\Traits\AuditoriaDataExtractor;

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
        $old = $model->getRelation('__old')?->toArray() ?? [];
        $changes = $model->getChanges();

        $oldFiltered = collect($old)
            ->only(array_keys($changes))
            ->toArray();

        $this->log('updated', $model, $oldFiltered, $changes);
    }
    public function updating(Model $model)
    {
        $model->setRelation('__old', collect($model->getOriginal()));
        // $model->oldValues = $model->getOriginal();
    }

    public function deleted(Model $model)
    {
        $this->log('deletedd', $model, $model->getOriginal(), $model->getAttributes());
    }

    protected function log($action, Model $model, $old, $new)
    {
        $requestData = request()?->except([
    '_token',
    'password',
    'password_confirmation'
]) ?? [];

        // dd($old, $new);

        $referencia =  AuditoriaDataExtractor::extract($model, $old, $new);

        $old = is_array($old) ? $old : [];
        $new = is_array($new) ? $new : [];

        $cambios = array_diff_assoc($new, $old);

        //dd($cambios);

        ActivityLog::create([
            'model'      => class_basename($model),
            'model_id'   => $model->id,
            'action'     => $action,
            'old_values' => $old,
            'new_values' => $new,
            'user_id'    => auth()->id(),
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_payload' => AuditoriaCifrado::encrypt($requestData),
             'campos_modificados' => !empty($cambios)
        ? json_encode(array_keys($cambios))
        : null,

    'empresa_id' => $model->empresa_id ?? auth()->user()->id_empresa,

    'referencia' =>  $referencia['referencia'] ?? null,
        ]);
    }
}
