<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['required', 'email', 'max:255'],
            'telefono' => ['required', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'regimen_fiscal' => ['nullable', 'string', 'max:255'],
            'rfc' => ['nullable', 'string', 'max:13'],
            'tipo' => ['required', 'string', 'max:255'],
            'tipo_empresa' => ['required', 'string', 'in:lista,mep'],
            'tipo_viaje' => ['required', 'string', 'max:100'],
        ];

        if ($this->input('tipo_empresa') === 'lista') {
            $rules['id_empresa'] = ['required', 'integer', 'exists:empresas,id'];
        }

        if ($this->input('tipo_empresa') === 'mep') {
            $rules['password'] = ['required', 'string', 'min:6'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El correo electrónico no es válido.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'tipo_empresa.required' => 'Debe indicar el tipo de empresa.',
            'id_empresa.required' => 'Debe seleccionar una empresa existente.',
            'password.required' => 'Debe proporcionar una contraseña para el acceso MEP.',
            'password.min' => 'La contraseña debe contener al menos 6 caracteres.',
        ];
    }
}
