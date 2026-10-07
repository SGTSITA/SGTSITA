@extends('layouts.app')

@section('template_title')
    Gastos
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0">Gastos</h5>
                <p class="text-sm text-muted mb-0">Modulo unificado para gastos del periodo, viaje, cotización.</p>
            </div>
            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalGastoNew">
                <i class="fa fa-plus"></i> Registrar gasto
            </button>
        </div>

        <!-- Cards de Totales -->
        <div class="px-4 py-3 bg-light border-bottom">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border shadow-none mb-0">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-xs text-muted mb-1 font-weight-bold text-uppercase">Total Gastos</p>
                                <h4 class="mb-0 font-weight-bolder text-dark" id="card-total-monto">$ 0.00</h4>
                            </div>
                            <div class="rounded-circle p-3 d-flex align-items-center justify-content-center"
                                style="width: 48px; height: 48px; background-color: rgba(94, 114, 228, 0.15); color: #5e72e4;">
                                <i class="fa fa-calculator text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-none mb-0">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-xs text-success mb-1 font-weight-bold text-uppercase">Total Pagado</p>
                                <h4 class="mb-0 font-weight-bolder text-success" id="card-pagado-monto">$ 0.00</h4>
                            </div>
                            <div class="rounded-circle p-3 d-flex align-items-center justify-content-center"
                                style="width: 48px; height: 48px; background-color: rgba(45, 206, 137, 0.15); color: #2dce89;">
                                <i class="fa fa-check-circle text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-none mb-0">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-xs text-danger mb-1 font-weight-bold text-uppercase">Total Pendiente</p>
                                <h4 class="mb-0 font-weight-bolder text-danger" id="card-pendiente-monto">$ 0.00</h4>
                            </div>
                            <div class="rounded-circle p-3 d-flex align-items-center justify-content-center"
                                style="width: 48px; height: 48px; background-color: rgba(245, 54, 92, 0.15); color: #f5365c;">
                                <i class="fa fa-exclamation-circle text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-2 mb-3 align-items-end">

                <div class="col-md-2">
                    <label class="form-label text-sm mb-1">Periodo</label>
                    <input type="text" id="daterange" readonly class="form-control form-control-sm">
                </div>

                <div class="col-md-2">
                    <label class="form-label text-sm mb-1">Unidad / Equipo</label>
                    <select id="gastosNewEquipo" class="form-control form-control-sm">
                        <option value="">Todas las unidades</option>
                        @foreach ($equipos as $e)
                            <option value="{{ $e->id }}">{{ $e->texto_select }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-sm mb-1">Tipo de Gasto</label>
                    <select id="gastosNewTipo" class="form-control form-control-sm">
                        <option value="todos">Todos</option>
                        <option value="general">General</option>
                        <option value="periodo">Periodo</option>
                        <option value="unidad">Unidad</option>
                        <option value="viaje">Viaje</option>
                        <option value="cotizacion">Cotización</option>
                        <option value="contenedor">Contenedor</option>
                        <option value="operador">Operador</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-sm mb-1">Categoría</label>
                    <select id="gastosNewCategoria" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->categoria }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-sm mb-1">Subcategoría</label>
                    <select id="gastosNewSubcategoria" class="form-control form-control-sm">
                        <option value="">Todas</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-sm mb-1">Concepto / Folio</label>
                    <div class="d-flex gap-1">
                        <input type="text" id="gastosNewSearch" class="form-control form-control-sm" placeholder="Buscar...">
                        <button type="button" class="btn btn-sm btn-outline-primary mb-0 px-2"
                            id="btnGastosNewBuscar" title="Buscar">
                            <i class="fa fa-search"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-success mb-0 px-2 text-nowrap" id="btnPayMultiple"
                            title="Pagar seleccionados">
                            Pagar
                        </button>
                    </div>
                </div>

            </div>
            <div class="row">
                <div id="myGridNew" class="col-12 ag-theme-alpine" style="height: 550px"></div>
            </div>
        </div>
    </div>

    <!-- Modal para Aplicar Pago (Pagar Gasto Pendiente) -->
    <div class="modal fade" id="modalPagarGasto" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog">
            <form class="modal-content" id="formPagarGasto">
                @csrf
                <input type="hidden" name="gasto_id" id="pagoGastoId">
                <div class="modal-header">
                    <h5 class="modal-title">Aplicar Pago a Gasto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted mb-0">Concepto del Gasto</label>
                            <div class="font-weight-bold" id="pagoGastoConcepto">-</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted mb-0">Saldo Pendiente</label>
                            <div class="font-weight-bold text-danger text-lg" id="pagoGastoSaldo">$ 0.00</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Cuenta de retiro (Banco) *</label>
                            <select class="form-select" name="cuenta_bancaria_id" id="pagoCuentaBancaria" required>
                                <option value="">-- Seleccionar Cuenta --</option>
                                @foreach ($bancos as $b)
                                    <option value="{{ $b['id'] }}">
                                        {{ $b['display'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Monto a Pagar *</label>
                            <input type="number" step="0.01" min="0.01" name="monto" id="pagoMonto"
                                class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de Pago *</label>
                            <input type="date" name="fecha_pago" id="pagoFecha" class="form-control"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Referencia del Pago</label>
                            <input type="text" name="referencia" class="form-control"
                                placeholder="Ej. Transferencia 12345">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success">Aplicar Pago</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Pagar Múltiples Gastos -->
    <div class="modal fade" id="modalPagarMultiple" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog">
            <form class="modal-content" id="formPagarMultiple">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Pagar Múltiples Gastos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted mb-0">Total a Pagar</label>
                            <div class="font-weight-bold text-danger text-lg" id="pagoMultipleTotal">$ 0.00</div>
                            <div class="text-sm text-muted mb-2" id="pagoMultipleResumen">0 gastos seleccionados para
                                aplicar</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Cuenta de retiro (Banco) *</label>
                            <select class="form-select" name="cuenta_bancaria_id" id="pagoMultipleCuenta" required>
                                <option value="">-- Seleccionar Cuenta --</option>
                                @foreach ($bancos as $b)
                                    <option value="{{ $b['id'] }}">
                                        {{ $b['display'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de Pago *</label>
                            <input type="date" name="fecha_pago" id="pagoMultipleFecha" class="form-control"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Referencia del Pago</label>
                            <input type="text" name="referencia" class="form-control"
                                placeholder="Ej. Transferencia Múltiple">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success">Aplicar Pago</button>
                </div>
            </form>
        </div>
    </div>

    {{-- <div class="modal fade" id="modalGastoNew" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="formGastoNew">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Registrar gasto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <input type="hidden" name="id" id="gastoIdNew" value="">
                        <input type="hidden" name="tipo_gasto" id="tipo_gasto" value="periodo">
                        <input type="hidden" name="metodo_imputacion" id="metodo_imputacion" value="directo">

                        <!-- Aplicar Gasto A: Radios visuales -->
                        <div class="col-12">
                            <h6 class="mb-3">Aplicar gasto a:</h6>
                            <div class="option-group">
                                <label class="custom-option selected">
                                    <input type="radio" checked name="formasAplicar" value="Periodo"
                                        onchange="handleSelectionNew(this)" />
                                    <i class="fas fa-clock icon"></i>
                                    <div class="text-group">
                                        <div class="text">Periodo</div>
                                        <div class="text text-xs text-muted" id="periodoGastoNewInfo"
                                            style="font-size: 11px;">
                                            -
                                        </div>
                                    </div>
                                    <i class="fas fa-check check-icon"></i>
                                </label>
                                <label class="custom-option">
                                    <input type="radio" name="formasAplicar" value="Equipo"
                                        onchange="handleSelectionNew(this)" />
                                    <i class="fas fa-truck-moving icon"></i>
                                    <span class="text">Unidad (Equipo)</span>
                                    <i class="fas fa-check check-icon"></i>
                                </label>
                                <label class="custom-option">
                                    <input type="radio" name="formasAplicar" value="Viaje"
                                        onchange="handleSelectionNew(this)" />
                                    <i class="fas fa-compass icon"></i>
                                    <span class="text">Contenedor (Viaje)</span>
                                    <i class="fas fa-check check-icon"></i>
                                </label>
                            </div>
                        </div>

                        <!-- Sección de Selección Múltiple para Unidades -->
                        <div class="col-12 d-none aplicacion-gastos-new" id="aplicacion-equipoNew">
                            <label class="form-label font-weight-bold text-info">Seleccione Unidades (Equipo)</label>
                            <select class="form-control" name="unidades[]" id="selectUnidadesNew" multiple>
                                @foreach ($equipos as $e)
                                    <option value="{{ $e->id }}">{{ $e->texto_select }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sección de Selección Múltiple para Viajes -->
                        <div class="col-12 d-none aplicacion-gastos-new" id="aplicacion-viajeNew">
                            <label class="form-label font-weight-bold text-info">Seleccione Viajes (Contenedor)</label>
                            <select class="form-control" name="viajes[]" id="selectViajesNew" multiple>
                                @foreach ($viajes as $v)
                                    <option value="{{ $v->id }}">
                                        Contenedor: {{ $v->Contenedor?->num_contenedor ?: 'S/N' }} (Inicio:
                                        {{ $v->fecha_inicio }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-weight-bold text-info">¿Dónde debe impactar este gasto?</label>
                            <select name="impacto" id="impacto" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="periodo">Gasto Administrativo / Periodo</option>
                                <option value="viaje">Gastos Operativo del Viaje</option>
                                <option value="cotizacion">Gastos + Costo de Cotización</option>
                            </select>

                        </div>

                        <!-- Concepto / Descripción breve -->
                        <div class="col-md-12">
                            <label class="form-label">Concepto / Motivo *</label>
                            <input type="text" name="concepto" id="conceptoNew" class="form-control"
                                placeholder="Escriba el concepto del gasto" required>
                        </div>

                        <!-- Monto y Categoría -->
                        <div class="col-md-6">
                            <label class="form-label">Monto total *</label>
                            <input type="number" step="0.01" min="0.01" name="monto_total" id="monto_totalNew"
                                class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría *</label>
                            <select class="form-select" name="categoria_gasto_id" id="categoria_gasto_idNew" required>
                                <option value="">-- Seleccionar Categoría --</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->categoria }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Concepto (Subcategoría) *</label>
                            <select class="form-select" name="gasto_concepto_id" id="gasto_concepto_idNew" required>
                                <option value="">-- Seleccionar Concepto --</option>
                            </select>
                        </div>

                        <!-- Condición de Pago y Fecha de Gasto -->
                        <div class="col-md-6">
                            <label class="form-label">Condición de pago</label>
                            <select class="form-select" id="tipoPagoNew" name="tipoPago" required>
                                <option value="0">Contado</option>
                                <option value="1">Diferido</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de aplicación *</label>
                            <input type="date" name="fecha_gasto" id="fecha_gastoNew" class="form-control"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <!-- Configuración de Pago Diferido (Colapsable) -->
                        <div class="col-12 collapse" id="seccionDiferidoNew">
                            <div class="p-3 mb-2 bg-gray-100 border border-secondary border-radius-md"
                                style="background-color: #f8f9fa; border-radius: 8px;">
                                <h6 class="mb-1 text-sm font-weight-bold">Configuración de pago diferido</h6>
                                <small class="text-muted mb-3 d-block" style="font-size: 11px;">
                                    Determine el rango de fechas para distribuir el pago en modalidad diferida mensualmente.
                                </small>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-label text-xs">Fecha de inicio</label>
                                            <input name="txtDiferirFechaInicia" id="txtDiferirFechaIniciaNew"
                                                type="date" class="form-control form-control-sm fechasDiferirNew" />
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label text-xs">Fecha de finalización</label>
                                            <input name="txtDiferirFechaTermina" id="txtDiferirFechaTerminaNew"
                                                type="date" class="form-control form-control-sm fechasDiferirNew" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-xs">Resumen del pago diferido</label>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <tbody>
                                                    <tr>
                                                        <th scope="row" class="text-xs py-1" style="font-size: 11px;">
                                                            Número de periodos</th>
                                                        <td class="text-end py-1" style="font-size: 11px;"><strong
                                                                id="labelDiasPeriodoNew">0</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row" class="text-xs py-1" style="font-size: 11px;">
                                                            Monto por periodo</th>
                                                        <td class="text-end py-1" style="font-size: 11px;"><strong
                                                                id="labelGastoDiarioNew">$ 0.00</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row" class="text-xs py-1" style="font-size: 11px;">
                                                            Total del gasto</th>
                                                        <td class="text-end py-1" style="font-size: 11px;"><strong
                                                                id="labelMontoGastoNew">$ 0.00</strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cuenta Retiro (Banco) -->
                        <div class="col-12" id="divCuentaRetiroNew">
                            <label class="form-label">Cuenta de retiro (Banco) *</label>
                            <select class="form-select" id="id_banco1New" name="id_banco1" required>
                                <option value="">-- Seleccionar Cuenta --</option>
                                @foreach ($bancos as $b)
                                    <option value="{{ $b['id'] }}">
                                        {{ $b['display'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-info">Guardar</button>
                </div>
            </form>
        </div>
    </div> --}}
    <div class="modal fade" id="modalGastoNew" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="formGastoNew">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Registrar gasto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="alertDraftRestaurado" class="alert alert-primary alert-dismissible fade show d-none py-2 px-3 mb-3 fs-7" role="alert">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-history text-primary fs-5"></i>
                                <span><strong>Borrador recuperado:</strong> Se restauraron los datos que tenías en progreso.</span>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-xs py-0 px-2 fs-8" id="btnDescartarDraft">
                                Descartar borrador
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">

                        <input type="hidden" name="id" id="gastoIdNew" value="">
                        <input type="hidden" name="tipo_gasto" id="tipo_gasto" value="periodo">
                        <input type="hidden" name="metodo_imputacion" id="metodo_imputacion" value="directo">

                        {{-- =======================
                        1. APLICAR GASTO A
                    ======================== --}}
                        <div class="col-12">
                            <h6 class="mb-3">Aplicar gasto a</h6>

                            <div class="option-group">

                                <label class="custom-option selected">
                                    <input type="radio" checked name="formasAplicar" value="Periodo"
                                        onchange="handleSelectionNew(this)" />

                                    <i class="fas fa-clock icon"></i>

                                    <div class="text-group">
                                        <div class="text">Periodo</div>
                                        <div class="text text-xs text-muted" id="periodoGastoNewInfo"
                                            style="font-size:11px;">
                                            -
                                        </div>
                                    </div>

                                    <i class="fas fa-check check-icon"></i>
                                </label>

                                <label class="custom-option">
                                    <input type="radio" name="formasAplicar" value="Equipo"
                                        onchange="handleSelectionNew(this)" />

                                    <i class="fas fa-truck-moving icon"></i>

                                    <span class="text">
                                        Unidad (Equipo)
                                    </span>

                                    <i class="fas fa-check check-icon"></i>
                                </label>

                                <label class="custom-option">
                                    <input type="radio" name="formasAplicar" value="Viaje"
                                        onchange="handleSelectionNew(this)" />

                                    <i class="fas fa-compass icon"></i>

                                    <span class="text">
                                        Contenedor (Viaje)
                                    </span>

                                    <i class="fas fa-check check-icon"></i>
                                </label>

                            </div>
                        </div>

                        {{-- UNIDAD PARA GASTO DE PERIODO / GENERAL (CUANDO APLICA) --}}
                        <div class="col-12 {{ $requiereUnidadGasto ?? false ? '' : 'd-none' }} aplicacion-gastos-new"
                            id="aplicacion-periodoUnidadNew">
                            <label class="form-label fw-bold text-info">
                                Unidad / Equipo correspondiente @if ($requiereUnidadGasto ?? false)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <select class="form-select" name="id_equipo" id="selectPeriodoUnidadNew">
                                <option value="">-- Seleccionar Unidad / Equipo --</option>
                                @foreach ($equipos as $e)
                                    <option value="{{ $e->id }}">{{ $e->texto_select }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                @if ($requiereUnidadGasto ?? false)
                                    Esta empresa requiere asociar cada gasto a una unidad o equipo.
                                @else
                                    Opcional para asociar este gasto general a una unidad.
                                @endif
                            </small>
                        </div>

                        {{-- UNIDADES --}}
                        <div class="col-12 d-none aplicacion-gastos-new" id="aplicacion-equipoNew">

                            <label class="form-label fw-bold text-info">
                                Seleccione Unidades (Equipo)
                            </label>

                            <select class="form-control" name="unidades[]" id="selectUnidadesNew" multiple>

                                @foreach ($equipos as $e)
                                    <option value="{{ $e->id }}">{{ $e->texto_select }}</option>
                                @endforeach

                            </select>

                        </div>

                        {{-- VIAJES --}}
                        <div class="col-12 d-none aplicacion-gastos-new" id="aplicacion-viajeNew">

                            <label class="form-label fw-bold text-info">
                                Seleccione Viajes (Contenedor)
                            </label>

                            <select class="form-control" name="viajes[]" id="selectViajesNew" multiple>

                                @foreach ($viajes as $v)
                                    <option value="{{ $v->id }}">
                                        Contenedor:
                                        {{ $v->Contenedor?->num_contenedor ?: 'S/N' }}
                                        (Inicio:
                                        {{ $v->fecha_inicio }})
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        {{-- =======================
                        2. IMPACTO
                    ======================== --}}

                        <div class="col-12">
                            <label class="form-label fw-bold text-info">
                                ¿Dónde debe impactar este gasto?
                            </label>

                            <select name="impacto" id="impacto" class="form-select" required>

                                <option value="">Seleccione</option>

                                <option value="periodo">
                                    Gasto Administrativo / Periodo
                                </option>

                                <option value="viaje">
                                    Gasto Operativo del Viaje
                                </option>

                                <option value="cotizacion">
                                    Gastos + Costo de Cotización
                                </option>

                            </select>

                        </div>

                        {{-- =======================
                        3. CLASIFICACIÓN
                    ======================== --}}

                        <div class="col-md-6">

                            <label class="form-label">
                                Categoría *
                            </label>

                            <select class="form-select" name="categoria_gasto_id" id="categoria_gasto_idNew" required>

                                <option value="">
                                    -- Seleccionar Categoría --
                                </option>

                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">
                                        {{ $categoria->categoria }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Concepto (Subcategoría) *
                            </label>

                            <select class="form-select" name="gasto_concepto_id" id="gasto_concepto_idNew" required>

                                <option value="">
                                    -- Seleccionar Concepto --
                                </option>

                            </select>

                        </div>

                        {{-- =======================
                        4. INFORMACIÓN DEL GASTO
                    ======================== --}}

                        <div class="col-md-6">

                            <label class="form-label">
                                Concepto / Motivo *
                            </label>

                            <input type="text" name="concepto" id="conceptoNew" class="form-control"
                                placeholder="Escriba el concepto del gasto" required>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Monto total *
                            </label>

                            <input type="number" step="0.01" min="0.01" name="monto_total" id="monto_totalNew"
                                class="form-control" placeholder="0.00" required>

                        </div>

                        {{-- =======================
                        5. FECHA Y PAGO
                    ======================== --}}

                        <div class="col-md-6">

                            <label class="form-label">
                                Fecha de aplicación *
                            </label>

                            <input type="date" name="fecha_gasto" id="fecha_gastoNew" class="form-control"
                                value="{{ now()->format('Y-m-d') }}" required>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Condición de pago
                            </label>

                            <select class="form-select" id="tipoPagoNew" name="tipoPago" required>

                                <option value="0">
                                    Contado
                                </option>

                                <option value="1">
                                    Diferido
                                </option>

                            </select>

                        </div>

                        {{-- PAGO DIFERIDO --}}
                        <div class="col-12 collapse" id="seccionDiferidoNew">

                            <div class="p-3 border rounded bg-light">

                                <h6 class="mb-1">
                                    Configuración de pago diferido
                                </h6>

                                <small class="text-muted d-block mb-3">
                                    Determine el rango de fechas para distribuir
                                    el pago mensualmente.
                                </small>

                                <div class="row">

                                    <div class="col-md-6">

                                        <div class="mb-2">

                                            <label class="form-label text-xs">
                                                Fecha de inicio
                                            </label>

                                            <input type="date" name="txtDiferirFechaInicia"
                                                id="txtDiferirFechaIniciaNew"
                                                class="form-control form-control-sm fechasDiferirNew">

                                        </div>

                                        <div>

                                            <label class="form-label text-xs">
                                                Fecha de finalización
                                            </label>

                                            <input type="date" name="txtDiferirFechaTermina"
                                                id="txtDiferirFechaTerminaNew"
                                                class="form-control form-control-sm fechasDiferirNew">

                                        </div>

                                    </div>

                                    <div class="col-md-6">

                                        <label class="form-label text-xs">
                                            Resumen
                                        </label>

                                        <table class="table table-sm table-bordered mb-0">

                                            <tbody>

                                                <tr>
                                                    <th>Número de periodos</th>
                                                    <td class="text-end">
                                                        <strong id="labelDiasPeriodoNew">0</strong>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th>Monto por periodo</th>
                                                    <td class="text-end">
                                                        <strong id="labelGastoDiarioNew">$0.00</strong>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th>Total del gasto</th>
                                                    <td class="text-end">
                                                        <strong id="labelMontoGastoNew">$0.00</strong>
                                                    </td>
                                                </tr>

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- =======================
                        6. CUENTA BANCARIA
                    ======================== --}}

                        <div class="col-12" id="divCuentaRetiroNew">

                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <label class="form-label mb-0 fw-bold">
                                    Cuenta de retiro (Banco) *
                                </label>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-2 py-1"
                                        id="btnOpenMovimientoInGasto" style="background-color: #5c67f2; border-color: #5c67f2; font-weight: 500;"
                                        title="Registrar un abono o depósito en una cuenta bancaria">
                                        <i class="fa fa-plus-circle"></i> Movimiento
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-2 py-1"
                                        id="btnOpenTransferenciaInGasto" style="background-color: #10b981; border-color: #10b981; font-weight: 500;"
                                        title="Transferir fondos entre cuentas bancarias">
                                        <i class="fa fa-exchange-alt"></i> Transferir
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center px-2 py-1"
                                        id="btnRefreshBancosInGasto" title="Recargar cuentas y saldos en vivo">
                                        <i class="fa fa-sync-alt" id="iconRefreshBancos"></i>
                                    </button>
                                </div>
                            </div>

                            <select class="form-select select-cuentas-bancarias" id="id_banco1New" name="id_banco1" required>

                                <option value="">
                                    -- Seleccionar Cuenta --
                                </option>

                                @foreach ($bancos as $b)
                                    <option value="{{ $b['id'] }}">
                                        {{ $b['display'] }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">
                        Cerrar
                    </button>

                    <button type="submit" class="btn btn-sm btn-info">
                        Guardar
                    </button>
                </div>

            </form>
        </div>
    </div>

    <div class="modal fade" id="modalHistorialPagos" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Historial de pagos
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">

                    <div id="historialPagosBody">

                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- Sub-modales para operaciones bancarias in-situ desde el registro de gasto --}}
    @include('gastos.modals.modal_movimiento_in_gasto')
    @include('gastos.modals.modal_transferencia_in_gasto')
@endsection

@section('js_custom')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />



    <!-- AG Grid Community y estilos -->
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <!-- Cargar Choices.js y Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        window.mesinicio = null;
        window.mesfin = null;

        window.idEmpresa = @json(auth()->user()->id_empresa ?? 0);
        window.requiereUnidadGasto = @json($requiereUnidadGasto ?? false);
        const gastosRoutes = {
            data: @json(route('gastos.data')),
            store: @json(route('gastos.store')),
            cuentasBancarias: @json(route('gastos.cuentas_bancarias')),
            bancosMovimiento: @json(route('gastos.bancos_movimiento')),
            bancosTransferencia: @json(route('gastos.bancos_transferencia')),
            historial: '/gastos',
            cancelarPago: '/gastos/pagos',
        };
        const GASTO_DRAFT_KEY = `sgt_gasto_draft_${window.idEmpresa || 'default'}`;




        function currencyFormatter(value) {
            if (value === null || value === undefined) return '';
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(value);
        }

        function formatFecha(params) {
            if (!params.value) return '';
            const parts = params.value.split('-');
            if (parts.length === 3) {
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            return params.value;
        }

        class EstatusRenderer {
            init(params) {
                this.eGui = document.createElement('div');
                this.eGui.style.display = 'flex';
                this.eGui.style.flexDirection = 'column';
                this.eGui.style.alignItems = 'flex-start';
                this.eGui.style.lineHeight = '1.2';
                this.eGui.style.justifyContent = 'center';
                this.eGui.style.height = '100%';

                const val = params.data.estatus;
                const badge = document.createElement('span');

                if (val === 'pagado') {
                    badge.className = 'badge bg-success mb-1';
                    badge.textContent = 'Pagado';
                    this.eGui.appendChild(badge);

                    // Si hay pagos, mostrar información de la cuenta y fecha
                    const pagos = params.data.pagos || [];
                    const pagoActivo = pagos.find(p => p.estatus !== 'cancelado');
                    if (pagoActivo) {
                        const infoText = document.createElement('span');
                        infoText.style.fontSize = '9.5px';
                        infoText.style.color = '#525f7f';
                        infoText.style.fontWeight = '500';

                        let bancoInfo = '';
                        if (pagoActivo.cuenta_bancaria) {
                            const cBanc = pagoActivo.cuenta_bancaria;
                            const accNum = cBanc.cuenta_bancaria || '';
                            const last4 = accNum.length > 4 ? '****' + accNum.slice(-4) : accNum;
                            bancoInfo = `${cBanc.nombre} ${last4}`;
                        }

                        const fecha = formatFecha({
                            value: pagoActivo.fecha_pago
                        });
                        infoText.innerHTML =
                            `<i class="fa fa-university text-muted" style="font-size: 8px;"></i> ${bancoInfo}<br><i class="fa fa-calendar-alt text-muted" style="font-size: 8px;"></i> ${fecha}`;
                        this.eGui.appendChild(infoText);
                    }
                } else if (val === 'pagado_parcial') {
                    badge.className = 'badge bg-warning text-dark mb-1';
                    badge.textContent = 'Parcial';
                    this.eGui.appendChild(badge);

                    // Si hay pagos parciales, mostrar info del último pago
                    const pagos = params.data.pagos || [];
                    const pagosActivos = pagos.filter(p => p.estatus !== 'cancelado');
                    if (pagosActivos.length > 0) {
                        const ultimoPago = pagosActivos[pagosActivos.length - 1];
                        const infoText = document.createElement('span');
                        infoText.style.fontSize = '9.5px';
                        infoText.style.color = '#525f7f';
                        infoText.style.fontWeight = '500';

                        let bancoInfo = '';
                        if (ultimoPago.cuenta_bancaria) {
                            const cBanc = ultimoPago.cuenta_bancaria;
                            const accNum = cBanc.cuenta_bancaria || '';
                            const last4 = accNum.length > 4 ? '****' + accNum.slice(-4) : accNum;
                            bancoInfo = `${cBanc.nombre} ${last4}`;
                        }

                        const fecha = formatFecha({
                            value: ultimoPago.fecha_pago
                        });
                        infoText.innerHTML =
                            `<i class="fa fa-university text-muted" style="font-size: 8px;"></i> ${bancoInfo}<br><i class="fa fa-calendar-alt text-muted" style="font-size: 8px;"></i> ${fecha}`;
                        this.eGui.appendChild(infoText);
                    }
                } else if (val === 'pendiente_pago') {
                    badge.className = 'badge bg-danger';
                    badge.textContent = 'Pendiente';
                    this.eGui.appendChild(badge);
                } else if (val === 'cancelado') {
                    badge.className = 'badge bg-secondary';
                    badge.textContent = 'Cancelado';
                    this.eGui.appendChild(badge);
                } else {
                    badge.className = 'badge bg-info';
                    badge.textContent = val || '';
                    this.eGui.appendChild(badge);
                }
            }
            getGui() {
                return this.eGui;
            }
        }

        class VinculosRenderer {
            init(params) {
                this.eGui = document.createElement('div');
                let vinculos = params.value || [];
                const tieneUnidad = vinculos.some(v => v.tipo === 'unidad' || v.tipo === 'equipo');
                if (!tieneUnidad && params.data && (params.data.equipo || params.data.id_equipo)) {
                    const descEquipo = params.data.equipo ?
                        (params.data.equipo.id_equipo || params.data.equipo.placas || `ID #${params.data.equipo.id}`) :
                        `ID #${params.data.id_equipo}`;
                    vinculos = [{ tipo: 'unidad', detalle: descEquipo }, ...vinculos];
                }

                if (vinculos.length === 0) {
                    this.eGui.innerHTML = '<span class="text-muted text-xs">-</span>';
                    return;
                }
                this.eGui.innerHTML = vinculos.map(v => {
                    const esContenedor = v.tipo === 'contenedor' || v.tipo === 'asignacion' || (v.detalle && v.detalle
                        .toLowerCase().includes('contenedor'));
                    const esUnidad = v.tipo === 'unidad' || v.tipo === 'equipo';
                    if (esContenedor) {
                        return `
                            <div style="line-height: 1.3; margin-bottom: 2px;">
                                <span style="font-size: 11.5px; font-weight: bold; background-color: rgba(94, 114, 228, 0.12); color: #5e72e4; border: 1px solid rgba(94, 114, 228, 0.35); border-radius: 4px; padding: 2px 6px; display: inline-block;">
                                    <i class="fa fa-box" style="font-size: 9px; margin-right: 3px;"></i>
                                    <strong>${v.tipo.toUpperCase()}:</strong> ${v.detalle}
                                </span>
                            </div>
                        `;
                    }
                    if (esUnidad) {
                        return `
                            <div style="line-height: 1.3; margin-bottom: 2px;">
                                <span style="font-size: 11.5px; font-weight: bold; background-color: rgba(45, 206, 137, 0.12); color: #2dce89; border: 1px solid rgba(45, 206, 137, 0.35); border-radius: 4px; padding: 2px 6px; display: inline-block;">
                                    <i class="fa fa-truck" style="font-size: 9px; margin-right: 3px;"></i>
                                    <strong>UNIDAD:</strong> ${v.detalle}
                                </span>
                            </div>
                        `;
                    }
                    return `
                        <div style="line-height: 1.2; margin-bottom: 2px;">
                            <span style="font-size: 11px; color: #525f7f;">
                                <i class="fa fa-link text-muted" style="font-size: 9px; margin-right: 3px;"></i>
                                <strong>${v.tipo.toUpperCase()}:</strong> ${v.detalle}
                            </span>
                        </div>
                    `;
                }).join('');
            }
            getGui() {
                return this.eGui;
            }
        }

        class ActionButtonRenderer {
            init(params) {

                this.eGui = document.createElement('div');
                this.eGui.style.display = 'flex';
                this.eGui.style.gap = '3px';
                this.eGui.style.alignItems = 'center';
                this.eGui.style.height = '100%';

                const saldo = parseFloat(params.data.saldo_pendiente) || 0;
                const estatus = params.data.estatus;

                const totalPagos = params.data.total_pagos || 0;
                const ultimoPagoId = params.data.ultimo_pago_id;


                if (saldo > 0 && estatus !== 'cancelado') {

                    const btnPagar = document.createElement('button');

                    btnPagar.className =
                        'btn btn-xs btn-success my-0 py-1 px-2';

                    btnPagar.innerHTML =
                        '<i class="fa fa-money-bill"></i>';

                    btnPagar.title = 'Aplicar pago';

                    btnPagar.addEventListener('click', () => {
                        abrirModalPago(params.data);
                    });

                    this.eGui.appendChild(btnPagar);
                }


                if (estatus !== 'cancelado') {
                    const btnEditar = document.createElement('button');
                    btnEditar.className = 'btn btn-xs btn-warning my-0 py-1 px-2';
                    btnEditar.innerHTML = '<i class="fa fa-edit"></i>';
                    btnEditar.title = 'Editar gasto';
                    btnEditar.addEventListener('click', () => {
                        abrirModalEditar(params.data);
                    });
                    this.eGui.appendChild(btnEditar);
                }

                // HISTORIAL
                if (totalPagos > 0) {

                    const btnHistorial = document.createElement('button');

                    btnHistorial.className =
                        'btn btn-xs btn-info my-0 py-1 px-2';

                    btnHistorial.innerHTML =
                        '<i class="fa fa-list"></i>';

                    btnHistorial.title = 'Historial de pagos';

                    btnHistorial.addEventListener('click', () => {
                        abrirHistorialPagos(params.data.id);
                    });

                    this.eGui.appendChild(btnHistorial);
                }

                // ELIMINAR GASTO
                if (estatus !== 'cancelado') {
                    const btnEliminar = document.createElement('button');
                    btnEliminar.className = 'btn btn-xs btn-danger my-0 py-1 px-2';
                    btnEliminar.innerHTML = '<i class="fa fa-trash"></i>';
                    btnEliminar.title = 'Eliminar gasto';
                    btnEliminar.addEventListener('click', () => {
                        eliminarGastoNew(params.data.id, params.data.concepto, params.data.ultima_fecha_pago ||
                            params.data.fecha_gasto);
                    });
                    this.eGui.appendChild(btnEliminar);
                }

                if (this.eGui.children.length === 0) {
                    this.eGui.innerHTML =
                        '<span class="text-muted text-xs">-</span>';
                }
            }

            getGui() {
                return this.eGui;
            }
        }

        const localeText = {
            page: "Página",
            more: "Más",
            to: "a",
            of: "de",
            next: "Siguiente",
            last: "Último",
            first: "Primero",
            previous: "Anterior",
            loadingOoo: "Cargando...",
            selectAll: "Seleccionar todo",
            searchOoo: "Buscar...",
            blanks: "Vacíos",
            filterOoo: "Filtrar...",
            applyFilter: "Aplicar filtro...",
            equals: "Igual",
            notEqual: "Distinto",
            lessThan: "Menor que",
            greaterThan: "Mayor que",
            contains: "Contiene",
            notContains: "No contiene",
            startsWith: "Empieza con",
            endsWith: "Termina con",
            andCondition: "Y",
            orCondition: "O",
            group: "Grupo",
            columns: "Columnas",
            filters: "Filtros",
            pivotMode: "Modo Pivote",
            groups: "Grupos",
            values: "Valores",
            noRowsToShow: "Sin filas para mostrar",
            pinColumn: "Fijar columna",
            autosizeThiscolumn: "Ajustar columna",
            copy: "Copiar",
            resetColumns: "Restablecer columnas",
            blank: "Vacíos",
            notBlank: "No Vacíos",
            paginationPageSize: "Registros por página",
        };

        const gridOptions = {
            pagination: true,
            paginationPageSize: 15,
            paginationPageSizeSelector: [10, 15, 30, 50, 100],
            rowData: [],
            rowSelection: 'multiple',
            suppressRowClickSelection: false,
            rowHeight: 46,
            headerHeight: 38,
            columnDefs: [{
                    headerName: "",
                    checkboxSelection: true,
                    headerCheckboxSelection: true,
                    headerCheckboxSelectionFilteredOnly: true,
                    width: 50,
                    pinned: 'left'
                },
                {
                    field: "id",
                    headerName: "ID",
                    width: 75,
                    filter: 'agNumberColumnFilter',
                    floatingFilter: true,
                    hide: true
                },
                {
                    field: "fecha_gasto",
                    headerName: "Fecha",
                    width: 110,
                    valueFormatter: formatFecha,
                    filter: 'agDateColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "concepto",
                    headerName: "Concepto",
                    filter: 'agTextColumnFilter',
                    floatingFilter: true,
                    flex: 1
                },
                {
                    field: "tipo_gasto",
                    headerName: "Tipo",
                    width: 180,
                    filter: 'agTextColumnFilter',
                    floatingFilter: true,
                    valueFormatter: (params) => {
                        if (!params.value) return '';
                        const val = params.value.toLowerCase();
                        if (val === 'periodo' || val === 'general' || val === 'unidad') {
                            return 'General / Período';
                        }
                        if (val === 'operador') {
                            return 'Operador';
                        }
                        if (val === 'viaje') {
                            return 'Viaje';
                        }
                        if (val === 'cotizacion' || val === 'cotización' || val === 'extras' || val ===
                            'gasto_extra') {
                            return 'Cotización';
                        }
                        return params.value.charAt(0).toUpperCase() + params.value.slice(1);
                    }
                },
                {
                    field: "categoria",
                    headerName: "Categoría",
                    width: 130,
                    filter: 'agTextColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "vinculos",
                    headerName: "Vínculos",
                    width: 320,
                    cellRenderer: VinculosRenderer,
                    filter: 'agTextColumnFilter',
                    floatingFilter: true,
                    filterValueGetter: (params) => {
                        if (!params.data || !params.data.vinculos) return '';
                        return params.data.vinculos.map(v => `${v.tipo} ${v.detalle}`).join(' ');
                    }
                },
                {
                    field: "monto_total",
                    headerName: "Total",
                    width: 110,
                    valueFormatter: (params) => currencyFormatter(params.value),
                    cellStyle: {
                        textAlign: 'right'
                    },
                    filter: 'agNumberColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "monto_pagado",
                    headerName: "Pagado",
                    width: 110,
                    valueFormatter: (params) => currencyFormatter(params.value),
                    cellStyle: {
                        textAlign: 'right',
                        color: '#2dce89'
                    },
                    filter: 'agNumberColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "saldo_pendiente",
                    headerName: "Saldo",
                    width: 110,
                    valueFormatter: (params) => currencyFormatter(params.value),
                    cellStyle: {
                        textAlign: 'right',
                        color: '#f5365c'
                    },
                    filter: 'agNumberColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "estatus",
                    headerName: "Estatus",
                    width: 130,
                    cellRenderer: EstatusRenderer,
                    filter: 'agTextColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "origen_legacy",
                    headerName: "Origen",
                    width: 110,
                    valueGetter: (params) => params.data.origen_legacy || params.data.origen_modulo || '',
                    filter: 'agTextColumnFilter',
                    floatingFilter: true,
                    hide: true
                },
                {
                    headerName: "Acciones",
                    width: 145,
                    cellRenderer: ActionButtonRenderer,
                    filter: false,
                    suppressMenu: true,
                    sortable: false
                }
            ],
            localeText: localeText,
            onModelUpdated: (event) => {
                recalcularTotalesCards(event.api);
            }
        };

        let apiGrid = null;

        function recalcularTotalesCards(api) {
            const gridApi = api || apiGrid;
            if (!gridApi) return;

            let total = 0;
            let pagado = 0;
            let pendiente = 0;

            gridApi.forEachNodeAfterFilter((node) => {
                if (node.data) {
                    total += parseFloat(node.data.monto_total || 0);
                    pagado += parseFloat(node.data.monto_pagado || 0);
                    pendiente += parseFloat(node.data.saldo_pendiente || 0);
                }
            });

            document.getElementById('card-total-monto').textContent = currencyFormatter(total);
            document.getElementById('card-pagado-monto').textContent = currencyFormatter(pagado);
            document.getElementById('card-pendiente-monto').textContent = currencyFormatter(pendiente);
        }

        async function cargarGastosNew() {

            const from = window.mesinicio;
            const to = window.mesfin;
            const search = document.getElementById('gastosNewSearch').value;
            const tipo_gasto = document.getElementById('gastosNewTipo').value;
            const categoria_id = document.getElementById('gastosNewCategoria').value;
            const subcategoria_id = document.getElementById('gastosNewSubcategoria').value;
            const id_equipo = document.getElementById('gastosNewEquipo')?.value || '';

            const url =
                `${gastosRoutes.data}?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}&search=${encodeURIComponent(search)}&tipo_gasto=${encodeURIComponent(tipo_gasto)}&categoria_id=${encodeURIComponent(categoria_id)}&subcategoria_id=${encodeURIComponent(subcategoria_id)}&id_equipo=${encodeURIComponent(id_equipo)}`;

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const json = await response.json();

                if (apiGrid) {
                    apiGrid.setGridOption('rowData', json.gastos || []);
                }
            } catch (err) {
                console.error(err);
            }
        }

        document.getElementById('btnGastosNewBuscar').addEventListener('click', cargarGastosNew);

        document.getElementById('gastosNewSearch').addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                cargarGastosNew();
            }
        });

        document.getElementById('gastosNewTipo').addEventListener('change', cargarGastosNew);
        document.getElementById('gastosNewEquipo')?.addEventListener('change', cargarGastosNew);

        document.getElementById('gastosNewCategoria').addEventListener('change', function() {
            const catId = this.value;
            const selectSub = document.getElementById('gastosNewSubcategoria');
            selectSub.innerHTML = '<option value="">Todas</option>';
            if (catId) {
                fetch(`/gastos/categorias/${catId}/conceptos`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<option value="">Todas</option>';
                        data.forEach(item => {
                            html += `<option value="${item.id}">${item.nombre}</option>`;
                        });
                        selectSub.innerHTML = html;
                    });
            }
            cargarGastosNew();
        });

        document.getElementById('gastosNewSubcategoria').addEventListener('change', cargarGastosNew);

        document.getElementById('btnPayMultiple').addEventListener('click', () => {
            if (!apiGrid) return;
            const selectedRows = apiGrid.getSelectedRows();
            if (selectedRows.length === 0) {
                Swal.fire('Atención', 'Seleccione al menos un gasto para pagar', 'warning');
                return;
            }

            const valid = selectedRows.every(r => r.estatus !== 'cancelado' && parseFloat(r.saldo_pendiente) > 0);
            if (!valid) {
                Swal.fire('Atención', 'Algunos gastos seleccionados ya están pagados o cancelados.', 'warning');
                return;
            }

            let total = 0;
            selectedRows.forEach(r => total += parseFloat(r.saldo_pendiente));

            document.getElementById('pagoMultipleTotal').textContent = currencyFormatter(total);
            const count = selectedRows.length;
            document.getElementById('pagoMultipleResumen').textContent =
                `${count} gastos seleccionados para aplicar`;
            const modal = new bootstrap.Modal(document.getElementById('modalPagarMultiple'));
            modal.show();
        });

        document.getElementById('formPagarMultiple')?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = event.target;
            const selectedRows = apiGrid.getSelectedRows();
            if (selectedRows.length === 0) return;

            const formData = new FormData(form);
            selectedRows.forEach(r => formData.append('ids[]', r.id));

            Swal.fire({
                title: 'Procesando...',
                text: 'Registrando los pagos múltiples, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch('/gastos/pagar-multiple', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': formData.get('_token')
                    },
                    body: formData
                });
                const json = await response.json();

                if (json.TMensaje === 'success' || json.success) {
                    Swal.fire('Éxito', json.Mensaje || 'Los pagos se registraron correctamente.', 'success')
                        .then(() => {
                            form.reset();
                            bootstrap.Modal.getInstance(document.getElementById('modalPagarMultiple'))
                                .hide();
                            cargarGastosNew();
                        });
                } else {
                    Swal.fire(json.Titulo || 'Error', json.Mensaje || 'No se pudo aplicar el pago múltiple.',
                        'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Ocurrió un error al procesar el pago.', 'error');
            }
        });

        const selectTipoGasto = document.querySelector('select[name="tipo_gasto"]');
        const modalForm = document.getElementById('formGastoNew');

        let choicesUnidades = null;
        let choicesViajes = null;

        document.addEventListener('DOMContentLoaded', () => {


            // Inicializar AG Grid
            const myGridElement = document.querySelector("#myGridNew");
            if (myGridElement) {
                apiGrid = agGrid.createGrid(myGridElement, gridOptions);
            }

            // Inicializar Choices.js
            if (document.getElementById('selectUnidadesNew')) {
                choicesUnidades = new Choices(document.getElementById('selectUnidadesNew'), {
                    removeItemButton: true,
                    noResultsText: 'No se encontraron unidades',
                    noChoicesText: 'No hay opciones disponibles',
                    itemSelectText: 'Seleccionar'
                });
            }

            if (document.getElementById('selectViajesNew')) {
                choicesViajes = new Choices(document.getElementById('selectViajesNew'), {
                    removeItemButton: true,
                    noResultsText: 'No se encontraron viajes',
                    noChoicesText: 'No hay opciones disponibles',
                    itemSelectText: 'Seleccionar'
                });
            }

            // Inicializar Flatpickr


            flatpickr('#txtDiferirFechaIniciaNew', {
                locale: 'es',
                dateFormat: 'Y-m-d',
                allowInput: false,
                onChange: calcDaysNew
            });

            flatpickr('#txtDiferirFechaTerminaNew', {
                locale: 'es',
                dateFormat: 'Y-m-d',
                allowInput: false,
                onChange: calcDaysNew
            });

            // Listeners para autosave de borrador (Draft)
            if (modalForm) {
                modalForm.addEventListener('input', debouncedSaveDraft);
                modalForm.addEventListener('change', debouncedSaveDraft);
            }





        });

        function actualizarTextoPeriodo() {
            const from = window.mesinicio;
            const to = window.mesfin;


            const labelPeriodo = document.getElementById('periodoGastoNewInfo');
            if (labelPeriodo) {
                labelPeriodo.textContent = `${from} AL ${to}`;
            }


            document.getElementById('impacto').value = 'periodo';

        }



        function handleSelectionNew(input) {
            input.closest('.option-group').querySelectorAll('.custom-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            input.parentElement.classList.add('selected');

            document.querySelectorAll('.aplicacion-gastos-new').forEach(div => {
                div.classList.add('d-none');
            });

            if (choicesUnidades) choicesUnidades.removeActiveItems();
            if (choicesViajes) choicesViajes.removeActiveItems();

            const tipoGastoInput = document.getElementById('tipo_gasto');
            const selectPUnidad = document.getElementById('selectPeriodoUnidadNew');

            if (input.value === 'Equipo') {
                document.getElementById('aplicacion-equipoNew').classList.remove('d-none');
                tipoGastoInput.value = 'unidad';
                document.getElementById('impacto').value = 'viaje';
                if (selectPUnidad) {
                    selectPUnidad.value = '';
                    selectPUnidad.required = false;
                }
            } else if (input.value === 'Viaje') {
                document.getElementById('aplicacion-viajeNew').classList.remove('d-none');
                tipoGastoInput.value = 'viaje';
                document.getElementById('impacto').value = 'viaje';
                if (selectPUnidad) {
                    selectPUnidad.value = '';
                    selectPUnidad.required = false;
                }
            } else {
                tipoGastoInput.value = 'periodo';
                document.getElementById('impacto').value = 'periodo';
                if (window.requiereUnidadGasto) {
                    const pDiv = document.getElementById('aplicacion-periodoUnidadNew');
                    if (pDiv) pDiv.classList.remove('d-none');
                    if (selectPUnidad) selectPUnidad.required = true;
                }
            }
        }

        document.getElementById('tipoPagoNew').addEventListener('change', function() {
            const seccionDiferido = document.getElementById('seccionDiferidoNew');
            const divCuentaRetiro = document.getElementById('divCuentaRetiroNew');
            const selectBanco = document.getElementById('id_banco1New');
            const metodoImputacionInput = document.getElementById('metodo_imputacion');

            if (this.value === '1') {
                const bsCollapse = new bootstrap.Collapse(seccionDiferido, {
                    show: true
                });
                metodoImputacionInput.value = 'diferido';
                divCuentaRetiro.style.display = 'none';
                selectBanco.required = false;
                selectBanco.value = '';
            } else {
                const bsCollapse = bootstrap.Collapse.getInstance(seccionDiferido);
                if (bsCollapse) bsCollapse.hide();
                metodoImputacionInput.value = 'directo';
                divCuentaRetiro.style.display = 'block';
                selectBanco.required = true;
            }
        });

        document.getElementById('monto_totalNew').addEventListener('input', calcDaysNew);

        function diferenciaEnMeses(fecha1, fecha2) {
            let inicio = new Date(fecha1 + "T00:00:00");
            let fin = new Date(fecha2 + "T00:00:00");
            let periodos = 1;

            if (inicio.getFullYear() === fin.getFullYear() && inicio.getMonth() === fin.getMonth()) {
                return periodos;
            }

            while (inicio.getFullYear() < fin.getFullYear() || inicio.getMonth() < fin.getMonth()) {
                periodos++;
                inicio.setMonth(inicio.getMonth() + 1);
            }
            return periodos;
        }

        function moneyFormat(val) {
            return '$ ' + Number(val || 0).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function calcDaysNew() {
            const fechaI = document.getElementById('txtDiferirFechaIniciaNew').value;
            const fechaF = document.getElementById('txtDiferirFechaTerminaNew').value;
            const labelDias = document.getElementById('labelDiasPeriodoNew');
            const labelGastoDiario = document.getElementById('labelGastoDiarioNew');
            const labelMontoGasto = document.getElementById('labelMontoGastoNew');
            const montoVal = parseFloat(document.getElementById('monto_totalNew').value) || 0;

            labelMontoGasto.textContent = moneyFormat(montoVal);

            if (fechaI && fechaF) {
                const diasContados = diferenciaEnMeses(fechaI, fechaF);
                labelDias.textContent = diasContados;

                const dailyAmount = montoVal / diasContados;
                labelGastoDiario.textContent = moneyFormat(dailyAmount);
            } else {
                labelDias.textContent = '0';
                labelGastoDiario.textContent = '$ 0.00';
            }
        }

        // Evento Submit de Crear Gasto
        modalForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formasAplicar = document.querySelector('input[name="formasAplicar"]:checked').value;
            if (formasAplicar === 'Periodo') {
                if (window.requiereUnidadGasto) {
                    const selectPeriodoUnidad = document.getElementById('selectPeriodoUnidadNew');
                    if (!selectPeriodoUnidad || !selectPeriodoUnidad.value) {
                        Swal.fire('Unidad Requerida',
                            'Debe seleccionar la Unidad / Equipo correspondiente a este gasto.', 'warning');
                        return;
                    }
                }
            } else if (formasAplicar === 'Equipo') {
                const selectUnidades = document.getElementById('selectUnidadesNew');
                if (selectUnidades.selectedOptions.length === 0) {
                    Swal.fire('Selección Requerida',
                        'Por favor, seleccione al menos una Unidad para aplicar el gasto.', 'warning');
                    return;
                }
            } else if (formasAplicar === 'Viaje') {
                const selectViajes = document.getElementById('selectViajesNew');
                if (selectViajes.selectedOptions.length === 0) {
                    Swal.fire('Selección Requerida',
                        'Por favor, seleccione al menos un Viaje para aplicar el gasto.', 'warning');
                    return;
                }
            }

            const tipoPago = document.getElementById('tipoPagoNew').value;
            if (tipoPago === '1') {
                const fechaI = document.getElementById('txtDiferirFechaIniciaNew').value;
                const fechaF = document.getElementById('txtDiferirFechaTerminaNew').value;
                if (!fechaI || !fechaF) {
                    Swal.fire('Campos Requeridos',
                        'Para pago diferido es obligatorio indicar las fechas de inicio y fin.', 'warning');
                    return;
                }
            }
            const fechaAplicacion =
                document.getElementById('fecha_gastoNew').value;

            if (
                fechaAplicacion < window.mesinicio ||
                fechaAplicacion > window.mesfin
            ) {
                Swal.fire(
                    'Fecha inválida',
                    `La fecha de aplicación (${fechaAplicacion}) debe estar dentro del periodo seleccionado (${window.mesinicio} al ${window.mesfin}).`,
                    'warning'
                );

                return;
            }

            Swal.fire({
                title: 'Procesando...',
                text: 'Registrando el gasto, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData(modalForm);
            const gastoId = document.getElementById('gastoIdNew').value;
            const isEdit = !!gastoId;
            const urlSubmit = isEdit ? `/gastos/${gastoId}` : gastosRoutes.store;

            // For Laravel PUT requests with FormData, we can append _method = PUT
            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            try {
                const response = await fetch(urlSubmit, {
                    method: 'POST', // Use POST method to allow file/FormData submission + method spoofing
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': formData.get('_token')
                    },
                    body: formData
                });
                const json = await response.json();

                if (json.TMensaje === 'success') {
                    clearGastoDraft();
                    Swal.fire({
                        title: json.Titulo || 'Éxito',
                        text: json.Mensaje || 'El gasto se guardó correctamente.',
                        icon: 'success'
                    }).then(() => {
                        modalForm.reset();

                        const seccionDiferido = document.getElementById('seccionDiferidoNew');
                        const bsCollapse = bootstrap.Collapse.getInstance(seccionDiferido);
                        if (bsCollapse) bsCollapse.hide();

                        document.getElementById('divCuentaRetiroNew').style.display = 'block';
                        document.getElementById('id_banco1New').required = true;

                        if (choicesUnidades) choicesUnidades.removeActiveItems();
                        if (choicesViajes) choicesViajes.removeActiveItems();

                        document.querySelectorAll('.custom-option').forEach(opt => {
                            opt.classList.remove('selected');
                        });
                        document.querySelector('input[name="formasAplicar"][value="Periodo"]')
                            .parentElement.classList.add('selected');

                        document.querySelectorAll('.aplicacion-gastos-new').forEach(div => {
                            div.classList.add('d-none');
                        });

                        const pUnidad = document.getElementById('selectPeriodoUnidadNew');
                        if (pUnidad) {
                            pUnidad.value = '';
                        }
                        const pDiv = document.getElementById('aplicacion-periodoUnidadNew');
                        if (pDiv && window.requiereUnidadGasto) {
                            pDiv.classList.remove('d-none');
                        }

                        document.getElementById('tipo_gasto').value = 'periodo';
                        document.getElementById('metodo_imputacion').value = 'directo';

                        bootstrap.Modal.getInstance(document.getElementById('modalGastoNew')).hide();
                        cargarGastosNew();
                    });
                } else {
                    Swal.fire(json.Titulo || 'Error', json.Mensaje || 'No se pudo guardar el gasto.', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Ocurrió un error al enviar el formulario.', 'error');
            }
        });

        // Lógica de Edición (abrir modal con datos cargados)
        function abrirModalEditar(gasto) {
            // Reset modal first
            modalForm.reset();
            clearGastoDraft();
            if (choicesUnidades) choicesUnidades.removeActiveItems();
            if (choicesViajes) choicesViajes.removeActiveItems();

            document.getElementById('gastoIdNew').value = gasto.id;
            document.getElementById('conceptoNew').value = gasto.concepto;
            document.getElementById('monto_totalNew').value = gasto.monto_total;
            document.getElementById('categoria_gasto_idNew').value = gasto.categoria_gasto_id || '';
            cargarConceptosPorCategoria(gasto.categoria_gasto_id, gasto.gasto_concepto_id);
            document.getElementById('fecha_gastoNew').value = gasto.fecha_gasto;

            // Determinar tipo de imputación y configurar radios
            let formasAplicarVal = 'Periodo';
            if (gasto.tipo_gasto === 'unidad') {
                formasAplicarVal = 'Equipo';
            } else if (gasto.tipo_gasto === 'viaje') {
                formasAplicarVal = 'Viaje';
            }

            const inputRadio = document.querySelector(`input[name="formasAplicar"][value="${formasAplicarVal}"]`);
            if (inputRadio) {
                inputRadio.checked = true;
                handleSelectionNew(inputRadio);
            }

            // Populate selected units or trips
            const vinculos = gasto.vinculos || [];
            if (formasAplicarVal === 'Periodo') {
                const unidadPeriodoVal = gasto.id_equipo || (gasto.unidades_ids && gasto.unidades_ids.length > 0 ? gasto.unidades_ids[0] : '');
                const selectP = document.getElementById('selectPeriodoUnidadNew');
                if (selectP && unidadPeriodoVal) {
                    selectP.value = String(unidadPeriodoVal);
                }
                const pDiv = document.getElementById('aplicacion-periodoUnidadNew');
                if (pDiv && (window.requiereUnidadGasto || unidadPeriodoVal)) {
                    pDiv.classList.remove('d-none');
                }
            } else if (formasAplicarVal === 'Equipo' && choicesUnidades) {
                let mappedUnidades = [];
                if (Array.isArray(gasto.unidades_ids) && gasto.unidades_ids.length > 0) {
                    mappedUnidades = gasto.unidades_ids.map(String);
                } else if (gasto.id_equipo) {
                    mappedUnidades = [String(gasto.id_equipo)];
                } else if (vinculos.length > 0) {
                    const select = document.getElementById('selectUnidadesNew');
                    mappedUnidades = vinculos.filter(v => v.tipo === 'unidad').map(v => {
                        const opt = Array.from(select.options).find(o => o.text.includes(v.detalle.replace('Unidad: ', '')));
                        return opt ? opt.value : null;
                    }).filter(val => val !== null);
                }
                choicesUnidades.setChoiceByValue(mappedUnidades);
            } else if (formasAplicarVal === 'Viaje' && choicesViajes) {
                let mappedViajes = [];
                if (Array.isArray(gasto.viajes_ids) && gasto.viajes_ids.length > 0) {
                    mappedViajes = gasto.viajes_ids.map(String);
                } else if (vinculos.length > 0) {
                    const select = document.getElementById('selectViajesNew');
                    mappedViajes = vinculos.filter(v => v.tipo === 'asignacion' || v.tipo === 'contenedor').map(v => {
                        const opt = Array.from(select.options).find(o => o.text.includes(v.detalle.replace(
                            'Contenedor: ', '').replace('Viaje (Contenedor): ', '')));
                        return opt ? opt.value : null;
                    }).filter(val => val !== null);
                }
                choicesViajes.setChoiceByValue(mappedViajes);
            }

            // Set impact select
            const imputaciones = gasto.imputaciones || [];
            if (gasto.impacto) {
                document.getElementById('impacto').value = gasto.impacto;
            } else if (imputaciones.length > 0) {
                document.getElementById('impacto').value = imputaciones[0].tipo_imputacion || 'periodo';
            }

            // Condition/tipo de pago
            const metodoPagoVal = gasto.metodo_imputacion === 'diferido' ? '1' : '0';
            document.getElementById('tipoPagoNew').value = metodoPagoVal;
            document.getElementById('tipoPagoNew').dispatchEvent(new Event('change'));

            if (metodoPagoVal === '1') {
                if (gasto.txtDiferirFechaInicia) {
                    document.getElementById('txtDiferirFechaIniciaNew').value = gasto.txtDiferirFechaInicia;
                }
                if (gasto.txtDiferirFechaTermina) {
                    document.getElementById('txtDiferirFechaTerminaNew').value = gasto.txtDiferirFechaTermina;
                }
                calcDaysNew();
            }

            // Populate Bank Account if it is Contado and has payments
            const pagos = gasto.pagos || [];
            if (metodoPagoVal === '0' && pagos.length > 0 && pagos[0].cuenta_bancaria) {
                document.getElementById('id_banco1New').value = pagos[0].cuenta_bancaria.id;
            } else {
                document.getElementById('id_banco1New').value = '';
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('modalGastoNew'));
            modal.show();
        }

        // ==========================================
        // MOTOR DE BORRADOR (DRAFT AUTOSAVE) - SDD 002
        // ==========================================
        let saveDraftTimeout = null;

        function getFormDataAsDraft() {
            if (!modalForm) return null;
            const gastoId = document.getElementById('gastoIdNew')?.value;
            if (gastoId) return null; // No guardar borrador si se está editando un gasto existente

            const checkedForma = document.querySelector('input[name="formasAplicar"]:checked')?.value || 'Periodo';
            const unidades = choicesUnidades ? choicesUnidades.getValue(true) : [];
            const viajes = choicesViajes ? choicesViajes.getValue(true) : [];

            return {
                formasAplicar: checkedForma,
                id_equipo: document.getElementById('selectPeriodoUnidadNew')?.value || '',
                tipo_gasto: document.getElementById('tipo_gasto')?.value || 'periodo',
                metodo_imputacion: document.getElementById('metodo_imputacion')?.value || 'directo',
                unidades: Array.isArray(unidades) ? unidades : (unidades ? [unidades] : []),
                viajes: Array.isArray(viajes) ? viajes : (viajes ? [viajes] : []),
                impacto: document.getElementById('impacto')?.value || '',
                categoria_gasto_id: document.getElementById('categoria_gasto_idNew')?.value || '',
                gasto_concepto_id: document.getElementById('gasto_concepto_idNew')?.value || '',
                concepto: document.getElementById('conceptoNew')?.value || '',
                monto_total: document.getElementById('monto_totalNew')?.value || '',
                fecha_gasto: document.getElementById('fecha_gastoNew')?.value || '',
                tipoPago: document.getElementById('tipoPagoNew')?.value || '0',
                txtDiferirFechaInicia: document.getElementById('txtDiferirFechaIniciaNew')?.value || '',
                txtDiferirFechaTermina: document.getElementById('txtDiferirFechaTerminaNew')?.value || '',
                id_banco1: document.getElementById('id_banco1New')?.value || '',
                updated_at: Date.now()
            };
        }

        function saveGastoDraft() {
            try {
                const draft = getFormDataAsDraft();
                if (!draft) return;
                const hasData = draft.concepto || draft.monto_total || draft.categoria_gasto_id || 
                                (draft.unidades && draft.unidades.length) || (draft.viajes && draft.viajes.length) ||
                                (draft.formasAplicar !== 'Periodo') || draft.id_banco1 || draft.id_equipo;
                if (hasData) {
                    sessionStorage.setItem(GASTO_DRAFT_KEY, JSON.stringify(draft));
                }
            } catch (e) {
                console.warn('Error al guardar borrador en sessionStorage:', e);
            }
        }

        function debouncedSaveDraft() {
            clearTimeout(saveDraftTimeout);
            saveDraftTimeout = setTimeout(saveGastoDraft, 300);
        }

        function clearGastoDraft() {
            try {
                sessionStorage.removeItem(GASTO_DRAFT_KEY);
            } catch (e) {}
            const alertDraft = document.getElementById('alertDraftRestaurado');
            if (alertDraft) alertDraft.classList.add('d-none');
        }

        function restoreGastoDraft() {
            try {
                const raw = sessionStorage.getItem(GASTO_DRAFT_KEY);
                if (!raw) return false;
                const draft = JSON.parse(raw);
                if (!draft) return false;

                // 1. Radios formasAplicar
                const formaVal = draft.formasAplicar || 'Periodo';
                const radio = document.querySelector(`input[name="formasAplicar"][value="${formaVal}"]`);
                if (radio) {
                    radio.checked = true;
                    handleSelectionNew(radio);
                }

                // 1.1 Si es Periodo y tiene id_equipo
                if (draft.id_equipo && document.getElementById('selectPeriodoUnidadNew')) {
                    document.getElementById('selectPeriodoUnidadNew').value = draft.id_equipo;
                }

                // 2. Choices.js unidades / viajes
                if (formaVal === 'Equipo' && choicesUnidades && Array.isArray(draft.unidades) && draft.unidades.length) {
                    choicesUnidades.setChoiceByValue(draft.unidades.map(String));
                } else if (formaVal === 'Viaje' && choicesViajes && Array.isArray(draft.viajes) && draft.viajes.length) {
                    choicesViajes.setChoiceByValue(draft.viajes.map(String));
                }

                // 3. Impacto
                if (draft.impacto) {
                    document.getElementById('impacto').value = draft.impacto;
                }

                // 4. Categoría y concepto
                if (draft.categoria_gasto_id) {
                    document.getElementById('categoria_gasto_idNew').value = draft.categoria_gasto_id;
                    cargarConceptosPorCategoria(draft.categoria_gasto_id, draft.gasto_concepto_id);
                }

                // 5. Concepto, monto, fecha
                if (draft.concepto) document.getElementById('conceptoNew').value = draft.concepto;
                if (draft.monto_total) document.getElementById('monto_totalNew').value = draft.monto_total;
                if (draft.fecha_gasto) document.getElementById('fecha_gastoNew').value = draft.fecha_gasto;

                // 6. Condición de pago
                if (draft.tipoPago !== undefined) {
                    document.getElementById('tipoPagoNew').value = draft.tipoPago;
                    document.getElementById('tipoPagoNew').dispatchEvent(new Event('change'));
                }

                // 7. Diferido
                if (draft.tipoPago === '1') {
                    if (draft.txtDiferirFechaInicia) document.getElementById('txtDiferirFechaIniciaNew').value = draft.txtDiferirFechaInicia;
                    if (draft.txtDiferirFechaTermina) document.getElementById('txtDiferirFechaTerminaNew').value = draft.txtDiferirFechaTermina;
                    calcDaysNew();
                }

                // 8. Cuenta bancaria
                if (draft.id_banco1) {
                    document.getElementById('id_banco1New').value = draft.id_banco1;
                }

                // Mostrar alerta de borrador restaurado
                const alertDraft = document.getElementById('alertDraftRestaurado');
                if (alertDraft) alertDraft.classList.remove('d-none');

                return true;
            } catch (e) {
                console.error('Error al restaurar borrador de gasto:', e);
                return false;
            }
        }

        // ==========================================
        // OPERACIONES BANCARIAS IN-SITU - SDD 002
        // ==========================================
        async function actualizarSelectBancos(cuentaSeleccionadaId = null) {
            const icon = document.getElementById('iconRefreshBancos');
            if (icon) icon.classList.add('fa-spin');

            try {
                const fechaGasto = document.getElementById('fecha_gastoNew')?.value || '';
                const response = await fetch(`${gastosRoutes.cuentasBancarias}?fecha=${encodeURIComponent(fechaGasto)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (data.TMensaje === 'success' && Array.isArray(data.cuentas)) {
                    const selectGasto = document.getElementById('id_banco1New');
                    const valorActual = cuentaSeleccionadaId || selectGasto.value;

                    let htmlOptions = '<option value="">-- Seleccionar Cuenta --</option>';
                    data.cuentas.forEach(c => {
                        htmlOptions += `<option value="${c.id}">${c.display}</option>`;
                    });

                    selectGasto.innerHTML = htmlOptions;
                    if (valorActual) {
                        selectGasto.value = valorActual;
                    }

                    // Actualizar también selects de modales
                    document.querySelectorAll('.select-cuentas-bancarias').forEach(sel => {
                        if (sel.id !== 'id_banco1New') {
                            const selVal = sel.value;
                            let opts = `<option value="">-- Seleccionar Cuenta --</option>`;
                            data.cuentas.forEach(c => {
                                opts += `<option value="${c.id}">${c.display}</option>`;
                            });
                            sel.innerHTML = opts;
                            if (selVal) sel.value = selVal;
                        }
                    });

                    saveGastoDraft();
                }
            } catch (err) {
                console.error('Error al actualizar cuentas bancarias:', err);
            } finally {
                if (icon) icon.classList.remove('fa-spin');
            }
        }

        // Botón abrir sub-modal de Movimiento
        document.getElementById('btnOpenMovimientoInGasto')?.addEventListener('click', () => {
            saveGastoDraft();
            const modalGastoEl = document.getElementById('modalGastoNew');
            const bsGasto = bootstrap.Modal.getInstance(modalGastoEl);
            if (bsGasto) bsGasto.hide();

            const selectedCuentaId = document.getElementById('id_banco1New')?.value;
            if (selectedCuentaId) {
                const movCuenta = document.getElementById('movCuentaBancariaId');
                if (movCuenta) movCuenta.value = selectedCuentaId;
            }

            const modalMovEl = document.getElementById('modalMovimientoInGasto');
            const bsMov = new bootstrap.Modal(modalMovEl);
            bsMov.show();
        });

        // Botón abrir sub-modal de Transferencia
        document.getElementById('btnOpenTransferenciaInGasto')?.addEventListener('click', () => {
            saveGastoDraft();
            const modalGastoEl = document.getElementById('modalGastoNew');
            const bsGasto = bootstrap.Modal.getInstance(modalGastoEl);
            if (bsGasto) bsGasto.hide();

            const selectedCuentaId = document.getElementById('id_banco1New')?.value;
            if (selectedCuentaId) {
                const transDestino = document.getElementById('transCuentaDestino');
                if (transDestino) transDestino.value = selectedCuentaId;
            }

            const modalTransEl = document.getElementById('modalTransferenciaInGasto');
            const bsTrans = new bootstrap.Modal(modalTransEl);
            bsTrans.show();
        });

        // Botón refresco manual de bancos
        document.getElementById('btnRefreshBancosInGasto')?.addEventListener('click', () => {
            actualizarSelectBancos();
        });

        // Al cerrar cualquiera de los dos sub-modales, reabrir modalGastoNew
        ['modalMovimientoInGasto', 'modalTransferenciaInGasto'].forEach(modalId => {
            const modalEl = document.getElementById(modalId);
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', () => {
                    const modalGastoEl = document.getElementById('modalGastoNew');
                    const bsGasto = bootstrap.Modal.getInstance(modalGastoEl) || new bootstrap.Modal(modalGastoEl);
                    bsGasto.show();
                });
            }
        });

        // Envío de Formulario de Movimiento
        document.getElementById('formMovimientoInGasto')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const btnSubmit = document.getElementById('btnSubmitMovimientoInGasto');
            btnSubmit.disabled = true;

            Swal.fire({
                title: 'Guardando movimiento...',
                text: 'Por favor espere mientras se aplica el ajuste bancario.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData(form);
                const response = await fetch(gastosRoutes.bancosMovimiento, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': formData.get('_token')
                    },
                    body: formData
                });
                const json = await response.json();

                if (json.TMensaje === 'success') {
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('modalMovimientoInGasto'))?.hide();

                    await actualizarSelectBancos(json.cuenta_id);

                    Swal.fire({
                        icon: 'success',
                        title: json.Titulo || 'Movimiento registrado',
                        text: json.Mensaje || 'El saldo fue actualizado. Ya puedes continuar con el gasto.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire(json.Titulo || 'Error', json.Mensaje || 'No se pudo registrar el movimiento.', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Ocurrió un error de red al registrar el movimiento.', 'error');
            } finally {
                btnSubmit.disabled = false;
            }
        });

        // Envío de Formulario de Transferencia
        document.getElementById('formTransferenciaInGasto')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const btnSubmit = document.getElementById('btnSubmitTransferenciaInGasto');
            btnSubmit.disabled = true;

            const cOrigen = document.getElementById('transCuentaOrigen')?.value;
            const cDestino = document.getElementById('transCuentaDestino')?.value;
            if (cOrigen && cDestino && cOrigen === cDestino) {
                Swal.fire('Cuentas iguales', 'La cuenta de origen y la de destino deben ser diferentes.', 'warning');
                btnSubmit.disabled = false;
                return;
            }

            Swal.fire({
                title: 'Aplicando transferencia...',
                text: 'Por favor espere mientras se transfiere el saldo.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData(form);
                const response = await fetch(gastosRoutes.bancosTransferencia, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': formData.get('_token')
                    },
                    body: formData
                });
                const json = await response.json();

                if (json.TMensaje === 'success') {
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('modalTransferenciaInGasto'))?.hide();

                    await actualizarSelectBancos(json.cuenta_destino_id);

                    Swal.fire({
                        icon: 'success',
                        title: json.Titulo || 'Transferencia exitosa',
                        text: json.Mensaje || 'La transferencia se aplicó correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire(json.Titulo || 'Error', json.Mensaje || 'No se pudo aplicar la transferencia.', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Ocurrió un error de red al procesar la transferencia.', 'error');
            } finally {
                btnSubmit.disabled = false;
            }
        });

        // Descartar borrador manualmente
        document.getElementById('btnDescartarDraft')?.addEventListener('click', () => {
            clearGastoDraft();
            modalForm.reset();
            const pUnidad = document.getElementById('selectPeriodoUnidadNew');
            if (pUnidad) {
                pUnidad.value = '';
            }
            if (choicesUnidades) choicesUnidades.removeActiveItems();
            if (choicesViajes) choicesViajes.removeActiveItems();
            const radioPeriodo = document.querySelector('input[name="formasAplicar"][value="Periodo"]');
            if (radioPeriodo) {
                radioPeriodo.checked = true;
                handleSelectionNew(radioPeriodo);
            }
            document.getElementById('tipo_gasto').value = 'periodo';
            document.getElementById('metodo_imputacion').value = 'directo';
            document.getElementById('tipoPagoNew').value = '0';
            document.getElementById('tipoPagoNew').dispatchEvent(new Event('change'));
            document.getElementById('gasto_concepto_idNew').innerHTML = '<option value="">-- Seleccionar Concepto --</option>';
        });

        // Al hacer clic en botón principal "Registrar gasto"
        document.querySelector('[data-bs-target="#modalGastoNew"]')?.addEventListener('click', () => {
            document.getElementById('gastoIdNew').value = '';
            const restored = restoreGastoDraft();
            if (!restored) {
                modalForm.reset();
                if (choicesUnidades) choicesUnidades.removeActiveItems();
                if (choicesViajes) choicesViajes.removeActiveItems();
                const radioPeriodo = document.querySelector('input[name="formasAplicar"][value="Periodo"]');
                if (radioPeriodo) {
                    radioPeriodo.checked = true;
                    handleSelectionNew(radioPeriodo);
                }
                document.getElementById('tipo_gasto').value = 'periodo';
                document.getElementById('metodo_imputacion').value = 'directo';
                document.getElementById('tipoPagoNew').value = '0';
                document.getElementById('tipoPagoNew').dispatchEvent(new Event('change'));
                document.getElementById('gasto_concepto_idNew').innerHTML =
                    '<option value="">-- Seleccionar Concepto --</option>';
                const alertDraft = document.getElementById('alertDraftRestaurado');
                if (alertDraft) alertDraft.classList.add('d-none');
            }
        });

        // Asegurar que al abrirse el modal en modo creación siempre se lance la selección correcta
        document.getElementById('modalGastoNew')?.addEventListener('show.bs.modal', function() {
            const gastoId = document.getElementById('gastoIdNew')?.value;
            if (!gastoId) {
                const radioChecked = document.querySelector('input[name="formasAplicar"]:checked') ||
                                     document.querySelector('input[name="formasAplicar"][value="Periodo"]');
                if (radioChecked) {
                    radioChecked.checked = true;
                    handleSelectionNew(radioChecked);
                }
                if (window.requiereUnidadGasto && (!radioChecked || radioChecked.value === 'Periodo')) {
                    const pDiv = document.getElementById('aplicacion-periodoUnidadNew');
                    if (pDiv) pDiv.classList.remove('d-none');
                }
            }
        });

        // Carga dinámica de conceptos por categoría
        function cargarConceptosPorCategoria(categoriaId, conceptoSeleccionadoId = null) {
            const selectConcepto = document.getElementById('gasto_concepto_idNew');
            selectConcepto.innerHTML = '<option value="">-- Cargando Conceptos --</option>';

            if (!categoriaId) {
                selectConcepto.innerHTML = '<option value="">-- Seleccionar Concepto --</option>';
                return;
            }

            fetch(`/gastos/categorias/${categoriaId}/conceptos`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">-- Seleccionar Concepto --</option>';
                    data.forEach(item => {
                        html +=
                            `<option value="${item.id}" ${item.id == conceptoSeleccionadoId ? 'selected' : ''}>${item.nombre}</option>`;
                    });
                    selectConcepto.innerHTML = html;
                })
                .catch(() => {
                    selectConcepto.innerHTML = '<option value="">-- Error al cargar conceptos --</option>';
                });
        }

        document.getElementById('categoria_gasto_idNew').addEventListener('change', function() {
            cargarConceptosPorCategoria(this.value);
        });

        document.getElementById('gasto_concepto_idNew').addEventListener('change', function() {
            const selectedText = this.options[this.selectedIndex]?.text;
            if (this.value) {
                document.getElementById('conceptoNew').value = selectedText;
            }
        });

        // Lógica de Pago Individual
        function abrirModalPago(gasto) {
            document.getElementById('pagoGastoId').value = gasto.id;
            document.getElementById('pagoGastoConcepto').textContent = gasto.concepto;
            document.getElementById('pagoGastoSaldo').textContent = currencyFormatter(gasto.saldo_pendiente);
            document.getElementById('pagoMonto').value = gasto.saldo_pendiente;
            document.getElementById('pagoMonto').max = gasto.saldo_pendiente;

            const modal = new bootstrap.Modal(document.getElementById('modalPagarGasto'));
            modal.show();
        }

        const formPagar = document.getElementById('formPagarGasto');
        formPagar.addEventListener('submit', async (event) => {
            event.preventDefault();

            const gastoId = document.getElementById('pagoGastoId').value;
            const formData = new FormData(formPagar);

            Swal.fire({
                title: 'Procesando...',
                text: 'Registrando el pago en bancos y gastos, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const urlPay = `/gastos/${gastoId}/pagar`;

                const response = await fetch(urlPay, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': formData.get('_token')
                    },
                    body: formData
                });
                const json = await response.json();

                if (json.TMensaje === 'success') {
                    Swal.fire('Éxito', json.Mensaje || 'El pago se registró correctamente.', 'success').then(
                        () => {
                            formPagar.reset();
                            bootstrap.Modal.getInstance(document.getElementById('modalPagarGasto')).hide();
                            cargarGastosNew();
                        });
                } else {
                    Swal.fire(json.Titulo || 'Error', json.Mensaje || 'No se pudo aplicar el pago.', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Ocurrió un error al procesar el pago.', 'error');
            }
        });

        async function abrirHistorialPagos(gastoId) {

            try {

                const response = await fetch(
                    `${gastosRoutes.historial}/${gastoId}/historial-pagos`
                );

                const json = await response.json();

                if (json.TMensaje !== 'success') {

                    Swal.fire(
                        'Error',
                        json.Mensaje,
                        'error'
                    );

                    return;
                }

                renderHistorialPagos(json.pagos);

                new bootstrap.Modal(
                    document.getElementById('modalHistorialPagos')
                ).show();

            } catch (e) {

                console.error(e);

                Swal.fire(
                    'Error',
                    'No fue posible cargar el historial',
                    'error'
                );
            }
        }

        function renderHistorialPagos(pagos) {

            let html = `
        <table class="table table-sm table-bordered">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Referencia</th>
                    <th>Estatus</th>
                    <th></th>
                </tr>

            </thead>

            <tbody>
    `;

            pagos.forEach(p => {

                html += `
            <tr>

                <td>${p.id}</td>

                <td>${p.fecha_pago}</td>

                <td>${currencyFormatter(p.monto)}</td>

                <td>${p.referencia ?? ''}</td>

                <td>

                    ${
                        p.estatus === 'cancelado'
                        ?
                        '<span class="badge bg-secondary">Cancelado</span>'
                        :
                        '<span class="badge bg-success">Aplicado</span>'
                    }

                </td>

                <td>
        `;

                if (p.estatus !== 'cancelado') {

                    html += `
                <button
                    class="btn btn-sm btn-danger"
                    onclick="cancelarPagoHistorial(${p.id}, '${p.fecha_pago}')">

                    Cancelar

                </button>
            `;
                }

                html += `
                </td>

            </tr>
        `;
            });

            html += `
            </tbody>

        </table>
    `;

            document.getElementById(
                'historialPagosBody'
            ).innerHTML = html;
        }

        async function cancelarPagoDirecto(pagoId, fechaPago) {
            cancelarPagoHistorial(pagoId, fechaPago);
        }

        async function cancelarPagoHistorial(
            pagoId,
            fechaPago
        ) {

            const result = await Swal.fire({

                title: 'Cancelar pago',

                html: `
            <label class="form-label">
                Fecha cancelación
            </label>

            <input
                id="fechaCancelacion"
                type="date"
                class="swal2-input"
                value="${fechaPago}">
        `,

                showCancelButton: true,

                preConfirm: () => {

                    return document.getElementById(
                        'fechaCancelacion'
                    ).value;
                }
            });

            if (!result.isConfirmed) {
                return;
            }

            try {

                Swal.fire({
                    title: 'Cancelando...',
                    text: 'Cancelando movimiento de pago.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });


                const response = await fetch(
                    `${gastosRoutes.cancelarPago}/${pagoId}/cancelar`, {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content
                        },

                        body: JSON.stringify({
                            fecha_cancelacion: result.value
                        })
                    }
                );

                const json = await response.json();

                if (json.TMensaje === 'success') {

                    Swal.fire(
                        'Correcto',
                        json.Mensaje,
                        'success'
                    );

                    cargarGastosNew();

                    document
                        .querySelector('.modal.show')
                        ?.querySelector('.btn-close')
                        ?.click();

                    const modalHistorial = bootstrap.Modal.getInstance(
                        document.getElementById('modalHistorialPagos')
                    );

                    if (modalHistorial) {
                        modalHistorial.hide();
                    }


                } else {

                    Swal.fire(
                        'Error',
                        json.Mensaje,
                        'error'
                    );
                }

            } catch (e) {

                console.error(e);

                Swal.fire(
                    'Error',
                    'No fue posible cancelar el pago',
                    'error'
                );
            }
        }

        async function eliminarGastoNew(gastoId, concepto, defaultDate) {
            const defaultDateVal = defaultDate || moment().format('YYYY-MM-DD');
            const {
                value: fechaCancelacion
            } = await Swal.fire({
                title: '¿Eliminar gasto?',
                icon: 'warning',
                html: `
                    <p>¿Estás seguro de que deseas eliminar el gasto "${concepto}"? Se cancelarán todos los pagos y movimientos bancarios asociados, y se sincronizará la eliminación en el módulo origen.</p>
                    <div class="form-group text-left" style="margin-top: 15px;">
                        <label for="swal-fecha-cancelacion"><b>Fecha de cancelación (para histórico en bancos):</b></label>
                        <input type="date" id="swal-fecha-cancelacion" class="form-control" value="${defaultDateVal}">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const fecha = document.getElementById('swal-fecha-cancelacion').value;
                    if (!fecha) {
                        Swal.showValidationMessage('La fecha de cancelación es requerida');
                    }
                    return fecha;
                }
            });

            if (!fechaCancelacion) {
                return;
            }

            try {
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Procesando la eliminación del gasto.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const response = await fetch(`/gastos/${gastoId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        fecha_cancelacion: fechaCancelacion
                    })
                });

                const json = await response.json();

                if (json.TMensaje === 'success') {
                    Swal.fire('Eliminado', json.Mensaje, 'success');
                    cargarGastosNew();
                } else {
                    Swal.fire('Error', json.Mensaje, 'error');
                }
            } catch (e) {
                console.error(e);
                Swal.fire('Error', 'No fue posible eliminar el gasto', 'error');
            }
        }

        $(function() {
            const hoy = moment().endOf("day"); // hoy hasta 23:59
            const hace7Dias = moment().subtract(6, "days").startOf(
                "day"); // desde hace 6 días (7 en total)

            // Inicializar daterangepicker
            $("#daterange").daterangepicker({
                    startDate: hace7Dias,
                    endDate: hoy,
                    //  maxDate: hoy, //  bloquear fechas futuras
                    locale: {
                        format: "YYYY-MM-DD",
                        separator: " - ",
                        applyLabel: "Aplicar",
                        cancelLabel: "Cancelar",
                        fromLabel: "Desde",
                        toLabel: "Hasta",
                        customRangeLabel: "Personalizado",
                        weekLabel: "S",
                        daysOfWeek: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
                        monthNames: [
                            "Enero",
                            "Febrero",
                            "Marzo",
                            "Abril",
                            "Mayo",
                            "Junio",
                            "Julio",
                            "Agosto",
                            "Septiembre",
                            "Octubre",
                            "Noviembre",
                            "Diciembre",
                        ],
                        firstDay: 1,
                    },
                    ranges: {
                        Hoy: [moment(), moment()],
                        "Últimos 7 días": [moment().subtract(6, "days"), moment()],
                        "Últimos 30 días": [moment().subtract(29, "days"), moment()],
                        "Este mes": [
                            moment().startOf("month"),
                            moment().endOf("month"),
                        ],
                        "Mes anterior": [
                            moment().subtract(1, "month").startOf("month"),
                            moment().subtract(1, "month").endOf("month"),
                        ],
                    },
                },
                function(start, end) {



                    window.mesinicio = moment(start).format("YYYY-MM-DD");
                    window.mesfin = moment(end).format("YYYY-MM-DD");

                    actualizarTextoPeriodo();
                    cargarGastosNew();

                },
            );


            $("#daterange").val(
                `${hace7Dias.format("YYYY-MM-DD")} - ${hoy.format("YYYY-MM-DD")}`,
            );

            window.mesinicio = moment(hace7Dias).format("YYYY-MM-DD");
            window.mesfin = moment(hoy).format("YYYY-MM-DD");


            // Cargar datos iniciales
            cargarGastosNew();
            actualizarTextoPeriodo();

            // Sincronizar estado inicial del modal de gasto
            const initialRadio = document.querySelector('input[name="formasAplicar"]:checked') ||
                                 document.querySelector('input[name="formasAplicar"][value="Periodo"]');
            if (initialRadio) {
                handleSelectionNew(initialRadio);
            }
        });
    </script>

    <style>
        .option-group {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            max-width: 100%;
            margin-bottom: 15px;
        }

        .custom-option {
            position: relative;
            display: flex;
            align-items: center;
            border: 1px dashed #ccc;
            border-radius: 8px;
            padding: 12px 16px;
            min-height: 70px;
            flex: 1 1 200px;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s;
        }

        .custom-option input[type="radio"] {
            display: none;
        }

        .custom-option .icon {
            margin-right: 12px;
            font-size: 20px;
            color: #ccc;
            flex-shrink: 0;
            transition: color 0.2s;
        }

        .custom-option .text {
            font-size: 0.9rem;
            color: #333;
        }

        .custom-option.selected {
            background-color: #e6f4ff;
            border-color: #007bff;
        }

        .custom-option.selected .icon {
            color: #007bff;
        }

        .check-icon {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            background-color: #a5dc86;
            border-radius: 50%;
            padding: 4px;
            font-size: 11px;
            color: white;
            display: none;
        }

        .custom-option.selected .check-icon {
            display: inline-block;
        }

        /* Ajustes para AG Grid en este modulo */
        .ag-theme-quartz {
            --ag-header-background-color: #f8f9fa;
            --ag-header-foreground-color: #495057;
            --ag-border-color: #e9ecef;
            --ag-row-hover-color: #f1f3f5;
            --ag-font-size: 12px;
            --ag-grid-size: 4px;
        }
    </style>
@endsection
