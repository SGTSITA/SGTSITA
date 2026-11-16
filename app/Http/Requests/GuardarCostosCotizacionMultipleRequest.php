<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarCostosCotizacionMultipleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'filas' => ['required', 'array', 'min:1'],
            'filas.*.id' => ['required', 'integer', 'exists:cotizaciones,id'],
            'filas.*.peso_contenedor' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'filas.*.peso_reglamentario' => ['nullable', 'numeric', 'min:0'],
            'filas.*.precio_viaje' => ['nullable'],
            'filas.*.burreo' => ['nullable'],
            'filas.*.maniobra' => ['nullable'],
            'filas.*.estadia' => ['nullable'],
            'filas.*.otro' => ['nullable'],
            'filas.*.precio_sobre_peso' => ['nullable'],
            'filas.*.base_factura' => ['nullable'],
            'filas.*.base_taref' => ['nullable'],
            'filas.*.iva' => ['nullable'],
            'filas.*.retencion' => ['nullable'],
            'filas.*.total' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'filas.required' => 'Debe enviar al menos una fila de cotización para guardar.',
            'filas.min' => 'Debe enviar al menos una fila de cotización para guardar.',
            'filas.*.id.required' => 'El identificador de la cotización es obligatorio.',
            'filas.*.id.exists' => 'Una de las cotizaciones seleccionadas no existe en el sistema.',
            'filas.*.peso_contenedor.max' => 'El peso del contenedor no puede exceder las 60 toneladas.',
        ];
    }
}
