<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_cliente' => ['nullable', 'integer'],
            'id_subcliente' => ['nullable', 'integer'],
            'origen' => ['nullable', 'string', 'max:255'],
            'destino' => ['nullable', 'string', 'max:255'],
            'num_contenedor' => ['nullable', 'string', 'max:50'],
            'tamano' => ['nullable', 'string', 'max:50'],
            'peso_contenedor' => ['nullable'],
            'fecha_entrega' => ['nullable'],
            'fecha_modulacion' => ['nullable'],
            'precio_viaje' => ['nullable'],
            'total' => ['nullable'],
            'estatus' => ['nullable', 'string', 'max:50'],
            'TipoCotizacion' => ['nullable', 'string'],
        ];
    }
}
