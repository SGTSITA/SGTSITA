@extends('layouts.app')

@section('template_title', 'Movimientos de cuenta')

@section('content')

    <style>
        .detalle-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .detalle-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 0.85rem;
        }

        .detalle-item .monto {
            font-weight: 600;
        }

        .detalle-total-line {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
            margin-top: 6px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .monto-total {
            color: #16a34a;
        }

        .tabla-scroll {
            max-height: 500px;
            /* Ajusta a lo que quieras */
            overflow-y: auto;
        }

        .tabla-scroll table {
            margin-bottom: 0;
        }
    </style>

    <div class="container-fluid">

        ```
        {{-- HEADER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body py-3">

                        <div class="d-flex align-items-center gap-3 flex-wrap">

                            {{-- IZQUIERDA --}}
                            <div>
                                <h2 class="mb-1">Movimientos de la cuenta en banco {{ $cuenta->catBanco->nombre }}</h2>
                                <small class="text-muted">
                                    {{ $cuenta->nombre_beneficiario }} · {{ $cuenta->moneda }}
                                </small>



                                <div class="fw-semibold">{{ $cuenta->cuenta_bancaria }} {{ $cuenta->tipo }}</div>
                            </div>

                            {{-- DERECHA --}}
                            <div class="d-flex align-items-center gap-2 flex-nowrap ms-auto">
                                <input type="text" id="searchMovimiento" class="form-control form-control-sm"
                                    style="width:180px" placeholder="Buscar movimiento...">

                                <button class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                                    data-bs-toggle="modal" data-bs-target="#modalMovimiento">
                                    <i class="fa fa-plus-circle"></i> Movimiento
                                </button>

                                <button class="btn btn-success btn-sm d-flex align-items-center gap-1"
                                    data-bs-toggle="modal" data-bs-target="#modalTransferencia">
                                    <i class="fa fa-exchange-alt"></i> Transferir
                                </button>

                                <a href="{{ route('bancos.cuentas', $cuenta->cat_banco_id) }}" class="btn btn-light btn-sm">
                                    <i class="fa fa-arrow-left me-1"></i> Volver
                                </a>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>

        {{-- RESUMEN DE CUENTA --}}
        <div class="row mb-4 g-3">

            {{-- Saldo inicial --}}
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <small class="text-muted">Saldo inicial</small>
                        <h4 class="fw-bold text-primary mb-0" id="saldoAnterior">
                            ${{ number_format($saldoAnterior ?? 0, 2) }}
                        </h4>
                        <small class="text-muted">{{ $cuenta->moneda }}</small>
                    </div>
                </div>
            </div>

            {{-- Depósitos (Abonos) --}}
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <small class="text-muted" id="conteoDepositos">Depósitos ( {{ $conteo_depositos }} )</small>
                        <h4 class="fw-bold text-success mb-0" id="totalDepositos">
                            + ${{ number_format($total_depositos ?? 0, 2) }}
                        </h4>
                        <small class="text-muted">{{ $cuenta->moneda }}</small><br />

                    </div>
                </div>
            </div>

            {{-- Pagos (Cargos) --}}
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <small class="text-muted" id="conteoCargos">Cargos ( {{ $conteo_cargos }} ) </small>
                        <h4 class="fw-bold text-danger mb-0" id="totalCargos">
                            - ${{ number_format($total_cargos ?? 0, 2) }}
                        </h4>
                        <small class="text-muted">{{ $cuenta->moneda }}</small>
                    </div>
                </div>
            </div>

            {{-- Saldo actual --}}
            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body text-center">
                        <small class="text-muted">Saldo actual</small>
                        <h4 class="fw-bold text-dark mb-0" id="saldoActual">
                            ${{ number_format($saldoActual ?? 0, 2) }}
                        </h4>
                        <small class="text-muted">{{ $cuenta->moneda }}</small>
                    </div>
                </div>
            </div>

        </div>




    </div>

    {{-- TABLA DE MOVIMIENTOS --}}
    <div class="row">
        <div class="col-12">


            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                    {{-- Título --}}
                    <h5 class="mb-0">Historial de movimientos</h5>

                    {{-- Acciones --}}
                    <div class="d-flex align-items-center gap-3 flex-wrap">

                        {{-- Periodo --}}
                        <div class="d-flex align-items-center gap-3">

                            <div class="d-flex align-items-center gap-2" style="margin-left: 20px">
                                <label class="mb-0 fw-semibold text-sm">Periodo:</label>
                                <input type="text" id="daterange" readonly class="form-control form-control-sm"
                                    style="width: auto; min-width: 200px; box-shadow: none" />
                            </div>

                        </div>


                        {{-- Exportar --}}
                        <div class="d-flex align-items-center gap-1">

                            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportar('excel')">
                                <i class="fa fa-file-excel"></i> Excel
                            </button>

                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportar('pdf')">
                                <i class="fa fa-file-pdf"></i> PDF
                            </button>

                            <form id="formExport" method="POST" action="{{ route('movimientoscuentas.export') }}"
                                target="_blank">
                                @csrf
                                <input type="hidden" name="formato" id="formatoInput">
                                <input type="hidden" name="cuenta_id" id="cuentaIdInput">
                                <input type="hidden" name="fecha_inicio" id="fechaInicioInput">
                                <input type="hidden" name="fecha_fin" id="fechaFinInput">
                            </form>

                        </div>

                    </div>

                </div>


                <div class="card-body p-0">
                    <div class="tabla-scroll">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th>Referencia</th>
                                    <th>Cargo</th>
                                    <th>Abono</th>
                                    <th class="text-end">Saldo</th>
                                    <th>Origen</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaMovimientos"></tbody>
                        </table>
                    </div>

                    {{-- @if (method_exists($movimientos, 'links'))
                        <div class="card-footer bg-white">
                            {{ $movimientos->links() }}
                        </div>
                    @endif --}}

                </div>
            </div>
        </div>
        ```

    </div>

    @include('bancos.modal-transferencia')

    @include('bancos.modal-movimiento-manual')




@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <!-- Moment.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>

    <!-- Date Range Picker JS -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        const cuentaId = {{ $cuenta->id }};

        let fechaInicioGlobal;
        let fechaFinGlobal;

        function exportar(formato) {

            document.getElementById('formatoInput').value = formato;

            document.getElementById('cuentaIdInput').value =
                cuentaId;

            document.getElementById('fechaInicioInput').value = fechaInicioGlobal;
            document.getElementById('fechaFinInput').value = fechaFinGlobal;

            document.getElementById('formExport').submit();
        }

        function formatearmoneda(valor) {
            return '$ ' + parseFloat(valor).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }


        function actualizarVista(data) {
            document.getElementById('saldoAnterior').innerText = formatearmoneda(data.saldoAnterior);


            document.getElementById('saldoActual').innerText =
                formatearmoneda(data.saldoActual);


            document.getElementById('conteoDepositos').innerText = 'Depósitos (' + data.conteo_depositos + ')';
            document.getElementById('totalDepositos').innerText =
                formatearmoneda(data.total_depositos);

            document.getElementById('conteoCargos').innerText = 'Cargos (' + data.conteo_cargos + ')';
            document.getElementById('totalCargos').innerText =
                formatearmoneda(data.total_cargos);


            let tbody = document.getElementById('tablaMovimientos');
            tbody.innerHTML = '';


            data.movimientos.forEach(mov => {
                let claseCargo = mov.tipo === 'cargo' ?
                    'text-danger fw-bold' :
                    '';
                let claseAbono = mov.tipo === 'abono' ?
                    'text-success fw-bold' :
                    '';

                let onclickDetalle = `onclick="toggleDetalle(${mov.id})" style="cursor:pointer;"`;
                tbody.innerHTML += `
                    <tr>
                        <td>${formatearFecha(mov.fecha_movimiento)}</td>
                        <td>${mov.concepto ?? ''}</td>
                        <td>${mov.referencia ?? ''}</td>
                        <td class="${claseCargo}"   ${mov.tipo === 'cargo' ? onclickDetalle : ''}>${mov.tipo === 'cargo' ? '- ' +  formatearmoneda(mov.monto) : '$ 0.00'}</td>
                        <td class="${claseAbono}"   ${mov.tipo === 'abono' ? onclickDetalle : ''}>${mov.tipo === 'abono' ? '+ ' +  formatearmoneda(mov.monto) : '$ 0.00'}</td>
                        <td>${ formatearmoneda(mov.saldo_resultante)}</td>
                        <td>${mov.origen ?? ''}</td>
                        <td></td>
                    </tr>
                    <tr id="detalle-${mov.id}" class="d-none bg-light">
    <td colspan="3">
        ${construirDetalle(mov.detalles)}
    </td>
</tr>
                `;
            });
        }

        function cargarMovimientos(inicio, fin) {

            let fechaInicio = inicio;
            let fechaFin = fin;

            fetch(
                    `/bancos/cat-bancos/cuentas/movimientosperiodo/${cuentaId}?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`
                )
                .then(response => response.json())
                .then(data => {
                    //console.log(data);
                    actualizarVista(data);
                });
        }

        function toggleDetalle(id) {


            const fila = document.getElementById(`detalle-${id}`);
            fila.classList.toggle('d-none');


        }

        function formatearLabel(key) {
            let label = key.replace(/_/g, ' ');
            label = label.replace(/([a-z])([A-Z])/g, '$1 $2');
            return label.charAt(0).toUpperCase() + label.slice(1);
        }

        function construirDetalle(detalles) {

            if (!detalles) {
                return '<div class="text-muted">Sin detalles</div>';
            }

            if (typeof detalles === 'string') {
                try {
                    detalles = JSON.parse(detalles);
                } catch (e) {
                    return '<div class="text-danger">Error en detalles</div>';
                }
            }

            if (Array.isArray(detalles) && detalles.length === 0) {
                return '<div class="text-muted">Sin detalles</div>';
            }

            let html = `
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
    `;

            // 🔥 SI ES ARRAY → TABLA PRO
            if (Array.isArray(detalles)) {

                let totalMonto = 0;

                html += `
        <div class="p-3">
            <div class="fw-semibold mb-2 text-secondary">
                Contenedores y Abonos
            </div>
            <ul class="detalle-list mb-2">
    `;

                detalles.forEach(obj => {

                    let contenedor = '';
                    let monto = 0;

                    Object.entries(obj).forEach(([key, value]) => {

                        if (key.toLowerCase().includes('contenedor')) {
                            contenedor = value;
                        }

                        if (!isNaN(value) && value !== '' && value !== null) {
                            monto = Number(value);
                            totalMonto += monto;
                        }
                    });

                    html += `
            <li class="detalle-item">
                <span>${contenedor}</span>
                <span class="monto">
                    ${formatearmoneda(monto)}
                </span>
            </li>
        `;
                });

                html += `
            </ul>

            <div class="detalle-total-line">
                <span>Total</span>
                <span class="monto-total">
                    ${formatearmoneda(totalMonto)}
                </span>
            </div>
        </div>
    `;
            }
            // 🔹 OBJETO SIMPLE
            else {
                html += `
    <div class="p-3">
        <ul class="detalle-simple-list">
`;

                Object.entries(detalles).forEach(([key, value]) => {

                    let alignMonto = '';

                    if (!isNaN(value) && value !== '' && value !== null) {
                        value = formatearmoneda(Number(value));
                        alignMonto = 'monto';
                    }

                    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}/.test(value)) {
                        value = formatearFecha(value);
                    }

                    html += `
        <li class="detalle-simple-item">
            <span class="label">
                ${formatearLabel(key)}
            </span>
            <span class="valor ${alignMonto}">
                ${value ?? '-'}
            </span>
        </li>
    `;
                });

                html += `
        </ul>
    </div>
`;
            }

            html += `
            </div>
        </div>
    `;

            return html;
        }

        document.getElementById('btnGuardarMovimiento').addEventListener('click', function() {

            const form = document.getElementById('formcrearmovimientom');
            const url = form.getAttribute('action');
            const formData = new FormData(form);


            Swal.fire({
                title: 'Guardando movimiento',
                text: 'Por favor espera...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });


            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {

                    Swal.fire({
                        icon: 'success',
                        title: 'Movimiento registrado',
                        text: data.message,
                        timer: 1800,
                        showConfirmButton: false
                    });


                    const modal = bootstrap.Modal.getInstance(
                        document.getElementById('modalMovimiento')
                    );
                    modal.hide();


                    form.reset();


                    location.reload();
                })
                .catch(error => {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message ?? 'No se pudo registrar el movimiento'
                    });
                });
        });

        document.getElementById('formTransferencia').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);

            Swal.fire({
                title: 'Aplicando transferencia...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch("{{ route('bancos.cuentas.transferencia') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {

                    if (!data.success) {
                        Swal.fire(data.Titulo ?? 'Error', data.Mensaje ??
                            'Error al intentar registrar el movimiento', 'error');
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Transferencia exitosa',
                        text: data.message,
                        timer: 1800,
                        showConfirmButton: false
                    });


                    const modal = bootstrap.Modal.getInstance(
                        document.getElementById('modalTransferencia')
                    );
                    modal.hide();


                    form.reset();

                    location.reload();

                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudo completar la transferencia', 'error');
                });
        });


        document.getElementById('searchMovimiento').addEventListener('keyup', function() {
            const filtro = this.value.toLowerCase();
            const filas = document.querySelectorAll('#tablaMovimientos tr');

            filas.forEach(fila => {
                const texto = fila.innerText.toLowerCase();
                fila.style.display = texto.includes(filtro) ? '' : 'none';
            });
        });

        function verMovimiento(id) {

            console.log('Ver movimiento', id);
        }

        function editarMovimiento(id) {

            console.log('Editar movimiento', id);
        }


        const startDate = moment().subtract(7, 'days');
        const endDate = moment();

        $('#daterange').daterangepicker({
                startDate,
                endDate,
                maxDate: moment().endOf('month'),
                opens: 'right',
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' al ',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: [
                        'Enero',
                        'Febrero',
                        'Marzo',
                        'Abril',
                        'Mayo',
                        'Junio',
                        'Julio',
                        'Agosto',
                        'Septiembre',
                        'Octubre',
                        'Noviembre',
                        'Diciembre',
                    ],
                    firstDay: 1,
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
            },
            function(start, end) {

                fechaInicioGlobal = start.format('YYYY-MM-DD HH:mm:ss');
                fechaFinGlobal = end.format('YYYY-MM-DD HH:mm:ss');

                cargarMovimientos(fechaInicioGlobal, fechaFinGlobal);
            },
        );
        fechaInicioGlobal = startDate.format('YYYY-MM-DD 00:00:00');
        fechaFinGlobal = endDate.format('YYYY-MM-DD 23:59:59');

        cargarMovimientos(fechaInicioGlobal, fechaFinGlobal);

        function formatearFecha(fecha) {

            if (!fecha) return '';

            const f = new Date(fecha);

            const dia = String(f.getDate()).padStart(2, '0');
            const mes = String(f.getMonth() + 1).padStart(2, '0');
            const anio = f.getFullYear();

            return `${dia}/${mes}/${anio}`;
        }
    </script>
@endpush
