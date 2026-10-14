<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalConfig extends Model
{
    use HasFactory;

    protected $table = 'global_configs';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Obtener el valor de una configuración por su clave.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getVal($key, $default = null)
    {
        $config = static::where('key', $key)->first();
        if (!$config) {
            return $default;
        }

        return $config->value;
    }

    /**
     * Guardar o actualizar una configuración global.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $description
     * @return static
     */
    public static function setVal($key, $value, $description = null)
    {
        $data = ['value' => $value];
        if ($description !== null) {
            $data['description'] = $description;
        }

        return static::updateOrCreate(
            ['key' => $key],
            $data
        );
    }
}
