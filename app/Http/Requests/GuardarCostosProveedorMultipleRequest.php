<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarCostosProveedorMultipleRequest extends FormRequest
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
            'filas.*.id_proveedor' => ['nullable', 'integer', 'exists:proveedores,id'],
            'filas.*.precio_proveedor' => ['nullable'],
            'filas.*.burreo_proveedor' => ['nullable'],
            'filas.*.maniobra_proveedor' => ['nullable'],
            'filas.*.estadia_proveedor' => ['nullable'],
            'filas.*.sobrepeso_proveedor' => ['nullable'],
            'filas.*.total_tonelada' => ['nullable'],
            'filas.*.base1_proveedor' => ['nullable'],
            'filas.*.base2_proveedor' => ['nullable'],
            'filas.*.otro_proveedor' => ['nullable'],
            'filas.*.iva_proveedor' => ['nullable'],
            'filas.*.retencion_proveedor' => ['nullable'],
            'filas.*.total_proveedor' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'filas.required' => 'Debe enviar al menos una fila de proveedor para guardar.',
            'filas.min' => 'Debe enviar al menos una fila de proveedor para guardar.',
            'filas.*.id.required' => 'El identificador de la cotización es obligatorio.',
            'filas.*.id.exists' => 'Una de las cotizaciones seleccionadas no existe en el sistema.',
            'filas.*.id_proveedor.exists' => 'El proveedor seleccionado no es válido.',
        ];
    }
}
