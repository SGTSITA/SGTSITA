<?php

namespace App\Services;

use App\Models\Asignaciones;
use App\Models\Bancos;
use App\Models\ComprobanteGastos;
use App\Models\GastosOperadores;
use App\Models\Operador;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class OperadorService
{
    /**
     * Obtiene los operadores (incluyendo eliminados) y pagos pendientes para la vista index.
     */
    public function getOperadoresIndexData(?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        $operadores = Operador::withTrashed()
            ->where('id_empresa', $idEmpresa)
            ->orderBy('id', 'asc')
            ->get();

        $pagos_pendientes = Asignaciones::where('id_empresa', $idEmpresa)
            ->where('estatus_pagado', 'Pendiente Pago')
            ->get();

        return [
            'operadores' => $operadores,
            'pagos_pendientes' => $pagos_pendientes
        ];
    }

    /**
     * Obtiene los datos detallados de un operador (historial de pagos y comprobantes clasificados).
     */
    public function getOperadorDetalleData(int $id, ?int $idEmpresa = null): ?array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        $operador = Operador::where('id_empresa', $idEmpresa)
            ->where('id', $id)
            ->first();

        if (!$operador) {
            return null;
        }

        $pagos = Asignaciones::where('id_empresa', $idEmpresa)
            ->where('estatus_pagado', 'Pagado')
            ->where('id_operador', $id)
            ->get();

        $comprobantes_gasolina = ComprobanteGastos::join('asignaciones', 'comprobantes_gastos.id_asignacion', 'asignaciones.id')
            ->where('asignaciones.id_operador', $id)
            ->where('id_empresa', $idEmpresa)
            ->where('comprobantes_gastos.tipo', 'Gasolina')
            ->select('comprobantes_gastos.*')
            ->get();

        $comprobantes_casetas = ComprobanteGastos::join('asignaciones', 'comprobantes_gastos.id_asignacion', 'asignaciones.id')
            ->where('asignaciones.id_operador', $id)
            ->where('id_empresa', $idEmpresa)
            ->where('comprobantes_gastos.tipo', 'Casetas')
            ->select('comprobantes_gastos.*')
            ->get();

        $comprobantes_otros = ComprobanteGastos::join('asignaciones', 'comprobantes_gastos.id_asignacion', 'asignaciones.id')
            ->where('asignaciones.id_operador', $id)
            ->where('id_empresa', $idEmpresa)
            ->where('comprobantes_gastos.tipo', 'Otros')
            ->select('comprobantes_gastos.*')
            ->get();

        return [
            'operador' => $operador,
            'pagos' => $pagos,
            'comprobantes_gasolina' => $comprobantes_gasolina,
            'comprobantes_casetas' => $comprobantes_casetas,
            'comprobantes_otros' => $comprobantes_otros
        ];
    }

    /**
     * Registra un nuevo operador con carga de documentos de soporte.
     */
    public function storeOperador(array $data, array $files = [], ?int $idEmpresa = null): Operador
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        $operador = new Operador();
        $operador->id_empresa = $idEmpresa;
        $operador->curp = $data['curp'];
        $operador->nombre = $data['nombre'];
        $operador->correo = $data['correo'] ?? null;
        $operador->telefono = $data['telefono'] ?? null;
        $operador->domicilio = $data['domicilio'] ?? null;
        $operador->fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
        $operador->acceso = $data['acceso'] ?? null;
        $operador->tipo_sangre = $data['tipo_sangre'] ?? null;
        $operador->nss = $data['nss'] ?? null;
        $operador->recomendacion = $data['recomendacion'] ?? null;

        $documentosKeys = ['comprobante_domicilio', 'ine', 'cedula_fiscal', 'licencia_conducir', 'foto'];
        $hasFiles = false;
        foreach ($documentosKeys as $key) {
            if (isset($files[$key]) && $files[$key] instanceof \Illuminate\Http\UploadedFile) {
                $hasFiles = true;
                break;
            }
        }

        if ($hasFiles) {
            $path = public_path('operador');
            if (!file_exists($path)) {
                @mkdir($path, 0777, true);
            }

            foreach ($documentosKeys as $key) {
                if (isset($files[$key]) && $files[$key] instanceof \Illuminate\Http\UploadedFile) {
                    $file = $files[$key];
                    $fileName = uniqid() . '_' . $file->getClientOriginalName();
                    $file->move($path, $fileName);
                    $operador->{$key} = $fileName;
                }
            }
        }

        $operador->save();

        return $operador;
    }

    /**
     * Actualiza los datos de un operador.
     */
    public function updateOperador(int $id, array $data): Operador
    {
        $operador = Operador::findOrFail($id);
        $operador->update($data);

        return $operador;
    }

    /**
     * Aplica el pago de sueldo al operador y registra gastos desglosados con comprobantes.
     */
    public function updatePagoOperador(int $idAsignacion, array $data, array $files = []): Asignaciones
    {
        return DB::transaction(function () use ($idAsignacion, $data, $files) {
            $asignacion = Asignaciones::findOrFail($idAsignacion);
            $asignacion->id_banco1_pago_operador = $data['id_banco1_pago_operador'] ?? null;
            $asignacion->cantidad_banco1_pago_operador = $data['cantidad_banco1_pago_operador'] ?? null;
            $asignacion->id_banco2_pago_operador = $data['id_banco2_pago_operador'] ?? null;
            $asignacion->cantidad_banco2_pago_operador = $data['cantidad_banco2_pago_operador'] ?? null;
            $asignacion->fecha_pago_operador = date('Y-m-d');
            $asignacion->estatus_pagado = 'Pagado';
            $asignacion->update();

            $idOperador = $data['id_operador'] ?? $asignacion->id_operador;

            $conceptos = [
                'gasolina' => $data['gasolina_' . $idAsignacion] ?? [],
                'casetas' => $data['casetas'] ?? [],
                'otros' => $data['otros'] ?? [],
            ];

            foreach ($conceptos as $campo => $valores) {
                if (is_array($valores)) {
                    foreach ($valores as $monto) {
                        GastosOperadores::create([
                            'id_asignacion' => $asignacion->id,
                            'id_operador' => $idOperador,
                            $campo => $monto,
                        ]);
                    }
                }
            }

            $comprobanteTipos = [
                'comprobante_gasolina' => 'Gasolina',
                'comprobante_casetas' => 'Casetas',
                'comprobante_otros' => 'Otros',
            ];

            $path = public_path('/comprobantes');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            foreach ($comprobanteTipos as $inputKey => $tipoGasto) {
                if (isset($files[$inputKey]) && is_array($files[$inputKey])) {
                    foreach ($files[$inputKey] as $image) {
                        if ($image instanceof UploadedFile) {
                            $fileName = uniqid() . $image->getClientOriginalName();
                            $image->move($path, $fileName);

                            ComprobanteGastos::create([
                                'imagen' => $fileName,
                                'id_asignacion' => $asignacion->id,
                                'tipo' => $tipoGasto,
                            ]);
                        }
                    }
                }
            }

            return $asignacion;
        });
    }

    /**
     * Obtiene los pagos pendientes y las cuentas bancarias para el operador.
     */
    public function getPagosPendientesOperador(int $idOperador, ?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        $operador = Operador::findOrFail($idOperador);

        $pagos_pendientes = Asignaciones::where('id_empresa', $idEmpresa)
            ->where('estatus_pagado', 'Pendiente Pago')
            ->where('id_operador', $idOperador)
            ->get();

        $bancos = Bancos::where('id_empresa', $idEmpresa)->get();

        return [
            'operador' => $operador,
            'pagos_pendientes' => $pagos_pendientes,
            'bancos' => $bancos
        ];
    }

    /**
     * Da de baja lógica a un operador verificando que no tenga saldo pendiente de pago.
     */
    public function deleteOperador(int $id, ?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        $operador = Operador::findOrFail($id);

        $tieneRestante = Asignaciones::where('id_operador', $id)
            ->where('id_empresa', $idEmpresa)
            ->where('restante_pago_operador', '>', 0)
            ->exists();

        if ($tieneRestante) {
            return [
                'success' => false,
                'message' => "El operador <strong>{$operador->nombre}</strong> tiene pagos pendientes y no puede ser dado de baja."
            ];
        }

        $operador->delete();

        return [
            'success' => true,
            'message' => 'Operador dado de baja correctamente.'
        ];
    }

    /**
     * Reactiva un operador dado de baja.
     */
    public function restoreOperador(int $id): bool
    {
        $operador = Operador::withTrashed()->findOrFail($id);
        return (bool) $operador->restore();
    }
}
