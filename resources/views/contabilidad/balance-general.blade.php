@extends('layouts.app')

@section('template_title')
    Balance General y Estado de Resultados
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- BARRA DE FILTROS Y CONTROL --}}
    {{-- PANEL SUPERIOR DE CONTROL Y REPORTES CONTABLES --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('contabilidad.balance-general.index') }}" id="formFiltroBalance">
                {{-- FILA 1: FILTROS PRINCIPALES Y TIPO DE REPORTE --}}
                <div class="row g-3 align-items-end mb-3">

                    {{-- Selector de Tipo de Reporte Contable --}}
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">
                            <i class="fas fa-file-invoice text-primary me-1"></i> Tipo de Reporte Contable:
                        </label>
                        <select name="tipo_reporte" id="selectTipoReporte" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                            @foreach ($catalogoReportes as $key => $rep)
                                <option value="{{ $key }}" {{ $tipoReporte === $key ? 'selected' : '' }}>
                                    [{{ $rep['codigo_conac'] }}] {{ $rep['nombre'] }} {{ !$rep['disponible'] ? '(Próximamente)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted text-xxs d-block mt-1">
                            <i class="fas fa-info-circle me-1"></i>{{ $catalogoReportes[$tipoReporte]['descripcion'] ?? '' }}
                        </small>
                    </div>

                    {{-- Filtro de Empresas (solo si es_admin == 1) --}}
                    @if ($esAdmin)
                        <div class="col-xl-3 col-lg-3 col-md-6">
                            <label class="form-label fw-bold text-dark text-xs mb-1">
                                <i class="fas fa-building text-primary me-1"></i> Empresa(s):
                            </label>
                            <select name="empresas[]" id="selectEmpresas" class="form-select form-select-sm" multiple style="min-height: 38px;">
                                <option value="todas" {{ in_array('todas', $seleccionEmpresas) ? 'selected' : '' }}>-- Todas las Empresas (Consolidado) --</option>
                                @foreach ($todasEmpresas as $emp)
                                    <option value="{{ $emp->id }}" {{ in_array($emp->id, $seleccionEmpresas) && !in_array('todas', $seleccionEmpresas) ? 'selected' : '' }}>
                                        {{ $emp->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted text-xxs">Mantén Ctrl presionado para multi-selección.</small>
                        </div>
                    @endif

                    {{-- Filtro de Unidad / Camión (id_equipo - marca) --}}
                    <div class="col-xl-{{ $esAdmin ? '3' : '4' }} col-lg-{{ $esAdmin ? '3' : '4' }} col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">
                            <i class="fas fa-truck text-primary me-1"></i> Unidad / Camión:
                        </label>
                        <select name="id_equipo" class="form-select form-select-sm">
                            <option value="">-- Todos los Camiones (Consolidado) --</option>
                            @foreach ($equiposDisponibles as $eq)
                                <option value="{{ $eq->id }}" {{ (string)$idEquipo === (string)$eq->id ? 'selected' : '' }}>
                                    {{ $eq->texto_select }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted text-xxs">Filtra la operatividad por unidad vehicular.</small>
                    </div>

                    {{-- Fecha de Corte --}}
                    <div class="col-xl-{{ $esAdmin ? '3' : '4' }} col-lg-{{ $esAdmin ? '2' : '4' }} col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Fecha de Corte:
                        </label>
                        <input type="date" name="fecha_corte" class="form-control form-control-sm" value="{{ $fechaCorte }}" required>
                        <small class="text-muted text-xxs">Calcula saldos al día especificado.</small>
                    </div>

                </div>

                {{-- FILA 2: BARRA DE ACCIONES Y BOTONES CON WRAP Y VISIBILIDAD GARANTIZADA --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between pt-2 border-top gap-2">
                    
                    {{-- Botón de Aplicar Filtro --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm mb-0">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Reporte
                        </button>
                    </div>

                    {{-- Accesos Directos Contables --}}
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('contabilidad.poliza-apertura.index') }}" class="btn btn-outline-info btn-sm shadow-sm mb-0" title="Póliza de Apertura / Saldos Iniciales">
                            <i class="fas fa-file-invoice-dollar me-1"></i> Póliza de Apertura
                        </a>
                        <a href="{{ route('contabilidad.configuracion.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm mb-0" title="Configurar cuentas CONAC y matriz">
                            <i class="fas fa-sliders-h me-1"></i> Matriz y Cuentas CONAC
                        </a>
                        <a href="{{ route('contabilidad.manual') }}" class="btn btn-outline-warning btn-sm shadow-sm mb-0" title="Manual Contable y Guía CONAC">
                            <i class="fas fa-book me-1"></i> Guía Contabilizadora
                        </a>

                        {{-- Botón Vista Previa / Imprimir --}}
                        @php
                            $queryParams = array_merge(request()->all(), ['tipo_reporte' => $tipoReporte]);
                            $queryPdf = array_merge($queryParams, ['download' => 1]);
                        @endphp
                        <a href="{{ route('contabilidad.balance-general.imprimir', $queryParams) }}" target="_blank" class="btn btn-danger btn-sm shadow-sm mb-0" title="Abrir versión imprimible">
                            <i class="fas fa-print me-1"></i> Imprimir
                        </a>
                        <a href="{{ route('contabilidad.balance-general.imprimir', $queryPdf) }}" target="_blank" class="btn btn-outline-danger btn-sm shadow-sm mb-0" title="Descargar archivo PDF directo">
                            <i class="fas fa-file-pdf me-1"></i> Descargar PDF
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- HOJA DE BALANCE GENERAL (FORMATO MODELO TP 002) --}}
    <div class="card shadow border-0" id="hojaBalance">
        <div class="card-body p-4 p-md-5">

            {{-- ENCABEZADO FORMAL DEL BALANCE --}}
            <div class="text-center border-bottom pb-4 mb-4">
                <span class="badge bg-light text-primary border px-3 py-1 mb-2 fw-semibold" style="letter-spacing: 1px;">
                    LINEAMIENTOS CONAC • PARTIDA DOBLE
                </span>
                <h3 class="fw-bolder text-dark mb-1 text-uppercase" style="letter-spacing: 1.5px;">BALANCE GENERAL</h3>
                
                <h5 class="text-secondary fw-normal mb-1">
                    @if ($equipoSeleccionado)
                        UNIDAD: <strong class="text-dark">{{ $equipoSeleccionado->texto_select }}</strong>
                    @else
                        CONSOLIDADO GLOBAL DE UNIDADES
                    @endif
                </h5>

                <p class="text-muted text-sm mb-0">
                    AL <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($fechaCorte)->isoFormat('D [DE] MMMM [DE] YYYY') }}</span>
                    @if ($esAdmin && in_array('todas', $seleccionEmpresas))
                        <span class="badge bg-secondary text-white ms-2">Consolidado Multi-Empresa</span>
                    @endif
                </p>

                {{-- Status de Cuadre Contable --}}
                <div class="mt-2">
                    @if ($balance['cuadrado'])
                        <span class="badge bg-success-light text-success border border-success px-3 py-1 text-xs">
                            <i class="fas fa-check-circle me-1"></i> Balance Cuadrado: Activo = Pasivo + Capital ($ {{ number_format($balance['activo']['total'], 2) }})
                        </span>
                    @else
                        <span class="badge bg-danger-light text-danger border border-danger px-3 py-1 text-xs">
                            <i class="fas fa-exclamation-triangle me-1"></i> Diferencia Contable: $ {{ number_format($balance['diferencia'], 2) }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- CUERPO DEL BALANCE: DOS COLUMNAS BALANCEADAS --}}
            <div class="row g-4 mb-4">

                {{-- COLUMNA IZQUIERDA: ACTIVO --}}
                <div class="col-md-6 border-end pe-md-4">
                    <div class="d-flex justify-content-between align-items-center bg-light p-2 px-3 rounded mb-3 border">
                        <h6 class="mb-0 fw-bold text-primary text-uppercase" style="letter-spacing: 1px;">
                            <i class="fas fa-wallet me-2"></i>ACTIVO
                        </h6>
                        <span class="text-xs text-muted">Bancos, Clientes y Equipos</span>
                    </div>

                    <table class="table table-sm table-hover align-middle mb-0">
                        <tbody>
                            @forelse ($balance['activo']['items'] as $item)
                                <tr>
                                    <td class="text-sm py-2 {{ $item['es_calculado'] ? 'fw-bold text-dark' : 'text-secondary ps-3' }}">
                                        @if (!$item['es_calculado'])
                                            <i class="fas fa-angle-right text-muted me-1" style="font-size: 10px;"></i>
                                        @endif
                                        {{ $item['concepto'] }}
                                    </td>
                                    <td class="text-end text-sm py-2 fw-semibold {{ $item['monto'] < 0 ? 'text-danger' : 'text-dark' }}" style="width: 140px;">
                                        $ {{ number_format($item['monto'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3 text-xs">Sin registros de activo en este corte.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-primary border-top border-2 border-primary">
                                <th class="text-uppercase fw-bolder py-3 text-dark">SUMA DEL ACTIVO</th>
                                <th class="text-end fw-bolder py-3 text-primary fs-6">
                                    $ {{ number_format($balance['activo']['total'], 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- COLUMNA DERECHA: PASIVO Y PATRIMONIO --}}
                <div class="col-md-6 ps-md-4">

                    {{-- SECCIÓN PASIVO --}}
                    <div class="d-flex justify-content-between align-items-center bg-light p-2 px-3 rounded mb-3 border">
                        <h6 class="mb-0 fw-bold text-danger text-uppercase" style="letter-spacing: 1px;">
                            <i class="fas fa-file-invoice-dollar me-2"></i>PASIVO
                        </h6>
                        <span class="text-xs text-muted">Proveedores y Acreedores</span>
                    </div>

                    <table class="table table-sm table-hover align-middle mb-4">
                        <tbody>
                            @forelse ($balance['pasivo']['items'] as $item)
                                <tr>
                                    <td class="text-sm py-2 {{ $item['es_calculado'] ? 'fw-bold text-dark' : 'text-secondary ps-3' }}">
                                        @if (!$item['es_calculado'])
                                            <i class="fas fa-angle-right text-muted me-1" style="font-size: 10px;"></i>
                                        @endif
                                        {{ $item['concepto'] }}
                                    </td>
                                    <td class="text-end text-sm py-2 fw-semibold {{ $item['monto'] < 0 ? 'text-danger' : 'text-dark' }}" style="width: 140px;">
                                        $ {{ number_format($item['monto'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-2 text-xs">Sin pasivos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light border-top">
                                <th class="text-uppercase fw-bold py-2 text-dark text-xs">TOTAL PASIVO</th>
                                <th class="text-end fw-bold py-2 text-dark text-sm">
                                    $ {{ number_format($balance['pasivo']['total'], 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>

                    {{-- SECCIÓN PATRIMONIO / CAPITAL --}}
                    <div class="d-flex justify-content-between align-items-center bg-light p-2 px-3 rounded mb-3 border">
                        <h6 class="mb-0 fw-bold text-success text-uppercase" style="letter-spacing: 1px;">
                            <i class="fas fa-chart-pie me-2"></i>CAPITAL CONTABLE / PATRIMONIO
                        </h6>
                        <span class="text-xs text-muted">Utilidades y Resultados</span>
                    </div>

                    <table class="table table-sm table-hover align-middle mb-0">
                        <tbody>
                            @forelse ($balance['patrimonio']['items'] as $item)
                                <tr>
                                    <td class="text-sm py-2 {{ $item['es_calculado'] ? 'fw-bold text-dark' : 'text-secondary ps-3' }}">
                                        @if (!$item['es_calculado'])
                                            <i class="fas fa-angle-right text-muted me-1" style="font-size: 10px;"></i>
                                        @endif
                                        {{ $item['concepto'] }}
                                    </td>
                                    <td class="text-end text-sm py-2 fw-semibold {{ $item['monto'] < 0 ? 'text-danger' : 'text-dark' }}" style="width: 140px;">
                                        $ {{ number_format($item['monto'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-2 text-xs">Sin movimientos de capital.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light border-top">
                                <th class="text-uppercase fw-bold py-2 text-dark text-xs">TOTAL CAPITAL CONTABLE</th>
                                <th class="text-end fw-bold py-2 text-dark text-sm">
                                    $ {{ number_format($balance['patrimonio']['total'], 2) }}
                                </th>
                            </tr>
                            <tr class="table-primary border-top border-2 border-primary">
                                <th class="text-uppercase fw-bolder py-3 text-dark">SUMA DEL PASIVO Y CAPITAL</th>
                                <th class="text-end fw-bolder py-3 text-primary fs-6">
                                    $ {{ number_format($balance['total_pasivo_patrimonio'], 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>

                </div>

            </div>

            {{-- SECCIÓN INFERIOR: RESUMEN DE ESTADO DE RESULTADOS (MODELO PDF) --}}
            <div class="mt-4 pt-4 border-top">
                <div class="card bg-light border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white p-2 rounded-circle">
                                <i class="fas fa-chart-line"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">ESTADO DE RESULTADOS (DEL EJERCICIO AL CORTE)</h6>
                                <small class="text-muted">Desglose de Operación y Separación de Intereses Bancarios CONAC</small>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-muted">Periodo: </span>
                            <span class="badge bg-light text-dark border">{{ $balance['fecha_inicio_ejercicio'] }} al {{ $balance['fecha_corte'] }}</span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4 align-items-center">

                            {{-- Desglose Numérico con Intereses Separados --}}
                            <div class="col-lg-8">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0 bg-white rounded shadow-sm border">
                                        <tbody>
                                            <tr>
                                                <td class="py-2 px-3 fw-semibold text-dark">
                                                    <i class="fas fa-plus text-success me-2"></i> Ingresos por Fletes y Servicios
                                                </td>
                                                <td class="py-2 px-3 text-end fw-bold text-dark" style="width: 160px;">
                                                    $ {{ number_format($balance['estado_resultados']['ingresos_fletes'], 2) }}
                                                </td>
                                            </tr>
                                            <tr class="table-info">
                                                <td class="py-2 px-3 fw-bold text-info">
                                                    <i class="fas fa-percent text-info me-2"></i> Intereses Bancarios / Rendimientos Financieros (4.3.1.1)
                                                    <span class="badge bg-info text-dark ms-2" style="font-size: 10px;">Separado de Fletes</span>
                                                </td>
                                                <td class="py-2 px-3 text-end fw-bold text-info" style="width: 160px;">
                                                    $ {{ number_format($balance['estado_resultados']['intereses_ganados'], 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 px-3 fw-semibold text-danger">
                                                    <i class="fas fa-minus text-danger me-2"></i> Gastos Operativos y Generales (5.1.*)
                                                </td>
                                                <td class="py-2 px-3 text-end fw-bold text-danger" style="width: 160px;">
                                                    - $ {{ number_format($balance['estado_resultados']['gastos_totales'], 2) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="border-top border-2 border-dark">
                                                <th class="py-3 px-3 text-uppercase fw-bolder text-dark">
                                                    <i class="fas fa-equals text-primary me-2"></i> UTILIDAD NETA DEL EJERCICIO
                                                </th>
                                                <th class="py-3 px-3 text-end fw-bolder {{ $balance['estado_resultados']['utilidad_neta'] < 0 ? 'text-danger' : 'text-success' }} fs-6">
                                                    $ {{ number_format($balance['estado_resultados']['utilidad_neta'], 2) }}
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            {{-- Tarjeta de Resumen Visual --}}
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm text-center p-3 {{ $balance['estado_resultados']['utilidad_neta'] >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                    <small class="text-uppercase opacity-75 fw-semibold" style="letter-spacing: 1px;">Resultado del Ejercicio</small>
                                    <h2 class="fw-bolder my-2 text-white">
                                        $ {{ number_format($balance['estado_resultados']['utilidad_neta'], 2) }}
                                    </h2>
                                    <small class="opacity-75">
                                        {{ $balance['estado_resultados']['utilidad_neta'] >= 0 ? 'Utilidad acumulada al corte' : 'Déficit acumulado al corte' }}
                                    </small>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- FIRMAS DE CONFORMIDAD / AUDITORÍA (COMO EN EL PDF) --}}
            <div class="row text-center mt-5 pt-4 text-xs text-muted">
                <div class="col-4">
                    <div class="border-top pt-2 mx-4">
                        ELABORÓ<br>
                        <strong>DEPARTAMENTO CONTABLE</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top pt-2 mx-4">
                        REVISÓ<br>
                        <strong>DIRECCIÓN FINANCIERA</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top pt-2 mx-4">
                        AUTORIZÓ<br>
                        <strong>DIRECCIÓN GENERAL</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
