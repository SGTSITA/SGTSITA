<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbonarPrestamoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monto' => ['required', 'numeric', 'min:0.01'],
            'id_banco_abono' => ['required', 'integer', 'exists:bancos,id'],
            'fechaAbono' => ['required', 'string'],
            'referencia' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'monto.required' => 'El monto del abono es obligatorio.',
            'monto.min' => 'El monto del abono debe ser mayor a cero.',
            'id_banco_abono.required' => 'Debe seleccionar una cuenta bancaria de destino.',
            'id_banco_abono.exists' => 'La cuenta bancaria seleccionada no es válida.',
            'fechaAbono.required' => 'La fecha de abono es obligatoria.',
        ];
    }
}
