<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferenciaBancariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cuenta_origen' => ['required', 'integer', 'exists:bancos,id', 'different:cuenta_destino'],
            'cuenta_destino' => ['required', 'integer', 'exists:bancos,id'],
            'concepto' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_aplicacion' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'cuenta_origen.required' => 'La cuenta de origen es obligatoria.',
            'cuenta_origen.different' => 'La cuenta de origen y destino deben ser diferentes.',
            'cuenta_destino.required' => 'La cuenta de destino es obligatoria.',
            'concepto.required' => 'El concepto de la transferencia es obligatorio.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'fecha_aplicacion.required' => 'La fecha de aplicación es obligatoria.',
        ];
    }
}
