<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProveedorCuentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_beneficiario' => ['required', 'string', 'max:255'],
            'id_proveedores' => ['required', 'integer', 'exists:proveedores,id'],
            'cuenta_bancaria' => ['required', 'string', 'max:20'],
            'nombre_banco' => ['required', 'string', 'max:255'],
            'cuenta_clabe' => ['required', 'string', 'max:18'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_beneficiario.required' => 'El nombre del beneficiario es obligatorio.',
            'id_proveedores.required' => 'El proveedor es obligatorio.',
            'cuenta_bancaria.required' => 'El número de cuenta bancaria es obligatorio.',
            'nombre_banco.required' => 'El nombre del banco es obligatorio.',
            'cuenta_clabe.required' => 'La cuenta CLABE es obligatoria.',
        ];
    }
}
