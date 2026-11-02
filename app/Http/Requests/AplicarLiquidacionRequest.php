<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AplicarLiquidacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '_IdOperador' => ['required', 'integer', 'exists:operadores,id'],
            'bancoId' => ['nullable', 'integer', 'exists:bancos,id'],
            'FechaAplicacionPago' => ['required', 'string'],
            'totalMontoPago' => ['required', 'numeric'],
            'totalPagoPrestamo' => ['nullable', 'numeric', 'min:0'],
            'totalPagoAdelanto' => ['nullable', 'numeric', 'min:0'],
            'pagoContenedores' => ['required', 'array', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            '_IdOperador.required' => 'El operador es obligatorio.',
            '_IdOperador.exists' => 'El operador seleccionado no es válido.',
            'FechaAplicacionPago.required' => 'La fecha de aplicación es requerida.',
            'pagoContenedores.required' => 'Debe incluir al menos un contenedor para liquidar.',
        ];
    }
}
