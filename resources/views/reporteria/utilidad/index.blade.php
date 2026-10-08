@extends('layouts.app')

@section('template_title')
    Reporte de Resultados
@endsection

@section('content')
    <div id="miModal" class="modal">
        <div class="modal-content">
            <div class="card h-100" style="box-shadow: none !important; border: none;">
                <div class="card-header border-bottom border-1 pb-2 pt-0 px-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex flex-column">
                            <h6 class="font-weight-bold fs-18 mb-0" style="color:#333335 !important" id="tituloModalGastos">
                                <i class="fas fa-receipt text-primary me-2"></i>Detalle de Gastos
                            </h6>
                            <span class="text-xs text-muted" id="labelContenedor">Contenedor</span>
                        </div>
                        <span class="close" onclick="cerrarModal()">&times;</span>
                    </div>

                    <!-- Pestañas de Gastos (Directos vs Indirectos) -->
                    <ul class="nav nav-pills nav-fill p-1 bg-light rounded mt-3 mb-1" id="gastosModalTabs" role="tablist">
                        <li class="nav-item" id="tabItemContenedor">
                            <a class="nav-link py-1 px-3 text-xs font-weight-bold active" id="tabBtnContenedor" href="javascript:void(0)" onclick="cambiarTabGastos('contenedor')">
                                <i class="fas fa-box me-1"></i> Gastos de Viaje (<span id="tabCountContenedor">0</span>)
                            </a>
                        </li>
                        <li class="nav-item" id="tabItemIndirectos">
                            <a class="nav-link py-1 px-3 text-xs font-weight-bold" id="tabBtnIndirectos" href="javascript:void(0)" onclick="cambiarTabGastos('indirectos')">
                                <i class="fas fa-building me-1"></i> Gastos Indirectos (<span id="tabCountIndirectos">0</span>)
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body px-0 py-2">
                    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                        <span class="text-xs text-muted font-weight-bold" id="subtituloSeccionGastos">Gastos registrados</span>
                        <span class="badge bg-gradient-success font-weight-bolder text-xs px-2 py-1" id="badgeTotalGastosModal">$ 0.00</span>
                    </div>
                    <div style="max-height: 420px; overflow-y: auto; overflow-x: hidden; padding-right: 4px;">
                        <ul class="list-group" id="infoGastos"></ul>
                    </div>
                </div>

                <div class="card-footer border-top pt-2 pb-0 px-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-xs text-muted" id="resumenConteoGastos">0 gastos en total</span>
                        <button type="button" class="btn btn-sm bg-gradient-info mb-0 px-4" onclick="cerrarModal()" id="close">
                            De acuerdo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">


            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h5 id="card_title">
                                Reporte de Resultados
                                <div class="d-flex align-items-center gap-3 mt-2 flex-wrap">
                                    <div class="d-flex flex-column" style="min-width: 200px;">
                                        <label class="text-xs font-weight-bolder text-uppercase mb-1" style="color:#7b809a;">Periodo</label>
                                        <input type="text" id="daterange" readonly class="form-control form-control-sm border border-light ps-2" style="background-color: #fff; box-shadow: none;" />
                                    </div>
                                    <div class="d-flex flex-column" style="min-width: 250px;">
                                        <label class="text-xs font-weight-bolder text-uppercase mb-1" style="color:#7b809a;">Proveedor</label>
                                        <select id="selProveedorUtilidad" class="form-select form-select-sm border border-light ps-2" style="background-color: #fff; box-shadow: none;">
                                            <option value="">-- Todos los Proveedores --</option>
                                            @foreach ($proveedores as $prov)
                                                <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </h5>

                            <div class="float-right">
                                <button class="btn btn-sm bg-gradient-info text-white d-none" id="btnVistaPreliminar">
                                    <i class="fa fa-fw fa-eye"></i> Vista Preliminar
                                </button>
                                <button class="btn btn-sm btn-outline" id="btnVerDetalle">Ver Gastos</button>
                                <button type="button" class="btn btn-sm bg-gradient-danger" id="btnVerDetalle1"
                                    onclick="exportUtilidades()">
                                    <i class="fa fa-fw fa-money-bill"></i> Exportar Reporte
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 620px"></div>

                        <!-- Panel de Vista Preliminar -->
                        <div id="panelVistaPreliminar" class="mt-4 d-none" style="animation: fadeIn 0.4s;">
                            <hr class="horizontal dark my-4">
                            <h5 class="mb-3 font-weight-bold text-dark"><i class="fas fa-file-pdf me-2 text-danger"></i> Vista Preliminar del Reporte (PDF)</h5>
                            
                            <div id="iframePreviewContainer" style="width: 100%; height: 720px; background-color: #f5f5f5; border-radius: 8px;" class="shadow-sm d-flex align-items-center justify-content-center">
                                <span class="text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Generando vista previa...</span>
                            </div>
                        </div>
                    </div>  </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <style>
        /* Fondo del modal */
        .modal {
            display: none;
            /* Oculto por defecto */
            position: fixed;
            z-index: 1000000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            /* Fondo oscuro semitransparente */
        }

        /* Contenido del modal */
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 22px 26px;
            border-radius: 14px;
            width: 580px;
            max-width: 95%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            animation: fadeIn 0.3s;
        }

        /* Botón de cerrar */
        .close {
            color: #aaa;
            float: right;
            font-size: 24px;
            cursor: pointer;
            line-height: 1;
        }

        .close:hover {
            color: #000;
        }

        .bg-purple-transparent {
            background-color: rgba(137, 32, 173, 0.1) !important;
            color: rgb(137, 32, 173) !important;
            font-size: 0.75em !important;
        }

        .bg-success-transparent {
            background-color: rgba(40, 167, 69, 0.1) !important;
            color: rgb(40, 167, 69) !important;
            font-size: 0.75em !important;
        }

        /* Animación */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
    <script
        src="{{ asset('js/sgt/reporteria/rpt-utilidades.js') }}?v={{ filemtime(public_path('js/sgt/reporteria/rpt-utilidades.js')) }}">
    </script>
    <script src="{{ asset('js/reporteria/genericExcel.js') }}"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <!-- Moment.js -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <!-- JS de Date Range Picker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#daterange').daterangepicker({
                    opens: 'right',
                    showDropdowns: true,
                    linkedCalendars: false,
                    locale: {
                        format: 'YYYY-MM-DD', // Formato de fecha
                        separator: " AL ", // Separador entre la fecha inicial y final
                        applyLabel: "Aplicar",
                        cancelLabel: "Cancelar",
                        fromLabel: "Desde",
                        toLabel: "Hasta",
                        customRangeLabel: "Personalizado",
                        daysOfWeek: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
                        monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
                            "Septiembre", "Octubre", "Noviembre", "Diciembre"
                        ],
                        firstDay: 1
                    },
                    ranges: {
                        Hoy: [moment(), moment()],
                        'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                        'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                        'Este mes': [moment().startOf('month'), moment().endOf('month')],
                        'Mes anterior': [
                            moment().subtract(1, 'month').startOf('month'),
                            moment().subtract(1, 'month').endOf('month'),
                        ],
                        'Año Actual ({{ date("Y") }})': [
                            moment().startOf('year'),
                            moment().endOf('year')
                        ],
                        'Año Anterior ({{ date("Y") - 1 }})': [
                            moment().subtract(1, 'year').startOf('year'),
                            moment().subtract(1, 'year').endOf('year')
                        ],
                    },
                    // maxDate: moment()
                },
                function(start, end, label) {
                    getUtilidadesViajes(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
                    $('#daterange').attr('data-start', start.format('YYYY-MM-DD'));
                    $('#daterange').attr('data-end', end.format('YYYY-MM-DD'));


                });

            const today = new Date();
            const sevenDaysAgo = new Date();
            sevenDaysAgo.setDate(today.getDate() - 7);

            const formatDate = (date) => date.toISOString().split('T')[0];

            document.getElementById('daterange').value = `${formatDate(sevenDaysAgo)} AL ${formatDate(today)}`

            getUtilidadesViajes(formatDate(sevenDaysAgo), formatDate(today));
            $('#daterange').attr('data-start', formatDate(sevenDaysAgo));
            $('#daterange').attr('data-end', formatDate(today));
        });
    </script>

    <script>
        function mostrarModal() {
            document.getElementById('miModal').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('miModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('miModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
@endpush
