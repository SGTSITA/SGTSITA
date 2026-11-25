<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCatBancoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => ['required', 'string', 'max:10', 'unique:cat_bancos,codigo,' . $id],
            'razon_social' => ['nullable', 'string', 'max:150'],
            'logo' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
            'color_secundario' => ['nullable', 'string', 'max:20'],
            'catalog_key' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del banco es obligatorio.',
            'codigo.required' => 'El código del banco es obligatorio.',
            'codigo.unique' => 'El código de banco ya se encuentra registrado.',
        ];
    }
}
