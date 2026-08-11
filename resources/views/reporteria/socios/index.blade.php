@extends('layouts.app')

@section('template_title')
    Reportería de Socios
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-gradient-dark py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-white mb-0"><i class="fas fa-chart-bar me-2"></i>Reportería de Utilidades y Socios</h5>
                        <p class="text-xs text-white opacity-8 mb-0">Genere, visualice y exporte los reportes financieros de socios de negocios.</p>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Sección de Filtros -->
                    <div class="card shadow-none border p-3 mb-4 bg-light">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label text-xs font-weight-bold text-uppercase">Rango de Fecha</label>
                                <input type="text" id="reporteDaterange" readonly class="form-control form-control-sm bg-white cursor-pointer">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs font-weight-bold text-uppercase">Socio de Negocio</label>
                                <select class="form-select form-select-sm" id="filtro_socio_id">
                                    <option value="">Todos los Socios</option>
                                    @foreach($socios as $socio)
                                        <option value="{{ $socio->id }}">{{ $socio->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-xs font-weight-bold text-uppercase">Unidad / Camión</label>
                                <select class="form-select form-select-sm" id="filtro_equipo_id">
                                    <option value="">Todas las Unidades</option>
                                    @foreach($equipos as $eq)
                                        <option value="{{ $eq->id }}">{{ ($eq->id_equipo ? $eq->id_equipo . ' ' : '') . $eq->placas }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-xs font-weight-bold text-uppercase">Tipo de Reporte</label>
                                <select class="form-select form-select-sm font-weight-bold" id="filtro_tipo_reporte">
                                    <option value="completo">Completo (Con Viajes)</option>
                                    <option value="socio">Por Socio (Sin Viajes)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-dark mb-0 w-100" onclick="generarReporte()">
                                    <i class="fas fa-search me-1"></i> Generar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Exportación (Visibles solo tras consultar) -->
                    <div id="seccionExportar" class="d-none mb-3 d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-sm btn-success mb-0" onclick="exportarReporte('xlsx')">
                            <i class="fas fa-file-excel me-1"></i> Exportar a Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-danger mb-0" onclick="exportarReporte('pdf')">
                            <i class="fas fa-file-pdf me-1"></i> Exportar a PDF
                        </button>
                    </div>

                    <!-- Contenedor del Reporte Generado -->
                    <div id="resultadoReporte" class="d-none">
                        <!-- Cards de Resumen -->
                        <div class="row mb-4">
                            <div class="col-lg-3 col-md-6 col-12 mb-3">
                                <div class="card border shadow-xs">
                                    <div class="card-body p-3">
                                        <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Utilidad Bruta Viajes</p>
                                        <h4 class="font-weight-bolder mb-0 text-dark" id="lblBruto">$ 0.00</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-12 mb-3">
                                <div class="card border shadow-xs">
                                    <div class="card-body p-3">
                                        <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Gastos Indirectos Periodo</p>
                                        <h4 class="font-weight-bolder mb-0 text-danger" id="lblGastos">$ 0.00</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-12 mb-3">
                                <div class="card border shadow-xs">
                                    <div class="card-body p-3">
                                        <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Distribución Socios</p>
                                        <h4 class="font-weight-bolder mb-0 text-warning" id="lblDistribucion">$ 0.00</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-12 mb-3">
                                <div class="card border shadow-xs">
                                    <div class="card-body p-3">
                                        <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Utilidad Neta Empresa</p>
                                        <h4 class="font-weight-bolder mb-0 text-success" id="lblNeta">$ 0.00</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grilla Agrupada de Socios -->
                        <div class="card border shadow-none p-3 mb-4">
                            <h6 class="mb-3 text-sm font-weight-bold text-uppercase text-muted"><i class="fas fa-users me-2"></i>Desglose de Rendimiento por Socio</h6>
                            <div id="gridReporteUtilidad" class="ag-theme-alpine" style="height: 280px; width: 100%;"></div>
                        </div>

                        <!-- Grilla de Viajes (Solo visible en reporte Completo) -->
                        <div class="card border shadow-none p-3" id="cardDesgloseViajes">
                            <h6 class="mb-3 text-sm font-weight-bold text-uppercase text-muted"><i class="fas fa-truck me-2"></i>Desglose de Viajes Realizados</h6>
                            <div id="gridReporteViajes" class="ag-theme-alpine" style="height: 350px; width: 100%;"></div>
                        </div>
                    </div>

                    <div id="resultadoVacio" class="text-center py-5 text-muted">
                        <i class="fas fa-chart-line fa-3x mb-3 text-secondary"></i>
                        <h5>Generación de Reportes</h5>
                        <p class="text-sm">Seleccione los criterios de búsqueda arriba y haga click en "Generar" para ver los resultados.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_custom')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <script>
        let gridReporteUtilidadOptions, gridReporteViajesOptions;
        let gridReporteUtilidadApi, gridReporteViajesApi;

        function formatCurrency(val) {
            return `$ ${parseFloat(val || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        function formatDate(val) {
            if (!val) return 'S/N';
            return moment(val).format('DD-MM-YYYY');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const start = moment().startOf('month');
            const end = moment().endOf('month');

            $('#reporteDaterange').daterangepicker({
                startDate: start,
                endDate: end,
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' - ',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    firstDay: 1
                }
            });

            // Init Grids
            gridReporteUtilidadOptions = {
                columnDefs: [
                    { headerName: 'Socio', field: 'socio', width: 150, sortable: true, filter: true },
                    { headerName: 'Unidad Pactada', field: 'unidad', width: 140, sortable: true, filter: true },
                    { headerName: 'Regla de Pago', field: 'factor', width: 100 },
                    { headerName: 'Viajes', field: 'viajes_realizados', width: 80, sortable: true },
                    { headerName: 'Util. Bruta', field: 'utilidad_bruta', width: 110, cellRenderer: params => formatCurrency(params.value) },
                    { headerName: 'Gastos Camión', field: 'gastos_camion', width: 120, cellRenderer: params => formatCurrency(params.value) },
                    { headerName: 'Util. Neta', field: 'utilidad_neta', width: 110, cellRenderer: params => formatCurrency(params.value) },
                    { headerName: 'Acumulado (Cortes)', field: 'saldo_acumulado', width: 140, cellRenderer: params => formatCurrency(params.value) },
                    { headerName: 'Total Pagado', field: 'total_pagado', width: 120, cellRenderer: params => formatCurrency(params.value) },
                    { headerName: 'Saldo Pendiente', field: 'saldo_pendiente', width: 130, cellRenderer: params => formatCurrency(params.value) }
                ],
                rowData: []
            };
            gridReporteUtilidadApi = agGrid.createGrid(document.querySelector('#gridReporteUtilidad'), gridReporteUtilidadOptions);

            gridReporteViajesOptions = {
                columnDefs: [
                    { headerName: 'Fecha Viaje', field: 'fecha_viaje', width: 120, sortable: true, cellRenderer: params => formatDate(params.value) },
                    { headerName: 'Contenedor', field: 'contenedor', width: 170, sortable: true, filter: true },
                    { headerName: 'Cliente', field: 'cliente', width: 160, sortable: true, filter: true },
                    { headerName: 'Unidad', field: 'unidad', width: 130, sortable: true },
                    { headerName: 'Estatus', field: 'estatus_viaje', width: 110, cellRenderer: params => `<span class="badge ${params.value === 'Planeada' ? 'bg-info' : 'bg-success'}">${params.value}</span>` },
                    { headerName: 'Utilidad Viaje', field: 'utilidad_viaje', width: 130, cellRenderer: params => formatCurrency(params.value) }
                ],
                rowData: []
            };
            gridReporteViajesApi = agGrid.createGrid(document.querySelector('#gridReporteViajes'), gridReporteViajesOptions);
        });

        async function generarReporte() {
            const dates = $('#reporteDaterange').val().split(' - ');
            const from = dates[0];
            const to = dates[1];
            const socioId = document.getElementById('filtro_socio_id').value;
            const equipoId = document.getElementById('filtro_equipo_id').value;
            const tipoReporte = document.getElementById('filtro_tipo_reporte').value;

            Swal.fire({
                title: 'Generando Reporte...',
                text: 'Procesando desglose financiero de socios de negocios...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const res = await fetch(`{{ route('socios.reporte.utilidad') }}?from=${from}&to=${to}&socio_id=${socioId}&equipo_id=${equipoId}`);
                const json = await res.json();

                // Fill totals cards
                document.getElementById('lblBruto').textContent = formatCurrency(json.total_utilidad_bruta_viajes);
                document.getElementById('lblGastos').textContent = formatCurrency(json.total_gastos_periodo);
                document.getElementById('lblDistribucion').textContent = formatCurrency(json.total_distribuido_socios);
                document.getElementById('lblNeta').textContent = formatCurrency(json.utilidad_neta_empresa);

                // Hide/show company-specific cards based on report type (privacy)
                const cardBruto = document.getElementById('lblBruto').closest('.col-12');
                const cardGastos = document.getElementById('lblGastos').closest('.col-12');
                const cardNeta = document.getElementById('lblNeta').closest('.col-12');
                const cardDistribucion = document.getElementById('lblDistribucion').closest('.col-12');

                if (tipoReporte === 'socio') {
                    cardBruto.classList.add('d-none');
                    cardGastos.classList.add('d-none');
                    cardNeta.classList.add('d-none');
                    
                    cardDistribucion.className = 'col-12 mb-3';
                    cardDistribucion.querySelector('p').textContent = 'Total Asignado a Socio';
                } else {
                    cardBruto.classList.remove('d-none');
                    cardGastos.classList.remove('d-none');
                    cardNeta.classList.remove('d-none');
                    
                    cardBruto.className = 'col-lg-3 col-md-6 col-12 mb-3';
                    cardGastos.className = 'col-lg-3 col-md-6 col-12 mb-3';
                    cardNeta.className = 'col-lg-3 col-md-6 col-12 mb-3';
                    cardDistribucion.className = 'col-lg-3 col-md-6 col-12 mb-3';
                    cardDistribucion.querySelector('p').textContent = 'Distribución Socios';
                }

                // Show report container and export buttons
                document.getElementById('resultadoReporte').classList.remove('d-none');
                document.getElementById('resultadoVacio').classList.add('d-none');
                document.getElementById('seccionExportar').classList.remove('d-none');

                // Fill grids
                if (gridReporteUtilidadApi) {
                    gridReporteUtilidadApi.setGridOption('rowData', json.socios_desglose);
                }

                // Show or hide voyages grid based on report type
                const cardViajes = document.getElementById('cardDesgloseViajes');
                if (tipoReporte === 'completo') {
                    cardViajes.classList.remove('d-none');
                    if (gridReporteViajesApi) {
                        gridReporteViajesApi.setGridOption('rowData', json.viajes_desglose);
                    }
                } else {
                    cardViajes.classList.add('d-none');
                }

                Swal.close();
            } catch (e) {
                console.error(e);
                Swal.fire('Error', 'No se pudieron calcular los rendimientos.', 'error');
            }
        }

        function exportarReporte(fileType) {
            const dates = $('#reporteDaterange').val().split(' - ');
            const from = dates[0];
            const to = dates[1];
            const socioId = document.getElementById('filtro_socio_id').value;
            const equipoId = document.getElementById('filtro_equipo_id').value;
            const tipoReporte = document.getElementById('filtro_tipo_reporte').value;

            // Direct download link trigger
            window.location.href = `{{ route('socios.exportar') }}?from=${from}&to=${to}&fileType=${fileType}&socio_id=${socioId}&equipo_id=${equipoId}&tipo_reporte=${tipoReporte}`;
        }
    </script>
@endsection
