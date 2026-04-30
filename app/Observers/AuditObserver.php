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

    private function normalize($data)
    {
        return collect($data)->map(function ($value) {
            if (is_array($value) || is_object($value)) {
                return json_encode($value);
            }
            return $value;
        })->toArray();
    }

    public function created(Model $model)
    {
        $this->log('created', $model, null, $model->getAttributes());
    }

    public function updated(Model $model)
    {
        $old = $model->getRelation('__old')?->toArray() ?? [];
        $changes = $model->getChanges();

        $changesFiltrados = collect($changes)
               ->except($this->exclude)
               ->toArray();


        if (empty($changesFiltrados)) {
            return;
        }
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
        //         $requestData = request()?->except([
        //     '_token',
        //     'password',
        //     'password_confirmation'
        // ]) ?? [];

        $requestData = collect(request()->all())
    ->except($this->exclude)
    ->filter(fn ($v, $k) => array_key_exists($k, $model->getAttributes()))
    ->toArray() ?? [];

        $referencia =  AuditoriaDataExtractor::extract($model, $old, $new);

        //dd($referencia);

        $old = is_array($old) ? $old : [];
        $new = is_array($new) ? $new : [];

        $oldNormalized = $this->normalize($old);
        $newNormalized = $this->normalize($new);

        $cambios = array_diff_assoc($newNormalized, $oldNormalized);

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
