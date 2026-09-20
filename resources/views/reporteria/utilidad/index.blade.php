@extends('layouts.app')

@section('template_title')
    Reporte de Resultados
@endsection

@section('content')
    <div id="miModal" class="modal">
        <div class="modal-content">

            <div class="card h-100" style="box-shadow: none !important;">
                <div class="card-header border-bottom border-1 pb-3">
                    <div class="row">
                        <div class="col-12 d-flex justify-content-between">
                            <div class="d-flex flex-column">
                                <h6 class="font-weight-bold fs-18" style="color:#333335 !important">Detalle de gastos</h6>
                                <span class="text-xs" id="labelContenedor">Contenedor 01018373kin</span>
                            </div>
                            <span class="close" onclick="cerrarModal()">&times;</span>
                        </div>

                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group" id="infoGastos">

                    </ul>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-12">
                            <button type="button" class="btn btn-sm w-100 bg-gradient-info" onclick="cerrarModal()"
                                id="close">
                                De acuerdo
                            </button>

                        </div>
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
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.3s;
        }

        /* Botón de cerrar */
        .close {
            color: #aaa;
            float: right;
            font-size: 24px;

            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .bg-purple-transparent {
            background-color: rgba(137, 32, 173, 0.1) !important;
            color: rgb(137, 32, 173) !important;
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
