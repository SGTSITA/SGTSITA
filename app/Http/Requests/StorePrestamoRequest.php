<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrestamoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_operador' => ['required', 'integer', 'exists:operadores,id'],
            'id_banco' => ['required', 'integer', 'exists:bancos,id'],
            'cantidad' => ['required', 'numeric', 'min:0.01'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'FechaAplicacion' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_operador.required' => 'Debe seleccionar un operador válido.',
            'id_operador.exists' => 'El operador seleccionado no existe.',
            'id_banco.required' => 'Debe seleccionar una cuenta bancaria de egreso.',
            'id_banco.exists' => 'La cuenta bancaria seleccionada no es válida.',
            'cantidad.required' => 'El monto del préstamo es obligatorio.',
            'cantidad.min' => 'El monto debe ser mayor a cero.',
            'FechaAplicacion.required' => 'Debe especificar la fecha de aplicación.',
        ];
    }
}
