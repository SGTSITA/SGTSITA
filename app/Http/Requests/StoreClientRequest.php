<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['required', 'string', 'email', 'max:255'],
            'telefono' => ['required', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'regimen_fiscal' => ['nullable', 'string', 'max:255'],
            'rfc' => ['nullable', 'string', 'max:20'],
            'nombre_empresa' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El correo electrónico no tiene un formato válido.',
            'telefono.required' => 'El teléfono es obligatorio.',
        ];
    }
}
