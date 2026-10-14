@extends('layouts.app')

@section('template_title')
    Balance General
@endsection

@section('content')
    <div class="container-fluid py-4">
        <!-- Tab Navigation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="nav-wrapper position-relative end-0">
                    <ul class="nav nav-pills nav-fill p-1" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link mb-0 px-0 py-1 active" id="balance-tab" data-bs-toggle="tab"
                                href="#balance-content" role="tab" aria-controls="balance" aria-selected="true">
                                <i class="fa fa-file-invoice-dollar me-2"></i> Reporte Balance General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mb-0 px-0 py-1" id="saldos-tab" data-bs-toggle="tab" href="#saldos-content"
                                role="tab" aria-controls="saldos" aria-selected="false">
                                <i class="fa fa-cog me-2"></i> Control de Saldos Iniciales
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="tab-content">
            <!-- TAB 1: BALANCE GENERAL -->
            <div class="tab-pane fade show active" id="balance-content" role="tabpanel" aria-labelledby="balance-tab">
                <!-- Summary Cards / Metrics -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="numbers">
                                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Activos</p>
                                            <h5 class="font-weight-bolder mb-0">
                                                ${{ number_format($balance['totales']['activo'], 2) }}
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <div
                                            class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                            <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="numbers">
                                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Pasivos</p>
                                            <h5 class="font-weight-bolder mb-0">
                                                ${{ number_format($balance['totales']['pasivo'], 2) }}
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <div
                                            class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                            <i class="ni ni-credit-card text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="numbers">
                                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Capital</p>
                                            <h5 class="font-weight-bolder mb-0">
                                                ${{ number_format($balance['totales']['capital'], 2) }}
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <div
                                            class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                            <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="numbers">
                                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Estado Cuadrado</p>
                                            <h5
                                                class="font-weight-bolder mb-0 {{ $balance['cuadrado'] ? 'text-success' : 'text-danger' }}">
                                                {{ $balance['cuadrado'] ? 'Cuadrado' : 'Descuadrado' }}
                                                <span class="text-xs font-weight-normal text-muted d-block">
                                                    Dif: ${{ number_format($balance['diferencia'], 2) }}
                                                </span>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <div
                                            class="icon icon-shape {{ $balance['cuadrado'] ? 'bg-gradient-success' : 'bg-gradient-warning' }} text-center rounded-circle">
                                            <i class="ni ni-check-bold text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Balance Card -->
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="row">
                            <div class="col-lg-5 col-12">
                                <h6>Balance General</h6>
                                <p class="text-sm mb-0">
                                    <i class="fa fa-calendar text-info" aria-hidden="true"></i>
                                    Generado al corte especificado
                                </p>
                            </div>
                            <div class="col-lg-7 col-12 my-auto text-end">
                                <form method="GET" action="{{ route('reporteria.balance-general.index') }}"
                                    class="d-inline-flex align-items-center gap-2 flex-wrap">
                                    <label for="fecha_corte" class="text-xs font-weight-bold text-uppercase mb-0 me-2">Fecha
                                        Corte:</label>
                                    <input type="date" id="fecha_corte" name="fecha_corte"
                                        class="form-control form-control-sm d-inline-block w-auto"
                                        value="{{ $fechaCorte }}" onchange="this.form.submit()">
                                </form>
                                <a href="{{ route('reporteria.balance-general.export.excel', ['fecha_corte' => $fechaCorte]) }}"
                                    class="btn btn-sm bg-gradient-success text-white ms-2 mb-0">
                                    <i class="fa fa-file-excel me-1"></i> Excel
                                </a>
                                <a href="{{ route('reporteria.balance-general.export.pdf', ['fecha_corte' => $fechaCorte]) }}"
                                    class="btn btn-sm bg-gradient-danger text-white ms-2 mb-0">
                                    <i class="fa fa-file-pdf me-1"></i> PDF
                                </a>

                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pb-2">
                        <div class="row p-4 print-layout">
                            <!-- Column Left: ACTIVOS -->
                            <div class="col-md-6 border-end">
                                <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-2 rounded">
                                    <h6 class="mb-0 text-primary text-uppercase font-weight-bold text-sm">Activos</h6>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Concepto</th>
                                                <th
                                                    class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">
                                                    Saldo</th>
                                                <th class="text-secondary opacity-7"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $activos = collect($balance['rows'])->where('grupo', 'activo');
                                            @endphp
                                            @foreach ($activos as $row)
                                                <tr class="align-middle">
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm font-weight-bold">
                                                                    {{ $row['concepto'] }}</h6>
                                                                <span
                                                                    class="text-xxs text-secondary text-uppercase">{{ str_replace('_', ' ', $row['tipo_calculo']) }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end pe-3 font-weight-bold text-sm">
                                                        ${{ number_format($row['valor'], 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-link text-secondary mb-0"
                                                            onclick="openConfigModal({{ json_encode($row) }})">
                                                            <i class="fa fa-ellipsis-v text-xs"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-light align-middle font-weight-bold">
                                                <td>
                                                    <div class="px-2 py-2">
                                                        <span class="text-sm font-weight-bold text-uppercase">Suma
                                                            Activos</span>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-3 font-weight-bold text-primary">
                                                    ${{ number_format($balance['totales']['activo'], 2) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Column Right: PASIVOS & CAPITAL -->
                            <div class="col-md-6">
                                <!-- PASIVOS -->
                                <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-2 rounded">
                                    <h6 class="mb-0 text-danger text-uppercase font-weight-bold text-sm">Pasivos</h6>
                                </div>
                                <div class="table-responsive mb-4">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Concepto</th>
                                                <th
                                                    class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">
                                                    Saldo</th>
                                                <th class="text-secondary opacity-7"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $pasivos = collect($balance['rows'])->where('grupo', 'pasivo');
                                            @endphp
                                            @foreach ($pasivos as $row)
                                                <tr class="align-middle">
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm font-weight-bold">
                                                                    {{ $row['concepto'] }}</h6>
                                                                <span
                                                                    class="text-xxs text-secondary text-uppercase">{{ str_replace('_', ' ', $row['tipo_calculo']) }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end pe-3 font-weight-bold text-sm">
                                                        ${{ number_format($row['valor'], 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-link text-secondary mb-0"
                                                            onclick="openConfigModal({{ json_encode($row) }})">
                                                            <i class="fa fa-ellipsis-v text-xs"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-light align-middle font-weight-bold">
                                                <td>
                                                    <div class="px-2 py-2">
                                                        <span class="text-sm font-weight-bold text-uppercase">Suma
                                                            Pasivos</span>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-3 font-weight-bold text-danger">
                                                    ${{ number_format($balance['totales']['pasivo'], 2) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- CAPITAL -->
                                <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-2 rounded">
                                    <h6 class="mb-0 text-success text-uppercase font-weight-bold text-sm">Capital</h6>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Concepto</th>
                                                <th
                                                    class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-3">
                                                    Saldo</th>
                                                <th class="text-secondary opacity-7"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $capital = collect($balance['rows'])->where('grupo', 'capital');
                                            @endphp
                                            @foreach ($capital as $row)
                                                <tr class="align-middle">
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm font-weight-bold">
                                                                    {{ $row['concepto'] }}</h6>
                                                                <span
                                                                    class="text-xxs text-secondary text-uppercase">{{ str_replace('_', ' ', $row['tipo_calculo']) }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end pe-3 font-weight-bold text-sm">
                                                        ${{ number_format($row['valor'], 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-link text-secondary mb-0"
                                                            onclick="openConfigModal({{ json_encode($row) }})">
                                                            <i class="fa fa-ellipsis-v text-xs"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-light align-middle font-weight-bold">
                                                <td>
                                                    <div class="px-2 py-2">
                                                        <span class="text-sm font-weight-bold text-uppercase">Suma
                                                            Capital</span>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-3 font-weight-bold text-success">
                                                    ${{ number_format($balance['totales']['capital'], 2) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4 p-4 border-top">
                            <div class="col-md-6 border-end">
                                <div class="d-flex justify-content-between font-weight-bolder p-2 align-items-center">
                                    <span class="text-uppercase text-primary fs-15">Total Activos</span>
                                    <span
                                        class="fs-16 text-primary">${{ number_format($balance['totales']['activo'], 2) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between font-weight-bolder p-2 align-items-center">
                                    <span class="text-uppercase text-dark fs-15">Total Pasivo + Capital</span>
                                    <span
                                        class="fs-16 text-dark">${{ number_format($balance['totales']['pasivo'] + $balance['totales']['capital'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: CONTROL DE SALDOS INICIALES -->
            <div class="tab-pane fade" id="saldos-content" role="tabpanel" aria-labelledby="saldos-tab">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Ejercicios con Saldos Iniciales Registrados</h6>
                            <p class="text-sm mb-0">Lista de ejercicios contables parametrizados para la empresa.</p>
                        </div>
                        <button class="btn bg-gradient-primary text-white" onclick="openSaldosModal(null)">
                            <i class="fa fa-plus me-1"></i> Registrar Saldo Inicial
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Ejercicio / Año</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Fecha de Inicio de Aplicación</th>
                                        <th class="text-secondary opacity-7">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ejerciciosRegistrados as $e)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold text-sm">{{ $e->ejercicio }}</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="text-sm text-secondary">{{ $e->fecha_inicio->format('d/m/Y') }}</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info"
                                                    onclick="openSaldosModal({{ $e->ejercicio }})">
                                                    <i class="fa fa-edit me-1"></i> Editar Saldos
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4">
                                                <span class="text-secondary text-xs">No se han registrado saldos
                                                    iniciales.</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Configuration Modal -->
    <div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="configModalLabel">Configurar Línea del Balance</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"
                        style="border:none; background:none; font-size:1.5rem;">&times;</button>
                </div>
                <form id="configForm">
                    @csrf
                    <input type="hidden" id="config_id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="config_concepto"
                                class="form-label text-xs font-weight-bold text-uppercase">Concepto</label>
                            <input type="text" class="form-control" id="config_concepto" name="concepto" required>
                        </div>
                        <div class="mb-3">
                            <label for="config_tipo_calculo"
                                class="form-label text-xs font-weight-bold text-uppercase">Origen / Tipo de Cálculo</label>
                            <select class="form-select" id="config_tipo_calculo" name="tipo_calculo" required
                                onchange="toggleManualInput(this.value)">
                                <option value="bancos">Bancos (Saldo dinámico de Cuentas)</option>
                                <option value="cxc">Cuentas por Cobrar (Clientes)</option>
                                <option value="cxp">Cuentas por Pagar (Proveedores)</option>
                                <option value="gxp">Gastos por Pagar (Viáticos y Generales)</option>
                                <option value="utilidad_ejercicio">Utilidad del Ejercicio</option>
                                <option value="utilidades_acumuladas">Utilidades Acumuladas</option>
                                <option value="manual">Manual (Especificar Valor)</option>
                            </select>
                        </div>
                        <div class="mb-3" id="manual_value_container" style="display:none;">
                            <label for="config_valor_manual"
                                class="form-label text-xs font-weight-bold text-uppercase">Valor Manual</label>
                            <input type="number" step="0.01" class="form-control" id="config_valor_manual"
                                name="valor_manual" value="0.00">
                        </div>
                        <div class="mb-3">
                            <label for="config_orden" class="form-label text-xs font-weight-bold text-uppercase">Orden
                                Visual</label>
                            <input type="number" class="form-control" id="config_orden" name="orden" required
                                value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn bg-gradient-primary text-white">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Captura / Edicion de Saldos Iniciales Modal -->
    <div class="modal fade" id="saldosModal" tabindex="-1" aria-labelledby="saldosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="saldosModalLabel">Registrar Saldos Iniciales</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"
                        style="border:none; background:none; font-size:1.5rem;">&times;</button>
                </div>
                <form id="saldosForm">
                    @csrf
                    <input type="hidden" id="saldo_ejercicio" name="ejercicio">
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="saldo_fecha_inicio"
                                    class="form-label text-xs font-weight-bold text-uppercase">Fecha de Inicio de
                                    Aplicación</label>
                                <input type="date" class="form-control" id="saldo_fecha_inicio" name="fecha_inicio"
                                    required onchange="extractYearFromDate(this.value)">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <h6 class="mb-2 font-weight-bold text-sm text-primary">
                                    Ejercicio Fiscal: <span id="label_ejercicio">-</span>
                                </h6>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Concepto</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Grupo</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Saldo Inicial</th>
                                    </tr>
                                </thead>
                                <tbody id="saldos_modal_rows">
                                    @foreach ($configs as $index => $config)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold text-sm">{{ $config->concepto }}</span>
                                                <input type="hidden" name="saldos[{{ $index }}][config_id]"
                                                    value="{{ $config->id }}">
                                            </td>
                                            <td>
                                                <span
                                                    class="text-xs text-uppercase text-secondary">{{ $config->grupo }}</span>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    class="form-control form-control-sm config-monto-input"
                                                    data-config-id="{{ $config->id }}"
                                                    name="saldos[{{ $index }}][monto]" value="0.00" required
                                                    style="max-width:200px;">
                                                <input type="hidden" class="config-fecha-hidden"
                                                    name="saldos[{{ $index }}][fecha_inicio]" value="">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn bg-gradient-primary text-white">Guardar Saldos</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script>
        function openConfigModal(data) {
            document.getElementById('config_id').value = data.id;
            document.getElementById('config_concepto').value = data.concepto;
            document.getElementById('config_tipo_calculo').value = data.tipo_calculo;
            document.getElementById('config_valor_manual').value = data.valor_manual || 0;
            document.getElementById('config_orden').value = data.orden || 0;

            toggleManualInput(data.tipo_calculo);

            var myModal = new bootstrap.Modal(document.getElementById('configModal'));
            myModal.show();
        }

        function toggleManualInput(value) {
            var container = document.getElementById('manual_value_container');
            if (value === 'manual') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }

        function extractYearFromDate(dateString) {
            if (dateString) {
                var parts = dateString.split('-');
                var year = parts[0];
                document.getElementById('saldo_ejercicio').value = year;
                document.getElementById('label_ejercicio').innerText = year;

                // Copy selected start date into all config hidden input fields
                document.querySelectorAll('.config-fecha-hidden').forEach(function(el) {
                    el.value = dateString;
                });
            }
        }

        function openSaldosModal(ejercicio = null) {
            var modalTitle = document.getElementById('saldosModalLabel');
            var inputFecha = document.getElementById('saldo_fecha_inicio');

            // Clear montos inputs
            document.querySelectorAll('.config-monto-input').forEach(function(el) {
                el.value = '0.00';
            });

            if (ejercicio) {
                modalTitle.innerText = "Editar Saldos Iniciales - Ejercicio " + ejercicio;

                // Fetch values from backend
                fetch("{{ route('reporteria.balance-general.saldos.get') }}?ejercicio=" + ejercicio)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            inputFecha.value = data.fecha_inicio;
                            extractYearFromDate(data.fecha_inicio);

                            Object.keys(data.saldos).forEach(configId => {
                                var input = document.querySelector('.config-monto-input[data-config-id="' +
                                    configId + '"]');
                                if (input) {
                                    input.value = data.saldos[configId].monto;
                                }
                            });

                            var myModal = new bootstrap.Modal(document.getElementById('saldosModal'));
                            myModal.show();
                        } else {
                            alert('Error al recuperar saldos.');
                        }
                    });
            } else {
                modalTitle.innerText = "Registrar Saldos Iniciales";
                var defaultDate = new Date().getFullYear() + "-01-01";
                inputFecha.value = defaultDate;
                extractYearFromDate(defaultDate);

                var myModal = new bootstrap.Modal(document.getElementById('saldosModal'));
                myModal.show();
            }
        }

        document.getElementById('configForm').addEventListener('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);

            fetch("{{ route('reporteria.balance-general.config.update') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al guardar la configuración.');
                });
        });

        document.getElementById('saldosForm').addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Guardando saldos...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            var formData = new FormData(this);

            fetch("{{ route('reporteria.balance-general.saldos.update') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        var modalEl = document.getElementById('saldosModal');
                        var modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error:', error);
                    Swal.fire('Error', 'Ocurrió un error al guardar los saldos iniciales.', 'error');
                });
        });
    </script>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            .print-layout,
            .print-layout * {
                visibility: visible;
            }

            .print-layout {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .btn,
            form,
            .nav-wrapper {
                display: none !important;
            }
        }
    </style>
@endpush
