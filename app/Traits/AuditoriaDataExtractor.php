<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

class AuditoriaDataExtractor
{
    public static function extract(Model $model, $old = [], $new = []): array
    {

        if (method_exists($model, 'getAuditoriaData')) {
            return $model->getAuditoriaData($old, $new);
        }


        return [
            'referencia' => $new['id'] ?? $old['id'] ?? null,
        ];
    }
}
