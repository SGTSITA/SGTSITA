<?php

namespace App\Http\Requests;

use App\Models\Operador;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOperadorRequest extends FormRequest
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
        $idEmpresa = auth()->user()?->id_empresa ?? 0;

        return [
            'curp' => [
                'required',
                'string',
                'size:18',
                'regex:/^[A-Z0-9]+$/',
                'unique:operadores,curp,NULL,id,id_empresa,' . $idEmpresa,
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'domicilio' => ['nullable', 'string', 'max:500'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'acceso' => ['nullable', 'string', 'max:255'],
            'tipo_sangre' => ['nullable', 'string', 'max:10'],
            'nss' => ['nullable', 'string', 'max:20'],
            'recomendacion' => ['nullable', 'string', 'max:500'],
            'comprobante_domicilio' => ['nullable', 'file', 'max:10240'],
            'ine' => ['nullable', 'file', 'max:10240'],
            'cedula_fiscal' => ['nullable', 'file', 'max:10240'],
            'licencia_conducir' => ['nullable', 'file', 'max:10240'],
            'foto' => ['nullable', 'file', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'curp.required' => 'El campo CURP es obligatorio.',
            'curp.size' => 'La CURP debe tener exactamente 18 caracteres.',
            'curp.regex' => 'La CURP no debe contener espacios ni caracteres especiales.',
            'curp.unique' => 'validacion_curp_personalizada',
            'nombre.required' => 'El nombre del operador es obligatorio.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->has('curp') && $v->errors()->first('curp') === 'validacion_curp_personalizada') {
                $curp = $this->input('curp');
                $idEmpresa = auth()->user()?->id_empresa ?? 0;
                $existente = Operador::where('curp', $curp)
                    ->where('id_empresa', $idEmpresa)
                    ->first();

                if ($existente) {
                    $v->errors()->add('curp_duplicada', "El CURP <strong>{$existente->curp}</strong> ya está registrado para el operador <strong>{$existente->nombre}</strong>.");
                }
            }
        });
    }
}
