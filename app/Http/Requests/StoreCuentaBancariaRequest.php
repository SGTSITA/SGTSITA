<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCuentaBancariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'banco_id' => ['required', 'integer', 'exists:cat_bancos,id'],
            'tipo_cuenta' => ['required', 'string'],
            'moneda' => ['required', 'string'],
            'numero_cuenta' => ['required', 'string', 'max:50'],
            'clabe' => ['nullable', 'string', 'max:18'],
            'beneficiario' => ['required', 'string', 'max:255'],
            'saldo_inicial' => ['required', 'numeric'],
            'banco_1' => ['nullable'],
            'principal' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'banco_id.required' => 'Debe seleccionar un banco de catálogo.',
            'banco_id.exists' => 'El banco seleccionado no es válido.',
            'tipo_cuenta.required' => 'El tipo de cuenta es obligatorio.',
            'moneda.required' => 'La moneda es obligatoria.',
            'numero_cuenta.required' => 'El número de cuenta es obligatorio.',
            'beneficiario.required' => 'El nombre del beneficiario es obligatorio.',
            'saldo_inicial.required' => 'El saldo inicial es obligatorio.',
        ];
    }
}
