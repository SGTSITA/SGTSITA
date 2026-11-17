<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CierreContableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id_empresa'       => 'required|integer|exists:empresas,id',
            'fecha_inicio'     => 'required|date',
            'fecha_fin'        => 'required|date|after_or_equal:fecha_inicio',
            'nombre_ejercicio' => 'nullable|string|max:100',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'id_empresa.required'        => 'Debe seleccionar una empresa válida para el cierre contable.',
            'id_empresa.exists'          => 'La empresa seleccionada no existe.',
            'fecha_inicio.required'      => 'La fecha de inicio del periodo a cerrar es obligatoria.',
            'fecha_inicio.date'          => 'La fecha de inicio no tiene un formato válido.',
            'fecha_fin.required'         => 'La fecha de fin (corte) del periodo a cerrar es obligatoria.',
            'fecha_fin.date'             => 'La fecha de fin no tiene un formato válido.',
            'fecha_fin.after_or_equal'   => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            'nombre_ejercicio.max'       => 'El nombre del ejercicio no puede superar los 100 caracteres.',
        ];
    }
}
