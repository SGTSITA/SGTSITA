@extends('layouts.app')

@section('template_title')
    Validación de Documentos
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0 pb-0">
                        <h5 class="mb-0 fw-bold">Reporte de Validación de Documentos</h5>
                    </div>

                    <div class="card-body">
                        <!-- Formulario de Filtros -->
                        <form method="GET" action="{{ route('reporteria.validacion-documentos.index') }}" class="row g-3 mb-4 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label text-sm fw-semibold">Periodo (Fecha de Inicio):</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="daterange" class="form-control form-control-sm cursor-pointer" readonly style="background: white;" />
                                    <input type="hidden" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}" />
                                    <input type="hidden" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}" />
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label for="id_cliente" class="form-label text-sm fw-semibold">Cliente:</label>
                                <select name="id_cliente" id="id_cliente" class="form-select form-select-sm">
                                    <option value="">Todos los clientes...</option>
                                    @foreach($clientes as $cli)
                                        <option value="{{ $cli->id }}" {{ request('id_cliente') == $cli->id ? 'selected' : '' }}>{{ $cli->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="num_contenedor" class="form-label text-sm fw-semibold">Contenedor:</label>
                                <input type="text" name="num_contenedor" id="num_contenedor" class="form-control form-control-sm" placeholder="Buscar..." value="{{ request('num_contenedor') }}" />
                            </div>

                            <div class="col-md-2 d-flex align-items-center mb-2">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input cursor-pointer" type="checkbox" id="incluirAuditoria" name="incluir_auditoria" value="1" {{ request('incluir_auditoria') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label mb-0 text-sm fw-semibold cursor-pointer" for="incluirAuditoria">Auditoría</label>
                                </div>
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm mb-0 flex-fill">
                                    <i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar
                                </button>
                                <a href="{{ route('reporteria.validacion-documentos.index') }}" class="btn btn-outline-secondary btn-sm mb-0 flex-fill">
                                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Limpiar
                                </a>
                            </div>
                        </form>

                        <hr class="my-4" />

                        <!-- Acciones Generales -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 text-sm fw-bold">Resultados ({{ $cotizaciones->count() }})</h6>
                            @if($cotizaciones->isNotEmpty())
                                <button type="button" id="btnDescargarTodo" class="btn btn-danger btn-sm mb-0 d-flex align-items-center gap-2">
                                    <i class="fas fa-file-pdf"></i> Descargar PDF Consolidado
                                </button>
                            @endif
                        </div>

                        <!-- Tabla de Resultados -->
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 5%">
                                            <input type="checkbox" id="selectAll" class="form-check-input cursor-pointer" checked />
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cliente</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Contenedor</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cotizaciones as $cot)
                                        <tr>
                                            <td class="align-middle text-center">
                                                <input type="checkbox" name="ids[]" value="{{ $cot->id }}" class="form-check-input coti-checkbox cursor-pointer" checked />
                                            </td>
                                            <td class="align-middle">
                                                <span class="text-sm text-dark font-weight-bold">{{ $cot->cliente }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <span class="text-sm font-weight-bold">{{ $cot->num_contenedor }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <button type="button" class="btn btn-outline-danger btn-xs mb-0 btn-descargar-individual" data-id="{{ $cot->id }}">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted text-sm">
                                                No se encontraron registros con los filtros seleccionados.
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
@endsection

@section('datatable')
    <!-- Moment.js y DateRangePicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(function() {
            // Inicializar DateRangePicker
            let start = $('#fecha_inicio').val() ? moment($('#fecha_inicio').val()) : null;
            let end = $('#fecha_fin').val() ? moment($('#fecha_fin').val()) : null;

            function cb(start, end) {
                if (start && end) {
                    $('#daterange').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
                    $('#fecha_inicio').val(start.format('YYYY-MM-DD'));
                    $('#fecha_fin').val(end.format('YYYY-MM-DD'));
                } else {
                    $('#daterange').val('Seleccionar periodo...');
                    $('#fecha_inicio').val('');
                    $('#fecha_fin').val('');
                }
            }

            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Limpiar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    firstDay: 1
                }
            });

            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                cb(picker.startDate, picker.endDate);
            });

            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                cb(null, null);
            });

            if (start && end) {
                cb(start, end);
            } else {
                $('#daterange').val('Seleccionar periodo...');
            }

            // Seleccionar todo
            $('#selectAll').on('change', function() {
                $('.coti-checkbox').prop('checked', this.checked);
            });

            $('.coti-checkbox').on('change', function() {
                if (!this.checked) {
                    $('#selectAll').prop('checked', false);
                } else if ($('.coti-checkbox:checked').length === $('.coti-checkbox').length) {
                    $('#selectAll').prop('checked', true);
                }
            });

            // Generar PDF consolidado
            $('#btnDescargarTodo').on('click', function() {
                let selectedIds = [];
                $('.coti-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selección vacía',
                        text: 'Por favor seleccione al menos un contenedor para exportar.'
                    });
                    return;
                }

                submitPdfForm(selectedIds);
            });

            // Generar PDF individual
            $('.btn-descargar-individual').on('click', function() {
                let id = $(this).data('id');
                submitPdfForm([id]);
            });

            function submitPdfForm(ids) {
                let form = $('<form>', {
                    action: "{{ route('reporteria.validacion-documentos.pdf') }}",
                    method: 'POST',
                    target: '_blank'
                });

                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: "{{ csrf_token() }}"
                }));

                ids.forEach(id => {
                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'ids[]',
                        value: id
                    }));
                });

                form.append($('<input>', {
                    type: 'hidden',
                    name: 'incluir_auditoria',
                    value: $('#incluirAuditoria').is(':checked') ? '1' : '0'
                }));

                $('body').append(form);
                form.submit();
                form.remove();
            }
        });
    </script>
@endsection
