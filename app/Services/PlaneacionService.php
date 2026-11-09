<?php

namespace App\Services;

use App\Models\Asignaciones;
use App\Models\Bancos;
use App\Models\Client;
use App\Models\Coordenadas;
use App\Models\Cotizaciones;
use App\Models\DineroContenedor;
use App\Models\DocumCotizacion;
use App\Models\Equipo;
use App\Models\Gasto;
use App\Models\GastosOperadores;
use App\Models\Operador;
use App\Models\User;
use App\Models\ViaticosOperador;
use App\Traits\CommonTrait as Common;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlaneacionService
{
    protected BancosService $bancosService;
    protected GastosService $gastosService;
    protected OperadorGastosContabilidadService $operadorGastosContabilidadService;

    public function __construct(
        BancosService $bancosService,
        GastosService $gastosService,
        OperadorGastosContabilidadService $operadorGastosContabilidadService
    ) {
        $this->bancosService = $bancosService;
        $this->gastosService = $gastosService;
        $this->operadorGastosContabilidadService = $operadorGastosContabilidadService;
    }

    /**
     * Carga datos para el tablero interactivo de planeación (Bryntum/Gantt/Board).
     */
    public function initBoard(array $filters, int $idEmpresa, $user): array
    {
        $userProveedores = User::find($user->id);
        $proveedorIds = $userProveedores?->proveedores()->pluck('proveedor_id') ?? collect();
        $isAdmin = (bool) $user->es_admin;

        $fromDate = $filters['fromDate'] ?? now()->format('Y-m-d');
        $toDate = $filters['toDate'] ?? now()->addDays(7)->format('Y-m-d');

        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('asignaciones.fecha_inicio', [$fromDate, $toDate])
                    ->orWhereBetween('asignaciones.fecha_fin', [$fromDate, $toDate])
                    ->orWhere(function ($q2) use ($fromDate, $toDate) {
                        $q2->where('asignaciones.fecha_inicio', '<=', $fromDate)
                            ->where('asignaciones.fecha_fin', '>=', $toDate);
                    });
            })
            ->when(!$isAdmin, function ($q) use ($idEmpresa) {
                $q->when($idEmpresa != 0, function ($q2) use ($idEmpresa) {
                    $q2->whereRaw(
                        "COALESCE(NULLIF(asignaciones.id_empresa, 0), cotizaciones.id_empresa) = ?",
                        [$idEmpresa]
                    );
                });
            })
            ->where('cotizaciones.estatus', 'Aprobada')
            ->where('estatus_planeacion', '=', 1)
            ->when($userProveedores && $userProveedores->proveedores()->exists(), function ($query) use ($proveedorIds) {
                $query->whereIn('cotizaciones.id_proveedor', $proveedorIds);
            })
            ->select('asignaciones.*', 'docum_cotizacion.num_contenedor', 'cotizaciones.id_cliente', 'cotizaciones.referencia_full', 'cotizaciones.tipo_viaje')
            ->orderBy('fecha_inicio')
            ->get();

        $extractor = $planeaciones->map(function ($p) {
            $itemNumContenedor = $p->num_contenedor;
            if (!is_null($p->referencia_full)) {
                $cotizacionFull = Cotizaciones::where('referencia_full', $p->referencia_full)->where('jerarquia', 'Secundario')->first();
                if ($cotizacionFull) {
                    $contenedorSecundario = DocumCotizacion::where("id_cotizacion", $cotizacionFull->id)->first();
                    if ($contenedorSecundario) {
                        $itemNumContenedor .= " / " . $contenedorSecundario->num_contenedor;
                    }
                }
            }
            return [
                "fecha_inicio" => $p->fecha_inicio,
                "fecha_fin" => $p->fecha_fin,
                "id_contenedor" => $p->id_contenedor,
                "id_cliente" => $p->id_cliente,
                "num_contenedor" => $itemNumContenedor
            ];
        });

        $clientes = $planeaciones->unique('id_cliente')->pluck('id_cliente');
        $clientesData = Client::whereIn('id', $clientes)->selectRaw("id, nombre as name, 'true' as expanded")->get();

        $board = [
            ["name" => "Clientes", "id" => "S", "expanded" => true, "children" => $clientesData]
        ];

        return [
            "boardCentros" => $board,
            "extractor" => $extractor,
            "scrollDate" => $fromDate,
            "TMensaje" => "success",
            "planeaciones" => $planeaciones,
        ];
    }

    /**
     * Anula la planeación de un viaje/contenedor, revirtiendo asignación, anticipos y gastos.
     * Consumido tanto por el panel web como por la API móvil (ApiValidationController::anularPlaneacionMobile).
     */
    public function anularPlaneacion(int|string $idContenedor, ?string $numContenedor, int $idEmpresa, $user): array
    {
        return DB::transaction(function () use ($idContenedor, $numContenedor) {
            $documenCotizacion = DocumCotizacion::findOrFail($idContenedor);
            $cotizaciones = Cotizaciones::findOrFail($documenCotizacion->id_cotizacion);
            $asignaciones = Asignaciones::where('id_contenedor', '=', $documenCotizacion->id)->first();

            if ($asignaciones && !is_null($asignaciones->id_operador) && !is_null($asignaciones->id_banco1_dinero_viaje)) {
                $movimientoBanco = $this->bancosService->findMovimiento(
                    $asignaciones->id,
                    Asignaciones::class,
                    $asignaciones->id_banco1_dinero_viaje
                );

                if ($movimientoBanco) {
                    $cancelarMovimientoBanco = $this->bancosService->cancelarMovimiento(
                        $asignaciones->id_banco1_dinero_viaje,
                        $movimientoBanco->id,
                        $movimientoBanco->fecha_movimiento
                    );

                    if (!$cancelarMovimientoBanco) {
                        throw new \Exception('No se pudo cancelar el movimiento bancario, dinero para viaje.');
                    }
                }
            }

            if (!is_null($cotizaciones->referencia_full)) {
                Cotizaciones::where('referencia_full', $cotizaciones->referencia_full)->update(["estatus_planeacion" => 0]);
            }

            $cotizaciones->estatus = 'Aprobada';
            $cotizaciones->estatus_planeacion = 0;
            $cotizaciones->update();

            // Validar gastos asociados a la asignación y cancelarlos
            if ($asignaciones) {
                $gastosUnified = Gasto::where('origen_legacy', 'like', 'asignacion_planeacion%')
                    ->where('origen_legacy_id', $asignaciones->id)
                    ->get();

                foreach ($gastosUnified as $g) {
                    foreach ($g->pagos as $pago) {
                        if ($pago->estatus !== 'cancelado') {
                            $movimientoBancoGasto = $this->bancosService->findMovimiento(
                                $g->id,
                                Gasto::class,
                                $pago->cuenta_bancaria_id
                            );
                            if ($movimientoBancoGasto) {
                                $this->bancosService->cancelarMovimiento(
                                    $pago->cuenta_bancaria_id,
                                    $movimientoBancoGasto->id,
                                    $movimientoBancoGasto->fecha_movimiento
                                );
                            }
                            $pago->update(['estatus' => 'cancelado']);
                        }
                    }
                    $g->vinculos()->delete();
                    $g->imputaciones()->delete();
                    $g->delete();
                }

                GastosOperadores::withoutGlobalScope('no_eliminados')->where('id_asignacion', $asignaciones->id)->delete();
                Coordenadas::where('id_asignacion', $asignaciones->id)->delete();
                $asignaciones->delete();
            }

            DineroContenedor::where('id_contenedor', $documenCotizacion->id)->delete();
            ViaticosOperador::where('id_cotizacion', $cotizaciones->id)->delete();

            return [
                "Titulo" => "Programa cancelado",
                "Mensaje" => "Se canceló el programa del viaje correctamente",
                "TMensaje" => "success",
            ];
        });
    }

    /**
     * Valida y finaliza un viaje/contenedor, verificando documentación obligatoria y planeación elemental.
     */
    public function finalizarViaje(int|string $idContenedor, int $idEmpresa): array
    {
        $contenedor = DocumCotizacion::find($idContenedor);
        if (!$contenedor) {
            return ["Titulo" => "Error", "Mensaje" => "Contenedor no encontrado", "TMensaje" => "error", "code" => 404];
        }

        $cotizaciones = Cotizaciones::find($contenedor->id_cotizacion);
        if (!$cotizaciones) {
            return ["Titulo" => "Error", "Mensaje" => "Cotización no encontrada", "TMensaje" => "error", "code" => 404];
        }

        // Validación de Planeación Elemental para viajes propios
        $asignacion = Asignaciones::where('id_contenedor', '=', $contenedor->id)->first();
        if ($asignacion && $asignacion->tipo_contrato === 'Propio') {
            $erroresElemental = [];
            if (empty($asignacion->dinero_viaje) || $asignacion->dinero_viaje == 0) {
                $erroresElemental[] = "Dinero para viaje";
            }
            if (empty($asignacion->id_banco1_dinero_viaje) || $asignacion->id_banco1_dinero_viaje == 0) {
                $erroresElemental[] = "Banco seleccionado";
            }
            if (empty($asignacion->fecha_inicio)) {
                $erroresElemental[] = "Fecha de inicio";
            }
            if (empty($asignacion->sueldo_viaje) || $asignacion->sueldo_viaje == 0) {
                $erroresElemental[] = "Sueldo operador";
            }

            $tieneGastosUnified = Gasto::where('origen_legacy_id', $asignacion->id)
                ->where('origen_legacy', 'like', 'asignacion_planeacion%')
                ->exists();
            $tieneGastosVinculo = \App\Models\GastoVinculo::where('vinculable_id', $asignacion->id)
                ->where('vinculable_type', '=', 'App\\Models\\Asignaciones')
                ->exists();

            if (!($tieneGastosUnified || $tieneGastosVinculo)) {
                $erroresElemental[] = "Gastos capturados";
            } else {
                $tieneDieselUnified = Gasto::where('origen_legacy_id', $asignacion->id)
                    ->where('origen_legacy', 'like', 'asignacion_planeacion%')
                    ->where(function ($q) {
                        $q->where('concepto', 'like', '%GDI02%')
                          ->orWhere('tipo_gasto', 'like', '%GDI02%');
                    })
                    ->exists();
                $tieneDieselVinculo = \App\Models\GastoVinculo::where('vinculable_id', $asignacion->id)
                    ->where('vinculable_type', '=', 'App\\Models\\Asignaciones')
                    ->whereHas('gasto', fn ($q) => $q->where('concepto', 'like', '%GDI02%'))
                    ->exists();

                if (!($tieneDieselUnified || $tieneDieselVinculo)) {
                    $erroresElemental[] = "Gasto Diésel GDI02";
                }
            }

            if (!empty($erroresElemental)) {
                return [
                    "Titulo" => "Planeación Elemental Activa",
                    "Mensaje" => "El viaje tiene una planeación elemental activa y tiene que completarla para poder finalizarla. Faltan: " . implode(", ", $erroresElemental),
                    "TMensaje" => "warning",
                ];
            }
        }

        // Validación de documentos
        $isCima = !empty($contenedor->cima);
        $missingDocs = [];

        if (empty($contenedor->doc_ccp)) {
            $missingDocs[] = "Carta Porte (CCP)";
        }
        if (empty($contenedor->doda)) {
            $missingDocs[] = "DODA";
        }
        if (empty($contenedor->boleta_liberacion)) {
            $missingDocs[] = "Boleta de Liberación";
        }
        if (!$isCima && empty($contenedor->doc_eir)) {
            $missingDocs[] = "EIR";
        }
        if (empty($cotizaciones->carta_porte)) {
            $missingDocs[] = "Carta Porte PDF";
        }
        if (empty($cotizaciones->carta_porte_xml)) {
            $missingDocs[] = "Carta Porte XML";
        }
        if (empty($cotizaciones->img_boleta)) {
            $missingDocs[] = "Boleta de Vacío";
        }

        if (!empty($missingDocs)) {
            return [
                "Titulo" => "Documentos faltantes",
                "Mensaje" => "No se puede finalizar el viaje. Faltan documentos requeridos: " . implode(", ", $missingDocs),
                "TMensaje" => "error",
            ];
        }

        $cotizaciones->estatus = 'Finalizado';
        $cotizaciones->save();

        return [
            "Titulo" => "Viaje finalizado",
            "Mensaje" => "Has finalizado correctamente el viaje",
            "TMensaje" => "success",
        ];
    }

    /**
     * Registra o actualiza la asignación de recursos y fechas para un viaje.
     */
    public function asignacion(array $data, int $idEmpresa, $user): array
    {
        $cantineroViaje = !empty($data['txtDineroViaje']) ? floatval($data['txtDineroViaje']) : 0.0;
        $idBancoViaje = !empty($data['cmbBanco']) ? intval($data['cmbBanco']) : null;
        $fechaAplicacionDinero = $data['FechaAplicacionDinero'] ?? now()->format('d/m/Y');

        // Validar saldo para anticipo de viaje
        if ($cantineroViaje > 0 && $idBancoViaje) {
            $validarSaldos = $this->bancosService->validarsaldoparacargo($idEmpresa, $idBancoViaje, $fechaAplicacionDinero, $cantineroViaje);
            if (isset($validarSaldos["saldodisponible"]) && $validarSaldos["saldodisponible"] === false) {
                return [
                    "TMensaje" => "error",
                    "Titulo" => "Saldo bancos",
                    "Mensaje" => $validarSaldos["message"],
                    'success' => false,
                    'cotizacion_data' => []
                ];
            }
        }

        // Validar saldo para otros gastos
        $otrosGastos = !empty($data['filasOtrosGastos']) ? (is_array($data['filasOtrosGastos']) ? $data['filasOtrosGastos'] : json_decode($data['filasOtrosGastos'], true)) : [];
        if ($otrosGastos && is_array($otrosGastos)) {
            $gastosPorBanco = [];
            foreach ($otrosGastos as $gasto) {
                $monto = floatval($gasto['monto'] ?? 0);
                $esPagoInmediato = !empty($gasto['pagoInmediato']);
                $idBanco = !empty($gasto['banco']) ? intval($gasto['banco']) : null;

                if ($esPagoInmediato && $idBanco) {
                    $fechaGasto = !empty($gasto['fechaAplicacion']) ? Carbon::createFromFormat('d/m/Y', $gasto['fechaAplicacion'])->format('Y-m-d') : now()->format('Y-m-d');
                    $gastosPorBanco[$idBanco][] = [
                        'monto' => $monto,
                        'fecha' => $fechaGasto,
                    ];
                }
            }

            foreach ($gastosPorBanco as $idcuenta => $gastosBanco) {
                $banco = Bancos::where('id_empresa', $idEmpresa)->where('id', $idcuenta)->first();
                if (!$banco) {
                    return [
                        "Titulo" => "Banco no encontrado",
                        "Mensaje" => "El banco seleccionado no existe o no pertenece a la empresa.",
                        "TMensaje" => "error",
                        'success' => false,
                    ];
                }

                usort($gastosBanco, fn ($a, $b) => strtotime($a['fecha']) <=> strtotime($b['fecha']));
                $fechaInicial = $gastosBanco[0]['fecha'];
                $movimientos = $this->bancosService->validarsaldoparacargo($idEmpresa, $idcuenta, $fechaInicial, 0);
                $saldoSimulado = $movimientos["saldoActual"];

                foreach ($gastosBanco as $gasto) {
                    $saldoSimulado -= $gasto['monto'];
                    if ($saldoSimulado < 0) {
                        return [
                            "Titulo" => "Saldo para gastos",
                            "Mensaje" => "Saldo insuficiente para fecha " . $gasto['fecha'] . ". Faltan $" . number_format(abs($saldoSimulado), 2),
                            "TMensaje" => "error",
                            'success' => false,
                        ];
                    }
                }
            }
        }

        // Procesar asignación dentro de transacción
        return DB::transaction(function () use ($data, $idEmpresa, $user, $cantineroViaje, $idBancoViaje, $fechaAplicacionDinero, $otrosGastos) {
            $numContenedoresRaw = $data['num_contenedor'] ?? null;
            $numContenedores = is_string($numContenedoresRaw) ? json_decode($numContenedoresRaw, true) : $numContenedoresRaw;
            if (!is_array($numContenedores)) {
                $numContenedores = !empty($numContenedoresRaw) ? [$numContenedoresRaw] : [];
            }
            $numContenedor = !empty($numContenedores) ? $numContenedores[0] : '';

            $fechaInicio = Common::TransformaFecha($data['txtFechaInicio'] ?? now()->format('Y-m-d'));
            $fechaFinal = Common::TransformaFecha($data['txtFechaFinal'] ?? now()->format('Y-m-d'));

            $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)->firstOrFail();
            $cotizacion = Cotizaciones::where('id', '=', $contenedor->id_cotizacion)->firstOrFail();

            $asignaciones = Asignaciones::where('id_contenedor', $contenedor->id)->first() ?? new Asignaciones();
            $asignaciones->id_contenedor = $contenedor->id;
            $asignaciones->id_empresa = $cotizacion->id_empresa;
            $asignaciones->fecha_inicio = $fechaInicio;
            $asignaciones->fecha_fin = $fechaFinal . ' 23:00:00';
            $asignaciones->fehca_inicio_guard = $fechaInicio;
            $asignaciones->fehca_fin_guard = $fechaFinal . ' 23:00:00';
            $asignaciones->save();

            $viajePropio = 0;
            $tipoViaje = $data['tipoViaje'] ?? 'propio';

            if ($tipoViaje === "propio") {
                $viajePropio = 1;
                $asignaciones->id_chasis = $data['cmbChasis'] ?? null;
                $asignaciones->id_chasis2 = $data['cmbChasis2'] ?? null;
                $asignaciones->id_dolys = $data['cmbDoly'] ?? null;
                $asignaciones->id_camion = $data['cmbCamion'] ?? null;
                $asignaciones->id_operador = $data['cmbOperador'] ?? null;
                $asignaciones->sueldo_viaje = $data['txtSueldoOperador'] ?? 0;
                $asignaciones->estatus_pagado = 'Pendiente Pago';
                $asignaciones->dinero_viaje = $cantineroViaje;
                $asignaciones->id_banco1_dinero_viaje = $idBancoViaje;
                $asignaciones->cantidad_banco1_dinero_viaje = $cantineroViaje;

                $sueldoOperador = floatval($data['txtSueldoOperador'] ?? 0);
                $resta = $sueldoOperador - $cantineroViaje;
                $asignaciones->pago_operador = $resta;
                $asignaciones->restante_pago_operador = $resta;
                $asignaciones->tipo_contrato = 'Propio';

                if (empty($data['cmbProveedor']) && $cantineroViaje > 0) {
                    $dineroViaje = new DineroContenedor();
                    $dineroViaje->id_contenedor = $asignaciones->id_contenedor;
                    $dineroViaje->id_banco = $idBancoViaje;
                    $dineroViaje->motivo = 'Dinero para viaje';
                    $dineroViaje->monto = $cantineroViaje;
                    $dineroViaje->es_principal = 1;
                    $dineroViaje->fecha_entrega_monto = Carbon::createFromFormat('d/m/Y', $fechaAplicacionDinero)->format('Y-m-d');
                    $dineroViaje->save();

                    $operadorN = Operador::find($data['cmbOperador'] ?? null);
                    $dataBanco = [
                        'cuenta_bancaria_id' => $idBancoViaje,
                        'tipo' => 'cargo',
                        'monto' => $cantineroViaje,
                        'concepto' => 'Dinero para viaje ' . $contenedor->num_contenedor . ' ' . ($operadorN?->nombre ?? ''),
                        'fecha_movimiento' => Carbon::createFromFormat('d/m/Y', $fechaAplicacionDinero)->format('Y-m-d'),
                        'origen' => 'sistema',
                        'referencia' => 'del ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($fechaFinal)->format('d/m/Y'),
                        'detalles' => json_encode([['num_contenedor' => $contenedor->num_contenedor, 'abono' => $cantineroViaje]]),
                        'referenciaable_id' => $asignaciones->id,
                        'referenciaable_type' => Asignaciones::class,
                    ];

                    $movimientoCrear = $this->bancosService->registrarMovimiento($dataBanco);
                    if (!$movimientoCrear) {
                        throw new \Exception('No se pudo crear el movimiento bancario, dinero para viaje.');
                    }
                    $dineroViaje->id_movimiento_bancario = $movimientoCrear->id;
                    $dineroViaje->save();
                }
            } else {
                $asignaciones->id_proveedor = $data['cmbProveedor'] ?? null;
                $asignaciones->precio = $data['precio_proveedor'] ?? 0;
                $asignaciones->burreo = $data['burreo_proveedor'] ?? 0;
                $asignaciones->maniobra = $data['maniobra_proveedor'] ?? 0;
                $asignaciones->estadia = $data['estadia_proveedor'] ?? 0;
                $asignaciones->otro = $data['otro_proveedor'] ?? 0;
                $asignaciones->iva = $data['iva_proveedor'] ?? 0;
                $asignaciones->retencion = $data['retencion_proveedor'] ?? 0;
                $asignaciones->total_proveedor = $data['total_proveedor'] ?? 0;
                $asignaciones->sobrepeso_proveedor = $data['sobrepeso_proveedor'] ?? 0;
                $asignaciones->base1_proveedor = $data['base_factura'] ?? 0;
                $asignaciones->base2_proveedor = $data['base_taref'] ?? 0;
                $asignaciones->total_tonelada = $data['cantidad_sobrepeso_proveedor'] ?? 0;
                $asignaciones->tipo_contrato = $data['tipoViaje'] ?? 'Subcontratado';
                $asignaciones->prove_restante = $asignaciones->total_proveedor;
            }

            $passwordTemporal = (string) mt_rand(100000, 999999);
            $asignaciones->password_temporal = $passwordTemporal;
            $asignaciones->save();

            $this->operadorGastosContabilidadService->sincronizarPlaneacion($asignaciones);

            $litrosDiesel = $data['litros_diesel'] ?? $data['gasto_litros_diesel'] ?? 0;
            $litrosUrea = $data['litros_urea'] ?? $data['gasto_litros_urea'] ?? 0;
            $cotizacion->litros_diesel = $litrosDiesel;
            $cotizacion->litros_urea = $litrosUrea;
            $cotizacion->estatus_planeacion = 1;
            $cotizacion->tipo_viaje = 'Sencillo';
            $cotizacion->save();

            if (count($numContenedores) >= 2) {
                $fullUUID = Common::generarUuidV4();
                foreach ($numContenedores as $i => $cont) {
                    $cDoc = DocumCotizacion::where('num_contenedor', $cont)->first();
                    if ($cDoc) {
                        $cot = Cotizaciones::find($cDoc->id_cotizacion);
                        if ($cot) {
                            $cot->referencia_full = $fullUUID;
                            $cot->jerarquia = ($i == 0) ? 'Principal' : 'Secundario';
                            $cot->estatus_planeacion = 1;
                            $cot->tipo_viaje = 'Full';
                            $cot->save();
                        }
                    }
                }
            }

            if ($viajePropio && !empty($otrosGastos)) {
                $this->guardarOtrosGastosPlaneacion($otrosGastos, $contenedor->num_contenedor, $data['cmbOperador'] ?? null, $idEmpresa, $user);
            }

            $operadorN = !empty($data['cmbOperador']) ? Operador::find($data['cmbOperador']) : null;
            $camionN = !empty($data['cmbCamion']) ? Equipo::find($data['cmbCamion']) : null;
            $chasisN = !empty($data['cmbChasis']) ? Equipo::find($data['cmbChasis']) : null;

            $waText = "🚛 *Detalles del Viaje Programado* 🚛

"
                . "📦 *Contenedor:* " . implode(" / ", $numContenedores) . "
"
                . "📍 *Origen:* " . ($cotizacion->origen ?? 'N/D') . "
"
                . "🏁 *Destino:* " . ($cotizacion->destino ?? 'N/D') . "
"
                . "📅 *Fecha Inicio:* " . Carbon::parse($fechaInicio)->format('d/m/Y') . "
"
                . "📅 *Fecha Fin:* " . Carbon::parse($fechaFinal)->format('d/m/Y') . "
"
                . "👤 *Operador:* " . ($operadorN ? $operadorN->nombre : 'Subcontratado') . "
"
                . "🚚 *Unidad:* " . ($camionN ? $camionN->id_equipo . ' (' . $camionN->placas . ')' : 'N/A') . "
"
                . "🔗 *Chasis:* " . ($chasisN ? $chasisN->id_equipo : 'N/A') . "
"
                . "🔑 *Contraseña Temporal:* " . $passwordTemporal;

            return [
                "TMensaje" => "success",
                "Titulo" => "Planeado correctamente",
                "Mensaje" => "Se ha programado correctamente el viaje del contenedor",
                "success" => true,
                "cotizacion_data" => ["tipo_viaje" => $cotizacion->tipo_viaje],
                "password_temporal" => $passwordTemporal,
                "wa_text" => $waText,
                "id_asignacion" => $asignaciones->id,
            ];
        });
    }

    /**
     * Guarda gastos asociados al viaje durante la planeación.
     */
    public function guardarOtrosGastosPlaneacion(array $filas, string $numContenedor, $idOperadorViaje, int $idEmpresa, $user): array
    {
        $respuesta = null;
        $descripcionGastosPermitidos = [
            'GCM01' => 'GCM01 - Comisión',
            'GDI02' => 'GDI02 - Diésel',
            'GBV01' => 'GBV01 - Burrero Vacío',
            'GU001' => 'GU001 - Urea',
            'OTR01' => 'Otros'
        ];

        $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)
            ->where('id_empresa', $idEmpresa)
            ->first() ?? DocumCotizacion::where('num_contenedor', $numContenedor)->first();

        if (!$contenedor) {
            return [];
        }

        $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();
        if (!$asignacion) {
            return [];
        }

        foreach ($filas as $gastoItem) {
            $motivo = $gastoItem['motivo'] ?? null;
            $monto = floatval($gastoItem['monto'] ?? 0);
            $esPagoInmediato = !empty($gastoItem['pagoInmediato']);
            $idBanco = !empty($gastoItem['banco']) ? intval($gastoItem['banco']) : null;
            $fechaAplicacionInput = $gastoItem['fechaAplicacion'] ?? null;

            $fechaAplicacion = null;
            if (!empty($fechaAplicacionInput)) {
                try {
                    $fechaAplicacion = Carbon::createFromFormat('d/m/Y', trim($fechaAplicacionInput))->format('Y-m-d');
                } catch (\Throwable $e) {
                    $fechaAplicacion = Carbon::parse($fechaAplicacionInput)->format('Y-m-d');
                }
            }

            if (!$motivo || !isset($descripcionGastosPermitidos[$motivo])) {
                continue;
            }
            if ($monto <= 0) {
                continue;
            }

            if ($esPagoInmediato && empty($idBanco)) {
                $respuesta = [
                    "Titulo" => "Debe seleccionar banco",
                    "Mensaje" => "Ha seleccionado pago inmediato. Por favor, indique el banco desde el cual se realizará el retiro.",
                    "TMensaje" => "warning"
                ];
                continue;
            }

            $tipoGasto = $motivo === 'OTR01' ? ($gastoItem['conceptoOtro'] ?? 'Otros') : $descripcionGastosPermitidos[$motivo];

            $categoriaId = null;
            $conceptoId = null;

            if ($motivo === 'GDI02') {
                $cat = DB::table('categorias_gastos')->where('categoria', 'like', '%Combustible%')->first();
                $categoriaId = $cat?->id ?? 1;
                $con = DB::table('gasto_conceptos')->where('clave', 'DSL')->first();
                $conceptoId = $con?->id;
            } elseif ($motivo === 'GCM01') {
                $cat = DB::table('categorias_gastos')->where('categoria', 'like', '%Comisiones de venta%')->first();
                $categoriaId = $cat?->id ?? 5;
                $con = DB::table('gasto_conceptos')->where('clave', 'COM_VTA')->first();
                $conceptoId = $con?->id;
            } else {
                $cat = DB::table('categorias_gastos')->where('categoria', 'like', '%Otros%')->first();
                $categoriaId = $cat?->id ?? 12;

                $claveConcepto = $motivo === 'GBV01' ? 'OTR_BV' : ($motivo === 'GU001' ? 'OTR_UR' : 'OTR_GEN');
                $nombreConcepto = $motivo === 'GBV01' ? 'GBV01 - Burrero Vacío' : ($motivo === 'GU001' ? 'GU001 - Urea' : 'Otros');

                $con = \App\Models\GastoConcepto::where('clave', $claveConcepto)->first();
                if (!$con) {
                    $con = new \App\Models\GastoConcepto();
                    $con->categoria_gasto_id = $categoriaId;
                    $con->clave = $claveConcepto;
                    $con->nombre = $nombreConcepto;
                    $con->tipo_default = 'viaje';
                    $con->afecta_utilidad = true;
                    $con->is_active = true;
                    $con->save();
                }
                $conceptoId = $con->id;
            }

            $vinculosData = [
                [
                    'tipo_vinculo' => 'cotizacion',
                    'vinculable_type' => Cotizaciones::class,
                    'vinculable_id' => $contenedor->id_cotizacion,
                ],
                [
                    'tipo_vinculo' => 'contenedor',
                    'vinculable_type' => DocumCotizacion::class,
                    'vinculable_id' => $contenedor->id,
                ],
                [
                    'tipo_vinculo' => 'asignacion',
                    'vinculable_type' => Asignaciones::class,
                    'vinculable_id' => $asignacion->id,
                ],
            ];
            if ($asignacion->id_operador) {
                $vinculosData[] = [
                    'tipo_vinculo' => 'operador',
                    'vinculable_type' => Operador::class,
                    'vinculable_id' => $asignacion->id_operador,
                ];
            }
            if ($asignacion->id_camion) {
                $vinculosData[] = [
                    'tipo_vinculo' => 'unidad',
                    'vinculable_type' => Equipo::class,
                    'vinculable_id' => $asignacion->id_camion,
                ];
            }

            $gasto = $this->gastosService->registrar([
                'id_empresa' => $idEmpresa,
                'id_equipo' => $asignacion->id_camion,
                'categoria_gasto_id' => $categoriaId,
                'gasto_concepto_id' => $conceptoId,
                'concepto' => $tipoGasto,
                'monto_total' => $monto,
                'tipo_gasto' => 'viaje',
                'estatus' => $esPagoInmediato ? 'pagado' : 'pendiente_pago',
                'fecha_gasto' => Carbon::now(),
                'origen_legacy' => 'asignacion_planeacion' . $tipoGasto,
                'origen_legacy_id' => $asignacion->id,
                'user_id' => $user->id,
                'vinculos' => $vinculosData,
                'imputaciones' => [
                    [
                        'fecha_imputacion' => Carbon::now(),
                        'tipo_imputacion' => 'viaje',
                        'imputable_type' => Asignaciones::class,
                        'imputable_id' => $asignacion->id,
                        'monto_imputado' => $monto,
                        'origen' => 'directo',
                    ]
                ]
            ]);

            if ($esPagoInmediato && $idBanco && $gasto) {
                $this->gastosService->pagar($gasto, [
                    'cuenta_bancaria_id' => $idBanco,
                    'monto' => $monto,
                    'fecha_pago' => $fechaAplicacion ?? now()->format('Y-m-d'),
                    'concepto_banco' => BancosService::generarConcepto('gop', $tipoGasto, $numContenedor, $asignacion->Operador?->nombre ?? ($asignacion->id_operador ? Operador::find($asignacion->id_operador)?->nombre : null)),
                    'referencia_banco' => 'GASTO_PLANEACION_VIAJE',
                ]);
            }
        }

        return $respuesta ?? [
            "Titulo" => "Gasto agregado",
            "Mensaje" => "Se agregaron los gastos correctamente.",
            "TMensaje" => "success"
        ];
    }

    /**
     * Reprograma fechas de viajes en lote.
     */
    public function reprogramarViajes(array $viajes, $user): array
    {
        return DB::transaction(function () use ($viajes) {
            foreach ($viajes as $v) {
                $idContenedor = is_object($v) ? $v->id : $v['id'];
                $asignacion = Asignaciones::where('id_contenedor', $idContenedor)->first();
                if ($asignacion) {
                    $start = is_object($v) ? $v->start : $v['start'];
                    $end = is_object($v) ? $v->end : $v['end'];
                    $fechaInicio = str_replace('T', ' ', $start);
                    $fechaFinal = str_replace('T', ' ', $end);

                    $asignacion->fecha_inicio = $fechaInicio;
                    $asignacion->fecha_fin = $fechaFinal;
                    $asignacion->fehca_inicio_guard = $fechaInicio;
                    $asignacion->fehca_fin_guard = $fechaFinal;
                    $asignacion->save();
                }
            }

            return [
                "TMensaje" => "success",
                "Titulo" => "Reprogramación exitosa",
                "Mensaje" => "Se ha realizado la reprogramación de fechas exitosamente",
                "success" => true,
            ];
        });
    }

    /**
     * Cambia fechas de un viaje específico.
     */
    public function cambioFechas(int $idAsignacion, string $fechaInicioRaw, string $fechaFinRaw): array
    {
        $fechaInicio = Common::TransformaFecha($fechaInicioRaw);
        $fechaFinal = Common::TransformaFecha($fechaFinRaw);

        $asignaciones = Asignaciones::findOrFail($idAsignacion);
        $asignaciones->fecha_inicio = $fechaInicio;
        $asignaciones->fecha_fin = $fechaFinal . ' 23:00:00';
        $asignaciones->fehca_inicio_guard = $fechaInicio;
        $asignaciones->fehca_fin_guard = $fechaFinal . ' 23:00:00';
        $asignaciones->save();

        return [
            "TMensaje" => "success",
            "Titulo" => "Fechas cambiadas",
            "Mensaje" => "Se han cambiado las fechas del viaje exitosamente",
            "success" => true,
        ];
    }

    /**
     * Actualiza la planeación de un viaje/cotización existente, sincronizando asignación, anticipos y gastos.
     */
    public function updatePlaneacion(int $idCotizacion, array $data, int $idEmpresa, $user): array
    {
        return DB::transaction(function () use ($idCotizacion, $data, $idEmpresa, $user) {
            $cotizacion = Cotizaciones::findOrFail($idCotizacion);
            $contenedor = $cotizacion->DocCotizacion;

            $fechaInicio = Common::TransformaFecha($data['txtFechaInicio'] ?? now()->format('Y-m-d'));
            $fechaFinal = Common::TransformaFecha($data['txtFechaFinal'] ?? now()->format('Y-m-d'));

            $asignaciones = Asignaciones::where('id_contenedor', $contenedor->id)->first() ?? new Asignaciones();
            $asignaciones->id_contenedor = $contenedor->id;
            $asignaciones->id_empresa = $cotizacion->id_empresa;
            $asignaciones->fecha_inicio = $fechaInicio;
            $asignaciones->fecha_fin = $fechaFinal . ' 23:00:00';
            $asignaciones->fehca_inicio_guard = $fechaInicio;
            $asignaciones->fehca_fin_guard = $fechaFinal . ' 23:00:00';

            $asignaciones->id_chasis = $data['cmbChasis'] ?? null;
            $asignaciones->id_chasis2 = $data['cmbChasis2'] ?? null;
            $asignaciones->id_dolys = $data['cmbDoly'] ?? null;
            $asignaciones->id_camion = $data['cmbCamion'] ?? null;
            $asignaciones->id_operador = $data['cmbOperador'] ?? null;

            $isElemental = !empty($data['elemental']);

            if (!$isElemental) {
                $sueldoOperador = !empty($data['txtSueldoOperador']) ? floatval(str_replace(',', '', $data['txtSueldoOperador'])) : 0.0;
                $dineroViaje = !empty($data['txtDineroViaje']) ? floatval(str_replace(',', '', $data['txtDineroViaje'])) : 0.0;
                $nuevoBancoDinero = !empty($data['cmbBanco']) ? intval($data['cmbBanco']) : null;

                $nuevaFechaAppDinero = null;
                if (!empty($data['FechaAplicacionDinero'])) {
                    try {
                        $nuevaFechaAppDinero = Carbon::createFromFormat('d/m/Y', trim($data['FechaAplicacionDinero']))->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $nuevaFechaAppDinero = Carbon::parse($data['FechaAplicacionDinero'])->format('Y-m-d');
                    }
                }

                // Gestionar DineroContenedor principal
                $dineroViajeModel = DineroContenedor::where('id_contenedor', $contenedor->id)
                    ->where(fn ($q) => $q->where('es_principal', 1)->orWhere('motivo', 'Dinero para viaje'))
                    ->orderByDesc('es_principal')
                    ->first();

                if (!$dineroViajeModel && $dineroViaje > 0) {
                    $dineroViajeModel = new DineroContenedor();
                    $dineroViajeModel->id_contenedor = $contenedor->id;
                    $dineroViajeModel->motivo = 'Dinero para viaje';
                    $dineroViajeModel->es_principal = 1;
                }

                // Movimiento bancario asociado
                $movimientoBancoExistente = null;
                if ($dineroViajeModel && $dineroViajeModel->id_movimiento_bancario) {
                    $movimientoBancoExistente = \App\Models\CatBancoCuentasMovimientos::where('id', $dineroViajeModel->id_movimiento_bancario)
                        ->where('cancelado', 0)
                        ->first();
                }

                if (!$movimientoBancoExistente && $asignaciones->id) {
                    $movimientoBancoExistente = \App\Models\CatBancoCuentasMovimientos::where('referenciaable_id', $asignaciones->id)
                        ->where(fn ($q) => $q->where('referenciaable_type', Asignaciones::class)->orWhere('referenciaable_type', 'like', '%Asignaciones%'))
                        ->where('cancelado', 0)
                        ->where('tipo', 'cargo')
                        ->where('concepto', 'not like', 'Devolución%')
                        ->where('concepto', 'not like', 'Dinero para viaje: %')
                        ->latest('id')
                        ->first();
                }

                if ($movimientoBancoExistente) {
                    $montoMismo = abs(floatval($movimientoBancoExistente->monto) - $dineroViaje) < 0.01;
                    $bancoMismo = ($movimientoBancoExistente->cuenta_bancaria_id == $nuevoBancoDinero);
                    $fechaMovStr = $movimientoBancoExistente->fecha_movimiento ? Carbon::parse($movimientoBancoExistente->fecha_movimiento)->format('Y-m-d') : null;
                    $fechaMisma = ($fechaMovStr === $nuevaFechaAppDinero);

                    if ($montoMismo && $bancoMismo && $fechaMisma && $dineroViaje > 0) {
                        if ($dineroViajeModel) {
                            $dineroViajeModel->es_principal = 1;
                            if (!$dineroViajeModel->id_movimiento_bancario) {
                                $dineroViajeModel->id_movimiento_bancario = $movimientoBancoExistente->id;
                                $dineroViajeModel->save();
                            }
                        }
                    } else {
                        $fechaCancelacionMov = $fechaMovStr ?: now()->format('Y-m-d');
                        $this->bancosService->cancelarMovimiento($movimientoBancoExistente->cuenta_bancaria_id, $movimientoBancoExistente->id, $fechaCancelacionMov);

                        $nuevoMovId = null;
                        if ($dineroViaje > 0 && $nuevoBancoDinero && $nuevaFechaAppDinero) {
                            $operadorN = Operador::find($data['cmbOperador'] ?? null);
                            $dataBanco = [
                                'cuenta_bancaria_id' => $nuevoBancoDinero,
                                'tipo' => 'cargo',
                                'monto' => $dineroViaje,
                                'concepto' => 'Dinero para viaje ' . $contenedor->num_contenedor . ' ' . ($operadorN?->nombre ?? ''),
                                'fecha_movimiento' => $nuevaFechaAppDinero,
                                'origen' => 'sistema',
                                'referencia' => 'del ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($fechaFinal)->format('d/m/Y'),
                                'detalles' => json_encode([['num_contenedor' => $contenedor->num_contenedor, 'abono' => $dineroViaje]]),
                                'referenciaable_id' => $asignaciones->id,
                                'referenciaable_type' => Asignaciones::class,
                            ];
                            $movCrear = $this->bancosService->registrarMovimiento($dataBanco);
                            $nuevoMovId = $movCrear?->id;
                        }

                        if ($dineroViajeModel) {
                            if ($dineroViaje > 0) {
                                $dineroViajeModel->id_banco = $nuevoBancoDinero;
                                $dineroViajeModel->monto = $dineroViaje;
                                $dineroViajeModel->fecha_entrega_monto = $nuevaFechaAppDinero;
                                $dineroViajeModel->id_movimiento_bancario = $nuevoMovId;
                                $dineroViajeModel->save();
                            } else {
                                $dineroViajeModel->delete();
                            }
                        }
                    }
                } elseif ($dineroViaje > 0 && $nuevoBancoDinero && $nuevaFechaAppDinero) {
                    $operadorN = Operador::find($data['cmbOperador'] ?? null);
                    $dataBanco = [
                        'cuenta_bancaria_id' => $nuevoBancoDinero,
                        'tipo' => 'cargo',
                        'monto' => $dineroViaje,
                        'concepto' => 'Dinero para viaje ' . $contenedor->num_contenedor . ' ' . ($operadorN?->nombre ?? ''),
                        'fecha_movimiento' => $nuevaFechaAppDinero,
                        'origen' => 'sistema',
                        'referencia' => 'del ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($fechaFinal)->format('d/m/Y'),
                        'detalles' => json_encode([['num_contenedor' => $contenedor->num_contenedor, 'abono' => $dineroViaje]]),
                        'referenciaable_id' => $asignaciones->id,
                        'referenciaable_type' => Asignaciones::class,
                    ];
                    $movCrear = $this->bancosService->registrarMovimiento($dataBanco);
                    if ($dineroViajeModel) {
                        $dineroViajeModel->id_banco = $nuevoBancoDinero;
                        $dineroViajeModel->monto = $dineroViaje;
                        $dineroViajeModel->fecha_entrega_monto = $nuevaFechaAppDinero;
                        $dineroViajeModel->id_movimiento_bancario = $movCrear?->id;
                        $dineroViajeModel->save();
                    }
                }

                $asignaciones->sueldo_viaje = $sueldoOperador;
                $asignaciones->dinero_viaje = $dineroViaje;
                $asignaciones->id_banco1_dinero_viaje = $nuevoBancoDinero;
                $asignaciones->cantidad_banco1_dinero_viaje = $dineroViaje;
                $resta = $sueldoOperador - $dineroViaje;
                $asignaciones->pago_operador = $resta;
                $asignaciones->restante_pago_operador = $resta;
                $asignaciones->tipo_contrato = 'Propio';
                $asignaciones->estatus_pagado = 'Pendiente Pago';
            }

            $asignaciones->save();

            if (!$isElemental) {
                $this->operadorGastosContabilidadService->sincronizarPlaneacion($asignaciones);
            }

            $cotizacion->estatus_planeacion = 1;
            if (!$isElemental) {
                $cotizacion->litros_diesel = $data['litros_diesel'] ?? $data['gasto_litros_diesel'] ?? 0;
                $cotizacion->litros_urea = $data['litros_urea'] ?? $data['gasto_litros_urea'] ?? 0;
            }
            $cotizacion->save();

            if (!$isElemental && !empty($data['filasOtrosGastos'])) {
                $filas = is_array($data['filasOtrosGastos']) ? $data['filasOtrosGastos'] : json_decode($data['filasOtrosGastos'], true);
                if (is_array($filas)) {
                    $this->sincronizarOtrosGastosPlaneacion($filas, $asignaciones, $contenedor, $data['cmbOperador'] ?? null, $idEmpresa, $user);
                }
            }

            return [
                "TMensaje" => "success",
                "Titulo" => "Actualizado correctamente",
                "Mensaje" => "Se ha actualizado correctamente la planeación del viaje",
                'success' => true
            ];
        });
    }

    /**
     * Sincroniza otros gastos en edición de planeación.
     */
    public function sincronizarOtrosGastosPlaneacion(array $otrosGastos, Asignaciones $asignacion, DocumCotizacion $contenedor, $idOperadorViaje, int $idEmpresa, $user): array
    {
        $descripcionGastosPermitidos = [
            'GCM01' => 'GCM01 - Comisión',
            'GDI02' => 'GDI02 - Diésel',
            'GBV01' => 'GBV01 - Burrero Vacío',
            'GU001' => 'GU001 - Urea',
            'OTR01' => 'Otros'
        ];

        $gastosExistentes = Gasto::with(['pagos.movimientoBancario', 'imputaciones'])
            ->where(function ($query) use ($asignacion) {
                $query->where(function ($q) use ($asignacion) {
                    $q->where('origen_legacy_id', $asignacion->id)
                      ->where('origen_legacy', 'like', 'asignacion_planeacion%');
                })->orWhereHas('vinculos', function ($q) use ($asignacion) {
                    $q->where('vinculable_type', Asignaciones::class)
                      ->where('vinculable_id', $asignacion->id);
                });
            })
            ->where('tipo_gasto', '!=', 'sueldo')
            ->where(function ($q) {
                $q->whereNull('origen_legacy')->orWhereNotIn('origen_legacy', [
                    'asignacion_sueldo_operador',
                    'asignacion_anticipo_operador',
                    'dinero_contenedor_anticipo',
                ]);
            })
            ->get();

        $gastosProcesadosIds = [];

        foreach ($otrosGastos as $gasto) {
            $gastoId = !empty($gasto['id']) ? intval($gasto['id']) : null;
            $motivo = $gasto['motivo'] ?? null;
            $monto = floatval($gasto['monto'] ?? 0);
            $esPagoInmediato = !empty($gasto['pagoInmediato']);
            $idBanco = !empty($gasto['banco']) ? intval($gasto['banco']) : null;
            $fechaAplicacionInput = $gasto['fechaAplicacion'] ?? null;
            $conceptoOtro = $gasto['conceptoOtro'] ?? null;

            if (!$motivo || !isset($descripcionGastosPermitidos[$motivo])) {
                continue;
            }
            if ($monto <= 0) {
                continue;
            }

            $fechaAplicacion = null;
            if (!empty($fechaAplicacionInput)) {
                try {
                    $fechaAplicacion = Carbon::createFromFormat('d/m/Y', trim($fechaAplicacionInput))->format('Y-m-d');
                } catch (\Throwable $e) {
                    $fechaAplicacion = Carbon::parse($fechaAplicacionInput)->format('Y-m-d');
                }
            }

            $tipoGasto = $motivo === 'OTR01' ? ($conceptoOtro ?: 'Otros') : $descripcionGastosPermitidos[$motivo];

            $gastoExistente = null;
            if ($gastoId) {
                $gastoExistente = $gastosExistentes->firstWhere('id', $gastoId);
            }
            if (!$gastoExistente && $motivo) {
                $gastoExistente = $gastosExistentes->first(function ($g) use ($motivo, $gastosProcesadosIds) {
                    if (in_array($g->id, $gastosProcesadosIds)) {
                        return false;
                    }
                    return str_starts_with($g->concepto, $motivo);
                });
            }

            $categoriaId = null;
            $conceptoId = null;

            if ($motivo === 'GDI02') {
                $cat = DB::table('categorias_gastos')->where('categoria', 'like', '%Combustible%')->first();
                $categoriaId = $cat?->id ?? 1;
                $con = DB::table('gasto_conceptos')->where('clave', 'DSL')->first();
                $conceptoId = $con?->id;
            } elseif ($motivo === 'GCM01') {
                $cat = DB::table('categorias_gastos')->where('categoria', 'like', '%Comisiones de venta%')->first();
                $categoriaId = $cat?->id ?? 5;
                $con = DB::table('gasto_conceptos')->where('clave', 'COM_VTA')->first();
                $conceptoId = $con?->id;
            } else {
                $cat = DB::table('categorias_gastos')->where('categoria', 'like', '%Otros%')->first();
                $categoriaId = $cat?->id ?? 12;

                $claveConcepto = $motivo === 'GBV01' ? 'OTR_BV' : ($motivo === 'GU001' ? 'OTR_UR' : 'OTR_GEN');
                $nombreConcepto = $motivo === 'GBV01' ? 'GBV01 - Burrero Vacío' : ($motivo === 'GU001' ? 'GU001 - Urea' : 'Otros');

                $con = \App\Models\GastoConcepto::where('clave', $claveConcepto)->first();
                if (!$con) {
                    $con = new \App\Models\GastoConcepto();
                    $con->categoria_gasto_id = $categoriaId;
                    $con->clave = $claveConcepto;
                    $con->nombre = $nombreConcepto;
                    $con->tipo_default = 'viaje';
                    $con->afecta_utilidad = true;
                    $con->is_active = true;
                    $con->save();
                }
                $conceptoId = $con->id;
            }

            if ($gastoExistente) {
                $gastosProcesadosIds[] = $gastoExistente->id;

                $gastoExistente->concepto = $tipoGasto;
                $gastoExistente->categoria_gasto_id = $categoriaId;
                $gastoExistente->gasto_concepto_id = $conceptoId;
                $gastoExistente->monto_total = $monto;
                if ($asignacion->id_camion) {
                    $gastoExistente->id_equipo = $asignacion->id_camion;
                    if (!$gastoExistente->vinculos()->where('tipo_vinculo', 'unidad')->exists()) {
                        $gastoExistente->vinculos()->create([
                            'tipo_vinculo' => 'unidad',
                            'vinculable_type' => Equipo::class,
                            'vinculable_id' => $asignacion->id_camion,
                        ]);
                    }
                }
                $gastoExistente->save();

                foreach ($gastoExistente->imputaciones as $imp) {
                    $imp->monto_imputado = $monto;
                    $imp->save();
                }

                $pagosActivos = $gastoExistente->pagos()->where('estatus', '!=', 'cancelado')->get();
                $estabaPagado = $pagosActivos->isNotEmpty();

                if ($estabaPagado) {
                    $pagoPrincipal = $pagosActivos->first();
                    $montoPagoIgual = abs(floatval($pagoPrincipal->monto) - $monto) < 0.01 && $pagosActivos->count() === 1;
                    $bancoPagoIgual = ($pagoPrincipal->cuenta_bancaria_id == $idBanco);
                    $fechaPagoActual = $pagoPrincipal->movimientoBancario?->fecha_movimiento
                        ? Carbon::parse($pagoPrincipal->movimientoBancario->fecha_movimiento)->format('Y-m-d')
                        : ($pagoPrincipal->fecha_pago ? Carbon::parse($pagoPrincipal->fecha_pago)->format('Y-m-d') : null);
                    $fechaPagoEsperada = $fechaAplicacion ?? now()->format('Y-m-d');
                    $fechaPagoIgual = ($fechaPagoActual === $fechaPagoEsperada);

                    if (!$esPagoInmediato || empty($idBanco)) {
                        foreach ($pagosActivos as $p) {
                            $fechaCancelacion = $p->movimientoBancario?->fecha_movimiento
                                ? Carbon::parse($p->movimientoBancario->fecha_movimiento)->format('Y-m-d')
                                : ($p->fecha_pago ? Carbon::parse($p->fecha_pago)->format('Y-m-d') : now()->format('Y-m-d'));
                            $this->gastosService->cancelarPago($p, $fechaCancelacion);
                        }
                        $gastoExistente->estatus = 'pendiente_pago';
                        $gastoExistente->save();
                    } elseif (!$montoPagoIgual || !$bancoPagoIgual || !$fechaPagoIgual) {
                        foreach ($pagosActivos as $p) {
                            $fechaCancelacion = $p->movimientoBancario?->fecha_movimiento
                                ? Carbon::parse($p->movimientoBancario->fecha_movimiento)->format('Y-m-d')
                                : ($p->fecha_pago ? Carbon::parse($p->fecha_pago)->format('Y-m-d') : now()->format('Y-m-d'));
                            $this->gastosService->cancelarPago($p, $fechaCancelacion);
                        }

                        $this->gastosService->pagar($gastoExistente, [
                            'cuenta_bancaria_id' => $idBanco,
                            'monto' => $monto,
                            'fecha_pago' => $fechaAplicacion ?? now()->format('Y-m-d'),
                            'concepto_banco' => BancosService::generarConcepto('gop', $tipoGasto, $contenedor->num_contenedor, $asignacion->Operador?->nombre ?? ($asignacion->id_operador ? Operador::find($asignacion->id_operador)?->nombre : null)),
                            'referencia_banco' => 'GASTO_PLANEACION_VIAJE',
                        ]);
                    }
                } else {
                    if ($esPagoInmediato && $idBanco) {
                        $this->gastosService->pagar($gastoExistente, [
                            'cuenta_bancaria_id' => $idBanco,
                            'monto' => $monto,
                            'fecha_pago' => $fechaAplicacion ?? now()->format('Y-m-d'),
                            'concepto_banco' => BancosService::generarConcepto('gop', $tipoGasto, $contenedor->num_contenedor, $asignacion->Operador?->nombre ?? ($asignacion->id_operador ? Operador::find($asignacion->id_operador)?->nombre : null)),
                            'referencia_banco' => 'GASTO_PLANEACION_VIAJE',
                        ]);
                    }
                }

                $goTipo = match ($motivo) {
                    'GDI02' => 'Diesel',
                    'GU001' => 'Urea',
                    default => $tipoGasto,
                };
                $goExistente = GastosOperadores::withoutGlobalScope('no_eliminados')
                    ->where('id_asignacion', $asignacion->id)
                    ->where(function ($q) use ($goTipo, $tipoGasto) {
                        $q->where('tipo', $goTipo)->orWhere('tipo', $tipoGasto);
                    })->first();

                if ($goExistente) {
                    $goExistente->update([
                        'cantidad' => $monto,
                        'id_banco' => ($esPagoInmediato && $idBanco) ? $idBanco : null,
                        'fecha_pago' => ($esPagoInmediato && $idBanco) ? ($fechaAplicacion ?? now()->format('Y-m-d')) : null,
                        'pago_inmediato' => ($esPagoInmediato && $idBanco) ? 1 : 0,
                        'estatus' => ($esPagoInmediato && $idBanco) ? 'Pagado' : 'Pago Pendiente',
                    ]);
                }
            } else {
                $vinculosData = [
                    [
                        'tipo_vinculo' => 'cotizacion',
                        'vinculable_type' => Cotizaciones::class,
                        'vinculable_id' => $contenedor->id_cotizacion,
                    ],
                    [
                        'tipo_vinculo' => 'contenedor',
                        'vinculable_type' => DocumCotizacion::class,
                        'vinculable_id' => $contenedor->id,
                    ],
                    [
                        'tipo_vinculo' => 'asignacion',
                        'vinculable_type' => Asignaciones::class,
                        'vinculable_id' => $asignacion->id,
                    ],
                ];
                if ($asignacion->id_operador) {
                    $vinculosData[] = [
                        'tipo_vinculo' => 'operador',
                        'vinculable_type' => Operador::class,
                        'vinculable_id' => $asignacion->id_operador,
                    ];
                }
                if ($asignacion->id_camion) {
                    $vinculosData[] = [
                        'tipo_vinculo' => 'unidad',
                        'vinculable_type' => Equipo::class,
                        'vinculable_id' => $asignacion->id_camion,
                    ];
                }

                $nuevoGasto = $this->gastosService->registrar([
                    'id_empresa' => $idEmpresa,
                    'id_equipo' => $asignacion->id_camion,
                    'categoria_gasto_id' => $categoriaId,
                    'gasto_concepto_id' => $conceptoId,
                    'concepto' => $tipoGasto,
                    'monto_total' => $monto,
                    'tipo_gasto' => 'viaje',
                    'estatus' => $esPagoInmediato ? 'pagado' : 'pendiente_pago',
                    'fecha_gasto' => Carbon::now(),
                    'origen_legacy' => 'asignacion_planeacion' . $tipoGasto,
                    'origen_legacy_id' => $asignacion->id,
                    'user_id' => $user->id,
                    'vinculos' => $vinculosData,
                    'imputaciones' => [
                        [
                            'fecha_imputacion' => Carbon::now(),
                            'tipo_imputacion' => 'viaje',
                            'imputable_type' => Asignaciones::class,
                            'imputable_id' => $asignacion->id,
                            'monto_imputado' => $monto,
                            'origen' => 'directo',
                        ]
                    ]
                ]);

                $gastosProcesadosIds[] = $nuevoGasto->id;

                if ($esPagoInmediato && $idBanco) {
                    $this->gastosService->pagar($nuevoGasto, [
                        'cuenta_bancaria_id' => $idBanco,
                        'monto' => $monto,
                        'fecha_pago' => $fechaAplicacion ?? now()->format('Y-m-d'),
                        'concepto_banco' => BancosService::generarConcepto('gop', $tipoGasto, $contenedor->num_contenedor, $asignacion->Operador?->nombre ?? ($asignacion->id_operador ? Operador::find($asignacion->id_operador)?->nombre : null)),
                        'referencia_banco' => 'GASTO_PLANEACION_VIAJE',
                    ]);
                }

                $goTipo = match ($motivo) {
                    'GDI02' => 'Diesel',
                    'GU001' => 'Urea',
                    default => $tipoGasto,
                };
                GastosOperadores::updateOrCreate(
                    [
                        'id_asignacion' => $asignacion->id,
                        'tipo' => $goTipo,
                    ],
                    [
                        'id_operador' => $asignacion->id_operador,
                        'id_cotizacion' => $contenedor->id_cotizacion,
                        'cantidad' => $monto,
                        'id_banco' => ($esPagoInmediato && $idBanco) ? $idBanco : null,
                        'fecha_pago' => ($esPagoInmediato && $idBanco) ? ($fechaAplicacion ?? now()->format('Y-m-d')) : null,
                        'pago_inmediato' => ($esPagoInmediato && $idBanco) ? 1 : 0,
                        'estatus' => ($esPagoInmediato && $idBanco) ? 'Pagado' : 'Pago Pendiente',
                    ]
                );
            }
        }

        // Eliminar gastos que ya no vienen en la solicitud
        foreach ($gastosExistentes as $gastoBorrado) {
            if (!in_array($gastoBorrado->id, $gastosProcesadosIds)) {
                foreach ($gastoBorrado->pagos()->where('estatus', '!=', 'cancelado')->get() as $pago) {
                    $fechaCancelacion = $pago->movimientoBancario?->fecha_movimiento
                        ? Carbon::parse($pago->movimientoBancario->fecha_movimiento)->format('Y-m-d')
                        : ($pago->fecha_pago ? Carbon::parse($pago->fecha_pago)->format('Y-m-d') : now()->format('Y-m-d'));
                    $this->gastosService->cancelarPago($pago, $fechaCancelacion);
                }

                $goId = ($gastoBorrado->origen_legacy === 'gastos_operadores') ? $gastoBorrado->origen_legacy_id : null;
                if ($goId) {
                    $goLegacy = GastosOperadores::withoutGlobalScope('no_eliminados')->find($goId);
                    if ($goLegacy) {
                        $goLegacy->update(['estatus' => 'eliminado']);
                    }
                }

                $tipoGastoBorrado = $gastoBorrado->concepto;
                $goAsignados = GastosOperadores::withoutGlobalScope('no_eliminados')
                    ->where('id_asignacion', $asignacion->id)
                    ->where(function ($q) use ($tipoGastoBorrado) {
                        $conceptoB = strtolower($tipoGastoBorrado);
                        if (str_contains($conceptoB, 'diesel') || str_contains($conceptoB, 'diésel') || str_contains($tipoGastoBorrado, 'GDI02')) {
                            $q->where('tipo', 'like', '%Diesel%')->orWhere('tipo', 'like', '%diésel%')->orWhere('tipo', 'like', '%GDI02%');
                        } elseif (str_contains($conceptoB, 'urea') || str_contains($tipoGastoBorrado, 'GU001')) {
                            $q->where('tipo', 'like', '%Urea%')->orWhere('tipo', 'like', '%GU001%');
                        } elseif (str_contains($conceptoB, 'burrero') || str_contains($tipoGastoBorrado, 'GBV01')) {
                            $q->where('tipo', 'like', '%Burrero%')->orWhere('tipo', 'like', '%GBV01%');
                        } elseif (str_contains($conceptoB, 'comisi') || str_contains($tipoGastoBorrado, 'GCM01')) {
                            $q->where('tipo', 'like', '%Comisi%')->orWhere('tipo', 'like', '%GCM01%');
                        } else {
                            $q->where('tipo', $tipoGastoBorrado);
                        }
                    })
                    ->get();

                foreach ($goAsignados as $itemGo) {
                    $itemGo->update(['estatus' => 'eliminado']);
                }

                $cotizacionModel = $contenedor->id_cotizacion ? Cotizaciones::find($contenedor->id_cotizacion) : null;
                $conceptoB = strtolower($tipoGastoBorrado);
                if (str_contains($conceptoB, 'diesel') || str_contains($conceptoB, 'diésel') || str_contains($tipoGastoBorrado, 'GDI02')) {
                    \App\Models\BitacoraViajeOperador::where('id_asignacion', $asignacion->id)
                        ->update(['costo' => 0, 'litros' => 0]);
                    if ($cotizacionModel) {
                        $cotizacionModel->litros_diesel = 0;
                        $cotizacionModel->save();
                    }
                } elseif (str_contains($conceptoB, 'urea') || str_contains($tipoGastoBorrado, 'GU001')) {
                    \App\Models\BitacoraViajeOperador::where('id_asignacion', $asignacion->id)
                        ->update(['costo_urea' => 0, 'litros_urea' => 0]);
                    if ($cotizacionModel) {
                        $cotizacionModel->litros_urea = 0;
                        $cotizacionModel->save();
                    }
                }

                $gastoBorrado->vinculos()->delete();
                $gastoBorrado->imputaciones()->delete();
                $gastoBorrado->update(['estatus' => 'cancelado']);
                $gastoBorrado->delete();
            }
        }

        return [
            "Titulo" => "Gastos actualizados",
            "Mensaje" => "Se sincronizaron los gastos correctamente.",
            "TMensaje" => "success"
        ];
    }

    /**
     * Registra una planeación elemental (simplificada, sin impacto bancario directo).
     */
    public function asignacionElemental(array $data, int $idEmpresa, $user): array
    {
        return DB::transaction(function () use ($data, $idEmpresa) {
            $numContenedoresRaw = $data['num_contenedor'] ?? null;
            $numContenedores = is_string($numContenedoresRaw) ? json_decode($numContenedoresRaw, true) : $numContenedoresRaw;
            if (!is_array($numContenedores)) {
                $numContenedores = !empty($numContenedoresRaw) ? [$numContenedoresRaw] : [];
            }
            $numContenedor = !empty($numContenedores) ? $numContenedores[0] : '';

            $fechaInicio = Common::TransformaFecha($data['txtFechaInicio'] ?? now()->format('Y-m-d'));
            $fechaFinal = Common::TransformaFecha($data['txtFechaFinal'] ?? now()->format('Y-m-d'));

            $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)->firstOrFail();
            $cotizacion = Cotizaciones::where('id', '=', $contenedor->id_cotizacion)->firstOrFail();

            $asignaciones = Asignaciones::where('id_contenedor', $contenedor->id)->first() ?? new Asignaciones();
            $asignaciones->id_contenedor = $contenedor->id;
            $asignaciones->id_empresa = $cotizacion->id_empresa;
            $asignaciones->fecha_inicio = $fechaInicio;
            $asignaciones->fecha_fin = $fechaFinal . ' 23:00:00';
            $asignaciones->fehca_inicio_guard = $fechaInicio;
            $asignaciones->fehca_fin_guard = $fechaFinal . ' 23:00:00';
            $asignaciones->id_chasis = $data['cmbChasis'] ?? null;
            $asignaciones->id_chasis2 = null;
            $asignaciones->id_dolys = null;
            $asignaciones->id_camion = $data['cmbCamion'] ?? null;
            $asignaciones->id_operador = $data['cmbOperador'] ?? null;
            $asignaciones->sueldo_viaje = 0;
            $asignaciones->estatus_pagado = 'Pendiente Pago';
            $asignaciones->dinero_viaje = 0;
            $asignaciones->pago_operador = 0;
            $asignaciones->restante_pago_operador = 0;
            $asignaciones->tipo_contrato = 'Propio';

            $passwordTemporal = (string) mt_rand(100000, 999999);
            $asignaciones->password_temporal = $passwordTemporal;
            $asignaciones->save();

            $litrosDiesel = $data['litros_diesel'] ?? $data['gasto_litros_diesel'] ?? 0;
            $litrosUrea = $data['litros_urea'] ?? $data['gasto_litros_urea'] ?? 0;
            $cotizacion->litros_diesel = $litrosDiesel;
            $cotizacion->litros_urea = $litrosUrea;
            $cotizacion->estatus_planeacion = 1;
            $cotizacion->tipo_viaje = 'Sencillo';
            $cotizacion->save();

            if (count($numContenedores) == 2) {
                $fullUUID = Common::generarUuidV4();
                foreach ($numContenedores as $i => $cont) {
                    $cDoc = DocumCotizacion::where('num_contenedor', $cont)->first();
                    if ($cDoc) {
                        $cot = Cotizaciones::find($cDoc->id_cotizacion);
                        if ($cot) {
                            $cot->referencia_full = $fullUUID;
                            $cot->jerarquia = ($i == 0) ? 'Principal' : 'Secundario';
                            $cot->estatus_planeacion = 1;
                            $cot->tipo_viaje = 'Full';
                            $cot->save();
                        }
                    }
                }
            }

            return [
                "TMensaje" => "success",
                "Titulo" => "Planeado correctamente",
                "Mensaje" => "Se ha programado correctamente el viaje del contenedor (Elemental)",
                'success' => true,
                'cotizacion_data' => ["tipo_viaje" => $cotizacion->tipo_viaje],
                'password_temporal' => null,
                'id_asignacion' => $asignaciones->id,
            ];
        });
    }
}