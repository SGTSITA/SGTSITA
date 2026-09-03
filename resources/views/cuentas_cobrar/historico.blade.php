@extends('layouts.app')

@section('template_title')
    Historial de Cobros (CxC)
@endsection

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3 id="card_title">Historial de Cobros (CxC)</h3>
                            <div class="d-flex gap-2 align-items-center">
                                <a href="{{ route('cobros_pagos.exportar_excel', array_merge(request()->all(), ['tipo' => 'cxc'])) }}"
                                    class="btn btn-sm btn-outline-success mb-0">
                                    <i class="fas fa-file-excel"></i> Excel
                                </a>
                                <a href="{{ route('cobros_pagos.exportar_pdf', array_merge(request()->all(), ['tipo' => 'cxc'])) }}"
                                    class="btn btn-sm btn-outline-danger mb-0">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                                @if (request('cliente_id'))
                                    <a class="btn btn-sm btn-secondary mb-0"
                                        href="{{ route('show.cobrar', request('cliente_id')) }}">
                                        <img src="{{ asset('img/icon/izquierda_white.png') }}" alt=""
                                            width="18px"> Regresar
                                    </a>
                                @else
                                    <a class="btn btn-sm btn-secondary mb-0" href="{{ route('index.cobrar') }}">
                                        <img src="{{ asset('img/icon/izquierda_white.png') }}" alt=""
                                            width="18px"> Regresar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-2">
                        <!-- Filtros -->
                        <form method="GET" action="{{ route('cobros_pagos.historico') }}" class="mb-4">
                            <div class="row align-items-end">
                                <div class="col-md-3 mb-2">
                                    <label for="cliente_id" class="form-label text-sm mb-1">Cliente</label>
                                    <select name="cliente_id" id="cliente_id"
                                        class="form-select form-select-sm select2-filter" style="width: 100%;">
                                        <option value="">Todos los clientes</option>
                                        @foreach ($clientes as $cli)
                                            <option value="{{ $cli->id }}"
                                                {{ request('cliente_id') == $cli->id ? 'selected' : '' }}>
                                                {{ $cli->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label for="proveedor_id" class="form-label text-sm mb-1">Proveedor
                                        (Planeaciones)</label>
                                    <select name="proveedor_id" id="proveedor_id"
                                        class="form-select form-select-sm select2-filter" style="width: 100%;">
                                        <option value="">Todos los proveedores</option>
                                        @foreach ($proveedores as $prov)
                                            <option value="{{ $prov->id }}"
                                                {{ request('proveedor_id') == $prov->id ? 'selected' : '' }}>
                                                {{ $prov->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label for="num_contenedor" class="form-label text-sm mb-1">Num Contenedor</label>
                                    <input type="text" name="num_contenedor" id="num_contenedor"
                                        class="form-control form-control-sm" value="{{ request('num_contenedor') }}"
                                        placeholder="Ej. MAEU123456">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="form-label text-sm mb-1">Periodo</label>
                                    <input type="text" id="daterange" readonly class="form-control form-control-sm"
                                        style="background-color: #fff;">
                                    <input type="hidden" name="fecha_inicio" id="fecha_inicio"
                                        value="{{ request('fecha_inicio') }}">
                                    <input type="hidden" name="fecha_fin" id="fecha_fin"
                                        value="{{ request('fecha_fin') }}">
                                </div>

                                <div class="col-md-1 mb-2 d-flex gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary mb-0 p-2" title="Filtrar">
                                        <img src="{{ asset('img/icon/buscar.webp') }}" alt="Filtrar" width="16px">
                                    </button>
                                    <a href="{{ route('cobros_pagos.historico') }}"
                                        class="btn btn-sm btn-outline-secondary mb-0 p-2 btn-reset-filter"
                                        title="Quitar filtros" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <img src="{{ asset('img/icon/reset.webp') }}" alt="Limpiar" width="16px">
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Listado de Cobros -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm align-middle text-sm"
                                style="font-size: 0.85rem;">
                                <thead class="thead table-light">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Fecha Pago A</th>
                                        <th>Banco A</th>
                                        <th>Monto A</th>
                                        <th>Fecha Pago B</th>
                                        <th>Banco B</th>
                                        <th>Monto B</th>
                                        <th>Total Cobrado</th>
                                        <th style="width: 25%;">Observaciones</th>
                                        <th class="text-center" style="width: 10%;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pagos as $pago)
                                        @php
                                            $totalCobrado = $pago->monto_A + $pago->monto_B;
                                        @endphp
                                        <tr class="main-row" data-target="#collapsePago{{ $pago->id }}"
                                            style="cursor: pointer;">
                                            <td>{{ $pago->cliente->nombre ?? 'N/A' }}</td>
                                            <td>{{ optional($pago->fechaAplicacion1)->format('d-m-Y') ?? '-' }}</td>
                                            <td>{{ $pago->bancoA->nombre_banco ?? '-' }}</td>
                                            <td>${{ number_format($pago->monto_A, 2) }}</td>
                                            <td>{{ optional($pago->fechaAplicacion2)->format('d-m-Y') ?? '-' }}</td>
                                            <td>{{ $pago->bancoB->nombre_banco ?? '-' }}</td>
                                            <td>${{ number_format($pago->monto_B, 2) }}</td>
                                            <td><strong>${{ number_format($totalCobrado, 2) }}</strong></td>
                                            <td class="text-truncate" style="max-width: 200px;"
                                                title="{{ $pago->observaciones }}">{{ $pago->observaciones ?? '-' }}</td>
                                            <td
                                                class="text-center action-cell d-flex justify-content-center align-items-center gap-1">
                                                <a href="{{ route('cobros_pagos.comprobante_pdf', $pago->id) }}"
                                                    class="btn btn-xs btn-outline-info py-1 px-2 m-0"
                                                    title="Descargar Comprobante PDF">
                                                    PDF
                                                </a>
                                                <button type="button"
                                                    class="btn btn-xs btn-danger text-white py-1 px-2 m-0 btn-eliminar-pago"
                                                    data-url="{{ route('cobros_pagos.eliminar', $pago->id) }}"
                                                    data-fecha="{{ optional($pago->fechaAplicacion1)->format('Y-m-d') ?? date('Y-m-d') }}">
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Fila de Detalles Colapsable -->
                                        <tr class="collapse" id="collapsePago{{ $pago->id }}">
                                            <td colspan="10" class="bg-light p-3">
                                                <div class="card card-body shadow-none border m-0">
                                                    <h6 class="mb-2">Cotizaciones vinculadas al Cobro
                                                        #{{ $pago->id }}</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm m-0 text-xs"
                                                            style="font-size: 0.8rem;">
                                                            <thead>
                                                                <tr class="table-secondary">
                                                                    <th>Cotización ID</th>
                                                                    <th>Contenedor</th>
                                                                    <th>Edo Cuenta</th>
                                                                    <th>Origen</th>
                                                                    <th>Destino</th>
                                                                    <th>Monto Aplicado</th>
                                                                    <th>Total de Cotización</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($pago->detalles as $detalle)
                                                                    @if ($detalle->cotizacion && $detalle->cotizacion->jerarquia === 'Secundario' && $detalle->monto == 0)
                                                                        @continue
                                                                    @endif
                                                                    @php
                                                                        $numContenedor =
                                                                            $detalle->cotizacion->DocCotizacion
                                                                                ->num_contenedor ?? 'N/A';
                                                                        if (
                                                                            $detalle->cotizacion &&
                                                                            !is_null(
                                                                                $detalle->cotizacion->referencia_full,
                                                                            ) &&
                                                                            $detalle->cotizacion->jerarquia ===
                                                                                'Principal'
                                                                        ) {
                                                                            $secundaria = \App\Models\Cotizaciones::where(
                                                                                'referencia_full',
                                                                                $detalle->cotizacion->referencia_full,
                                                                            )
                                                                                ->where('jerarquia', 'Secundario')
                                                                                ->with('DocCotizacion')
                                                                                ->first();
                                                                            if (
                                                                                $secundaria &&
                                                                                $secundaria->DocCotizacion
                                                                            ) {
                                                                                $numContenedor .=
                                                                                    ' / ' .
                                                                                    $secundaria->DocCotizacion
                                                                                        ->num_contenedor;
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ $detalle->cotizacion_id }}</td>
                                                                        <td><strong>{{ $numContenedor }}</strong></td>
                                                                        <td>{{ $detalle->cotizacion->estadoCuenta->numero ?? 'N/A' }}
                                                                        </td>
                                                                        <td>{{ $detalle->cotizacion->origen ?? '-' }}</td>
                                                                        <td>{{ $detalle->cotizacion->destino ?? '-' }}</td>
                                                                        <td>${{ number_format($detalle->monto, 2) }}</td>
                                                                        <td>${{ number_format($detalle->cotizacion->total ?? 0, 2) }}
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="7" class="text-center">No hay
                                                                            cotizaciones vinculadas.</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center">No se encontraron registros de cobros.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-center mt-3">
                            {!! $pagos->appends(request()->all())->links('pagination::bootstrap-5') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Moment.js -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <!-- Daterangepicker JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <!-- Select2 JS -->
    <script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Inicializar Select2 en los filtros
            $('.select2-filter').select2({
                placeholder: "Seleccione una opción",
                allowClear: true
            });

            // Manejar collapse manualmente al dar click en la fila (excepto en el botón eliminar)
            $('table tbody tr.main-row').on('click', function(e) {
                if ($(e.target).closest('.action-cell, button, form, a, input').length) {
                    return;
                }
                const target = $(this).data('target');
                const collapseElement = $(target);
                if (collapseElement.hasClass('show')) {
                    collapseElement.collapse('hide');
                } else {
                    collapseElement.collapse('show');
                }
            });

            // Inicializar daterangepicker
            const initStart = $('#fecha_inicio').val() ? moment($('#fecha_inicio').val()) : moment().subtract(6,
                "days").startOf("day");
            const initEnd = $('#fecha_fin').val() ? moment($('#fecha_fin').val()) : moment().endOf("day");

            $("#daterange").daterangepicker({
                startDate: initStart,
                endDate: initEnd,
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
                        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
                    ],
                    firstDay: 1,
                },
                ranges: {
                    Hoy: [moment(), moment()],
                    "Últimos 7 días": [moment().subtract(6, "days"), moment()],
                    "Últimos 30 días": [moment().subtract(29, "days"), moment()],
                    "Este mes": [moment().startOf("month"), moment().endOf("month")],
                    "Mes anterior": [moment().subtract(1, "month").startOf("month"), moment().subtract(1,
                        "month").endOf("month")],
                },
            }, function(start, end) {
                $('#fecha_inicio').val(start.format("YYYY-MM-DD"));
                $('#fecha_fin').val(end.format("YYYY-MM-DD"));
            });

            $("#daterange").val(`${initStart.format("YYYY-MM-DD")} - ${initEnd.format("YYYY-MM-DD")}`);
            if (!$('#fecha_inicio').val()) {
                $('#fecha_inicio').val(initStart.format("YYYY-MM-DD"));
                $('#fecha_fin').val(initEnd.format("YYYY-MM-DD"));
            }

            // Activar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // SweetAlert2 confirmación y eliminación con carga y recarga
            $('.btn-eliminar-pago').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const url = $(this).data('url');
                const fechaMovimiento = $(this).data('fecha');

                Swal.fire({
                    title: '¿Está seguro?',
                    html: `
                        <p>Se restaurarán los saldos correspondientes y se cancelarán los movimientos bancarios.</p>
                        <label for="fecha_cancelacion_input" class="form-label mt-2">Seleccione la fecha de cancelación para el banco:</label>
                        <input type="date" id="fecha_cancelacion_input" class="form-control" value="${fechaMovimiento}" required>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        const dateVal = document.getElementById('fecha_cancelacion_input')
                        .value;
                        if (!dateVal) {
                            Swal.showValidationMessage(
                                '¡Debe seleccionar una fecha de cancelación!');
                        }
                        return dateVal;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const fechaCancelacion = result.value;
                        // Mostrar loading
                        Swal.fire({
                            title: 'Procesando...',
                            html: 'Por favor espere mientras se realiza la cancelación.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Solicitud AJAX
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}',
                                fecha_cancelacion: fechaCancelacion
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Cancelado!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                let errorMsg = 'No se pudo realizar la cancelación.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMsg
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
