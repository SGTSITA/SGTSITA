<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JustificarGastosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numContenedor' => ['required', 'string', 'max:50'],
            'txtDescripcion' => ['required', 'string', 'max:255'],
            'montoJustificacion' => ['required', 'numeric', 'min:0.01'],
            'sinJustificar' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'numContenedor.required' => 'El contenedor es requerido.',
            'txtDescripcion.required' => 'La descripción del gasto es obligatoria.',
            'montoJustificacion.required' => 'El monto a justificar es obligatorio.',
            'montoJustificacion.min' => 'El monto a justificar debe ser mayor a 0.',
        ];
    }
}
