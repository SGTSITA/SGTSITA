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

            'id_equipo' => ['nullable', 'integer', 'exists:equipos,id'],
            'equipo_id' => ['nullable', 'integer', 'exists:equipos,id'],
            'unidades' => ['nullable', 'array'],
            'unidades.*' => ['integer', 'exists:equipos,id'],
            'partidas' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $idEmpresa = auth()->user()?->id_empresa;
            if (!$idEmpresa) {
                return;
            }

            $empresa = \App\Models\Empresas::withoutGlobalScopes()->find($idEmpresa);
            if ($empresa && $empresa->requiere_unidad_gasto) {
                $idEquipo = $this->id_equipo ?? $this->equipo_id ?? null;
                $unidades = $this->unidades;

                $tieneUnidad = !empty($idEquipo) || (!empty($unidades) && is_array($unidades) && count($unidades) > 0);

                if (!$tieneUnidad && $this->filled('viajes')) {
                    $viajes = is_array($this->viajes) ? $this->viajes : [$this->viajes];
                    $tieneCamion = \App\Models\Asignaciones::whereIn('id', $viajes)->whereNotNull('id_camion')->exists();
                    if ($tieneCamion) {
                        $tieneUnidad = true;
                    }
                } elseif (!$tieneUnidad && $this->filled('asignacion_id')) {
                    $tieneCamion = \App\Models\Asignaciones::where('id', $this->asignacion_id)->whereNotNull('id_camion')->exists();
                    if ($tieneCamion) {
                        $tieneUnidad = true;
                    }
                }

                if (!$tieneUnidad) {
                    $validator->errors()->add('id_equipo', 'La unidad/equipo es obligatoria para registrar gastos en esta empresa.');
                } elseif (!empty($idEquipo)) {
                    $pertenece = \App\Models\Equipo::where('id', $idEquipo)
                        ->where('id_empresa', $idEmpresa)
                        ->exists();
                    if (!$pertenece) {
                        $validator->errors()->add('id_equipo', 'La unidad seleccionada no pertenece a la empresa actual.');
                    }
                }
            }
        });
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
