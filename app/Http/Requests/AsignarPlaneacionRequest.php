<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AsignarPlaneacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idContenendor' => ['nullable'],
            'id_contenedor' => ['nullable'],
            'cmbCamion' => ['nullable'],
            'cmbOperador' => ['nullable'],
            'cmbChasis' => ['nullable'],
            'cmbDolly' => ['nullable'],
            'cmbProveedor' => ['nullable'],
            'cmbBanco' => ['nullable'],
            'txtDineroViaje' => ['nullable', 'numeric'],
            'txtSueldoOperador' => ['nullable', 'numeric'],
            'FechaInicio' => ['nullable'],
            'FechaFin' => ['nullable'],
            'filasOtrosGastos' => ['nullable'],
        ];
    }
}
