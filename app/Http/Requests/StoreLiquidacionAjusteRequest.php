<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLiquidacionAjusteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_liquidacion' => ['required', 'integer', 'exists:liquidaciones,id'],
            'fecha' => ['required', 'date'],
            'motivo' => ['required', 'string', 'min:3'],
            'id_banco' => ['nullable', 'integer', 'exists:bancos,id'],
            'partidas' => ['required', 'array', 'min:1'],
            'partidas.*.tipo' => ['required', 'in:percepcion,deduccion'],
            'partidas.*.categoria_concepto' => ['required', 'string', 'max:50'],
            'partidas.*.id_prestamo' => ['nullable', 'integer', 'exists:prestamos,id'],
            'partidas.*.descripcion' => ['nullable', 'string', 'max:500'],
            'partidas.*.monto' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_liquidacion.required' => 'Debe seleccionar una liquidación existente.',
            'id_liquidacion.exists' => 'La liquidación seleccionada no es válida.',
            'fecha.required' => 'La fecha del ajuste es requerida.',
            'motivo.required' => 'Debe especificar el motivo del ajuste.',
            'partidas.required' => 'Debe ingresar al menos un concepto en el ajuste.',
            'partidas.min' => 'Debe ingresar al menos un concepto en el ajuste.',
            'partidas.*.monto.min' => 'El monto de cada concepto debe ser mayor a cero.',
        ];
    }
}
