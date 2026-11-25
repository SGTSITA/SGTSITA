<?php

namespace App\Http\Requests;

use App\Models\Operador;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateOperadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('curp')) {
            $this->merge([
                'curp' => strtoupper(trim((string) $this->input('curp')))
            ]);
        }
    }

    public function rules(): array
    {
        $operador = $this->route('id');
        $operadorId = $operador instanceof Operador ? $operador->id : $operador;
        $idEmpresa = auth()->user()?->id_empresa ?? 0;

        return [
            'curp' => [
                'required',
                'string',
                'size:18',
                'unique:operadores,curp,' . $operadorId . ',id,id_empresa,' . $idEmpresa,
            ],
            'nombre' => ['nullable', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'domicilio' => ['nullable', 'string', 'max:500'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'acceso' => ['nullable', 'string', 'max:255'],
            'tipo_sangre' => ['nullable', 'string', 'max:10'],
            'nss' => ['nullable', 'string', 'max:20'],
            'recomendacion' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'curp.required' => 'El campo CURP es obligatorio.',
            'curp.size' => 'La CURP debe tener exactamente 18 caracteres.',
            'curp.unique' => 'validacion_curp_personalizada',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->has('curp') && $v->errors()->first('curp') === 'validacion_curp_personalizada') {
                $operador = $this->route('id');
                $operadorId = $operador instanceof Operador ? $operador->id : $operador;
                $curp = $this->input('curp');
                $idEmpresa = auth()->user()?->id_empresa ?? 0;

                $existente = Operador::where('curp', $curp)
                    ->where('id_empresa', $idEmpresa)
                    ->where('id', '!=', $operadorId)
                    ->first();

                if ($existente) {
                    $v->errors()->add('curp_duplicada', "El CURP <strong>{$existente->curp}</strong> ya está registrado para el operador <strong>{$existente->nombre}</strong>.");
                }
            }
        });
    }
}
