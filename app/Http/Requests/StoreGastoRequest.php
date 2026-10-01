<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGastoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'categoria_gasto_id' => ['nullable', 'integer'],
            'gasto_concepto_id' => ['nullable', 'integer'],
            'cotizacion_id' => ['nullable', 'integer'],
            'impacto'=> ['required','in:periodo,viaje,cotizacion'],
            'concepto' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],

            'monto_total' => ['required', 'numeric', 'min:0.01'],

            'moneda' => ['nullable', 'string', 'max:10'],

            'fecha_gasto' => ['required', 'date'],
            'fecha_operacion' => ['nullable', 'date'],

            'tipo_gasto' => [
                'required',
                'in:general,unidad,operador,viaje,cotizacion,periodo'
            ],

            'metodo_imputacion' => [
                'required',
                'in:directo,diferido'
            ],

            'vinculos' => ['nullable', 'array'],
            'vinculos.*.tipo_vinculo' => ['required_with:vinculos'],
            'vinculos.*.vinculable_type' => ['required_with:vinculos'],
            'vinculos.*.vinculable_id' => ['required_with:vinculos'],
            'vinculos.*.observaciones' => ['nullable', 'string'],

            'imputaciones' => ['nullable', 'array'],
            'imputaciones.*.fecha_imputacion' => ['nullable'],
            'imputaciones.*.tipo_imputacion' => ['nullable'],
            'imputaciones.*.imputable_type' => ['nullable'],
            'imputaciones.*.imputable_id' => ['nullable'],
            'imputaciones.*.monto_imputado' => ['nullable', 'numeric'],

            'programaciones' => ['nullable', 'array'],
            'programaciones.*.fecha_programada' => ['nullable'],
            'programaciones.*.fecha_vencimiento' => ['nullable'],
            'programaciones.*.monto_programado' => ['nullable', 'numeric'],

            'partidas' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'concepto.required' => 'Debe indicar el concepto.',
            'monto_total.required' => 'Debe indicar el monto.',
            'monto_total.numeric' => 'El monto debe ser numérico.',
            'fecha_gasto.required' => 'Debe indicar la fecha del gasto.',
            'tipo_gasto.required' => 'Debe indicar el tipo de gasto.',
        ];
    }
}
