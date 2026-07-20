@extends('layouts.app')

@section('template_title')
    Editar Planeación - Contenedor {{ $cotizacion->DocCotizacion->num_contenedor }}
@endsection

@section('content')
    <!-- Flatpickr CSS y JS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    @php
        $asignacion = $cotizacion->DocCotizacion->Asignaciones;
        $fechaInicioVal = $asignacion ? date('d/m/Y', strtotime($asignacion->fecha_inicio)) : date('d/m/Y');
        $fechaFinVal = $asignacion ? date('d/m/Y', strtotime($asignacion->fecha_fin)) : date('d/m/Y');
        $fechaAppVal =
            $dineroViaje && $dineroViaje->fecha_entrega_monto
                ? date('d/m/Y', strtotime($dineroViaje->fecha_entrega_monto))
                : '';
    @endphp

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg border-radius-xl">
                    <div class="card-header bg-gradient-info text-white pb-3 pt-3 sticky-top"
                        style="z-index: 1020; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-0 text-white font-weight-bolder">Completar / Editar Planeación</h5>
                            </div>
                            <a href="/planeaciones" class="btn btn-sm bg-white text-info font-weight-bold mb-0">
                                <i class="fa fa-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                        <div class="row align-items-center text-sm g-2">
                            <div class="col-md-2 col-6">
                                <div class="p-2 rounded text-white" style="background-color: rgba(255,255,255,0.15);">
                                    <span class="d-block text-uppercase fw-bold"
                                        style="font-size: 0.65rem; opacity: 0.85;">Contenedor</span>
                                    <strong class="fs-6">{{ $cotizacion->DocCotizacion->num_contenedor }}</strong>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <div class="p-2 rounded text-white" style="background-color: rgba(255,255,255,0.15);">
                                    <span class="d-block text-uppercase fw-bold"
                                        style="font-size: 0.65rem; opacity: 0.85;">Cliente</span>
                                    <strong class="fs-6 text-truncate d-block"
                                        title="{{ $cotizacion->Cliente->nombre }}">{{ $cotizacion->Cliente->nombre }}</strong>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-2 rounded text-white" style="background-color: rgba(255,255,255,0.15);">
                                    <span class="d-block text-uppercase fw-bold"
                                        style="font-size: 0.65rem; opacity: 0.85;">Peso / Precio Viaje</span>
                                    <strong class="fs-6">{{ $cotizacion->peso_contenedor ?? '--' }} /
                                        {{ $cotizacion->precio_viaje ? '$' . number_format($cotizacion->precio_viaje, 2) : '--' }}</strong>
                                </div>
                            </div>

                            <div class="col-md-5 col-12">
                                <div class="p-2 rounded text-white" style="background-color: rgba(255,255,255,0.15);">
                                    <span class="d-block text-uppercase fw-bold"
                                        style="font-size: 0.65rem; opacity: 0.85;"><i
                                            class="fa fa-map-marker-alt text-warning me-1"></i> Dirección de Entrega</span>
                                    <strong style="font-size: 0.85rem; font-weight: 700;" class="text-truncate d-block"
                                        title="{{ $cotizacion->direccion_entrega ?? '--' }}">{{ $cotizacion->direccion_entrega ?? '--' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('viajes.update', $cotizacion->id) }}"
                        id="formularioEditPlaneacion" enctype="multipart/form-data" role="form">
                        @csrf
                        <div class="card-body p-4">



                            <!-- FECHAS Y ASIGNACIÓN DE EQUIPO -->

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="form-label font-weight-bold" for="txtFechaInicio">Fecha Salida *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-calendar text-info"></i></span>
                                        <input name="txtFechaInicio" id="txtFechaInicio" type="text"
                                            class="form-control datepicker-input" value="{{ $fechaInicioVal }}"
                                            placeholder="dd/mm/aaaa" required>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label font-weight-bold" for="txtFechaFinal">Fecha Entrega *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-calendar text-info"></i></span>
                                        <input name="txtFechaFinal" id="txtFechaFinal" type="text"
                                            class="form-control datepicker-input" value="{{ $fechaFinVal }}"
                                            placeholder="dd/mm/aaaa" required>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label class="form-label font-weight-bold" for="cmbCamion">Unidad *</label>
                                    <select class="form-select" name="cmbCamion" id="cmbCamion" required>
                                        <option value="">Seleccione Unidad</option>
                                        @foreach ($equipos as $item)
                                            @if ($item->tipo == 'Tractos / Camiones')
                                                <option value="{{ $item->id }}"
                                                    {{ $asignacion && $asignacion->id_camion == $item->id ? 'selected' : '' }}>
                                                    {{ $item->id_equipo }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label class="form-label font-weight-bold" for="cmbChasis">Chasis *</label>
                                    <select class="form-select" name="cmbChasis" id="cmbChasis" required>
                                        <option value="">Seleccione Chasis</option>
                                        @foreach ($equipos as $item)
                                            @if ($item->tipo == 'Chasis / Plataforma')
                                                <option value="{{ $item->id }}"
                                                    {{ $asignacion && $asignacion->id_chasis == $item->id ? 'selected' : '' }}>
                                                    {{ $item->id_equipo }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label class="form-label font-weight-bold" for="cmbChasis2">Chasis 2</label>
                                    <select class="form-select" name="cmbChasis2" id="cmbChasis2">
                                        <option value="">Seleccionar</option>
                                        @foreach ($equipos as $item)
                                            @if ($item->tipo == 'Chasis / Plataforma')
                                                <option value="{{ $item->id }}"
                                                    {{ $asignacion && $asignacion->id_chasis2 == $item->id ? 'selected' : '' }}>
                                                    {{ $item->id_equipo }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label class="form-label font-weight-bold" for="cmbDoly">Doly / Acoplador</label>
                                    <select class="form-select" name="cmbDoly" id="cmbDoly">
                                        <option value="">Seleccionar</option>
                                        @foreach ($equipos as $item)
                                            @if ($item->tipo == 'Chasis / Plataforma')
                                                <option value="{{ $item->id }}"
                                                    {{ $asignacion && $asignacion->id_dolys == $item->id ? 'selected' : '' }}>
                                                    {{ $item->id_equipo }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>


                            </div>

                            <hr class="horizontal dark my-4">

                            <!-- INFORMACIÓN DE PAGO / SUELDO Y DIESEL -->

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="form-label font-weight-bold" for="cmbOperador">Operador *</label>
                                    <select class="form-select" name="cmbOperador" id="cmbOperador" required>
                                        <option value="">Seleccione operador</option>
                                        @foreach ($operadores as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $asignacion && $asignacion->id_operador == $item->id ? 'selected' : '' }}>
                                                {{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label class="form-label font-weight-bold" for="txtSueldoOperador">Sueldo Operador
                                        *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-gradient-success text-white"><i
                                                class="ni ni-money-coins text-sm"></i></span>
                                        <input class="form-control" name="txtSueldoOperador" id="txtSueldoOperador"
                                            type="text"
                                            value="{{ $asignacion ? number_format($asignacion->sueldo_viaje, 2) : '0.00' }}"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label class="form-label font-weight-bold" for="txtDineroViaje">Dinero viaje *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-gradient-success text-white"><i
                                                class="ni ni-money-coins text-sm"></i></span>
                                        <input class="form-control" name="txtDineroViaje" id="txtDineroViaje"
                                            type="text"
                                            value="{{ $asignacion ? number_format($asignacion->dinero_viaje, 2) : '0.00' }}"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label class="form-label font-weight-bold" for="cmbBanco">Banco *</label>
                                    <select class="form-select" name="cmbBanco" id="cmbBanco" required>
                                        <option value="">Seleccione banco</option>
                                        @foreach ($bancos as $item)
                                            <option value="{{ $item['id'] }}"
                                                {{ $asignacion && $asignacion->id_banco1_dinero_viaje == $item['id'] ? 'selected' : '' }}>
                                                {{ $item['display'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 form-group mt-2">
                                    <label class="form-label font-weight-bold" for="FechaAplicacionDinero">Fecha
                                        Aplicación *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-calendar text-danger"></i></span>
                                        <input class="form-control" name="FechaAplicacionDinero"
                                            id="FechaAplicacionDinero" type="text" value="{{ $fechaAppVal }}"
                                            placeholder="dd/mm/aaaa" required>
                                    </div>
                                </div>

                                <div class="col-md-4 form-group mt-2">
                                    <label class="form-label font-weight-bold" for="litros_diesel">Litros Diésel</label>
                                    <input type="number" step="0.001" class="form-control text-end"
                                        id="litros_diesel" name="litros_diesel"
                                        value="{{ $cotizacion->litros_diesel ?? 0 }}">
                                </div>
                                <div class="col-md-4 form-group mt-2">
                                    <label class="form-label font-weight-bold" for="litros_urea">Litros Urea</label>
                                    <input type="number" step="0.001" class="form-control text-end"
                                        id="litros_urea" name="litros_urea"
                                        value="{{ $cotizacion->litros_urea ?? 0 }}">
                                </div>
                            </div>

                            <hr class="horizontal dark my-4">

                            <!-- GASTOS ADICIONALES -->

                            <div class="row">
                                <div class="col-12">
                                    <button type="button" class="btn btn-success btn-sm mt-2" id="btnAddGastoEdit">
                                        <i class="ni ni-fat-add"></i> Agregar gastos al viaje
                                    </button>
                                    <div id="otrosGastosContainerEdit" class="mt-2">
                                        @foreach ($gastosAsignados as $gasto)
                                            @php
                                                $pago = $gasto->pagos->first();
                                                $motivo = null;
                                                if (strpos($gasto->concepto, 'GCM01') === 0) {
                                                    $motivo = 'GCM01';
                                                } elseif (strpos($gasto->concepto, 'GDI02') === 0) {
                                                    $motivo = 'GDI02';
                                                } elseif (strpos($gasto->concepto, 'GBV01') === 0) {
                                                    $motivo = 'GBV01';
                                                }
                                                $esPagoInmediato = $gasto->estatus === 'pagado';
                                                $bancoId = $pago ? $pago->cuenta_bancaria_id : '';
                                                $fechaPago =
                                                    $pago && $pago->fecha_pago
                                                        ? date('d/m/Y', strtotime($pago->fecha_pago))
                                                        : '';
                                            @endphp
                                            <div class="row gasto-item align-items-center mb-3 border-bottom pb-3">
                                                <div class="col-md-3">
                                                    <label class="form-label mb-1">Motivo del gasto</label>
                                                    <select class="form-control gasto-select" name="gasto_nombre[]"
                                                        required>
                                                        <option value="">Seleccione un motivo</option>
                                                        <option value="GCM01"
                                                            {{ $motivo === 'GCM01' ? 'selected' : '' }}>GCM01 - Comisión
                                                        </option>
                                                        <option value="GDI02"
                                                            {{ $motivo === 'GDI02' ? 'selected' : '' }}>GDI02 - Diesel
                                                        </option>
                                                        <option value="GBV01"
                                                            {{ $motivo === 'GBV01' ? 'selected' : '' }}>GBV01 - Burrero
                                                            Vacio</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label mb-1">Monto</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-gradient-success text-white">
                                                            <i class="ni ni-money-coins"></i>
                                                        </span>
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control" name="gasto_monto[]"
                                                            value="{{ floatval($gasto->monto_total) }}"
                                                            placeholder="0.00" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-1">
                                                    <label class="form-label mb-1 d-block">Pago inmed.</label>
                                                    <div class="form-check form-switch mt-2">
                                                        <input type="checkbox" class="form-check-input pagoInmediatoCheck"
                                                            name="gasto_pago_inmediato[]"
                                                            {{ $esPagoInmediato ? 'checked' : '' }}>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 banco-col"
                                                    style="display: {{ $esPagoInmediato ? 'block' : 'none' }};">
                                                    <label class="form-label mb-1">Banco</label>
                                                    <select class="form-select" name="gasto_banco_id[]">
                                                        <option value="">Seleccione banco</option>
                                                        @foreach ($bancos as $item)
                                                            <option value="{{ $item['id'] }}"
                                                                {{ $bancoId == $item['id'] ? 'selected' : '' }}>
                                                                {{ $item['display'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-2 fecha-col"
                                                    style="display: {{ $esPagoInmediato ? 'block' : 'none' }};">
                                                    <label class="form-label mb-1">Fecha Aplicación</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="fa fa-calendar text-danger"></i></span>
                                                        <input class="form-control dateInput" name="fechaAplicacion[]"
                                                            value="{{ $fechaPago }}" placeholder="Fecha"
                                                            type="text" />
                                                    </div>
                                                </div>

                                                <div class="col-md-1 text-end mt-4">
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm removeGastoBtn mb-0">
                                                        <i class="ni ni-fat-remove"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="card-footer d-flex justify-content-end gap-2 bg-light border-top p-3">
                            <a href="/planeaciones" class="btn btn-secondary font-weight-bold mb-0">Cancelar</a>
                            <button type="submit" class="btn bg-gradient-success text-white font-weight-bold mb-0"
                                id="btnGuardarEdit">
                                <i class="fa fa-save me-1"></i> Guardar Planeación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar Flatpickr para fechas
            if (typeof flatpickr !== 'undefined') {
                flatpickr("#txtFechaInicio", {
                    dateFormat: "d/m/Y",
                    locale: "es"
                });
                flatpickr("#txtFechaFinal", {
                    dateFormat: "d/m/Y",
                    locale: "es"
                });
                flatpickr("#FechaAplicacionDinero", {
                    dateFormat: "d/m/Y",
                    locale: "es"
                });
            }

            const botonGastos = document.getElementById('btnAddGastoEdit');
            const container = document.getElementById('otrosGastosContainerEdit');

            const opcionesGasto = [{
                    value: 'GCM01',
                    text: 'GCM01 - Comisión'
                },
                {
                    value: 'GDI02',
                    text: 'GDI02 - Diesel'
                },
                {
                    value: 'GBV01',
                    text: 'GBV01 - Burrero Vacio'
                },
                {
                    value: 'GU001',
                    text: 'GU001 - Urea'
                }
            ];

            if (botonGastos) {
                botonGastos.addEventListener('click', function() {
                    const total = container.querySelectorAll('.gasto-item').length;

                    if (total >= 2) {
                        Swal.fire("Límite alcanzado", "Solo puedes agregar un máximo de 2 gastos.",
                            "warning");
                        return;
                    }

                    const gastoHTML = `
                <div class="row gasto-item align-items-center mb-3 border-bottom pb-3">
                    <div class="col-md-3">
                        <label class="form-label mb-1">Motivo del gasto</label>
                        <select class="form-control gasto-select" name="gasto_nombre[]" required>
                            <option value="">Seleccione un motivo</option>
                            ${opcionesGasto.map(op => `<option value="${op.value}">${op.text}</option>`).join('')}
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">Monto</label>
                        <div class="input-group">
                            <span class="input-group-text bg-gradient-success text-white">
                                <i class="ni ni-money-coins"></i>
                            </span>
                            <input type="number" step="0.01" min="0" class="form-control" name="gasto_monto[]" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label mb-1 d-block">Pago inmed.</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" class="form-check-input pagoInmediatoCheck" name="gasto_pago_inmediato[]">
                        </div>
                    </div>

                    <div class="col-md-3 banco-col" style="display:none;">
                        <label class="form-label mb-1">Banco</label>
                        <select class="form-select" name="gasto_banco_id[]">
                            <option value="">Seleccione banco</option>
                            @foreach ($bancos as $item)
                               <option value="{{ $item['id'] }}">{{ $item['display'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 fecha-col" style="display:none;">
                        <label class="form-label mb-1">Fecha Aplicación</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-calendar text-danger"></i></span>
                            <input class="form-control dateInput" name="fechaAplicacion[]" placeholder="Fecha" type="text" />
                        </div>
                    </div>

                    <div class="col-md-1 text-end mt-4">
                        <button type="button" class="btn btn-danger btn-sm removeGastoBtn mb-0">
                            <i class="ni ni-fat-remove"></i>
                        </button>
                    </div>
                </div>`;

                    container.insertAdjacentHTML('beforeend', gastoHTML);

                    const ultimoDateInput = container.querySelector('.gasto-item:last-child .dateInput');
                    if (ultimoDateInput && typeof flatpickr !== 'undefined') {
                        flatpickr(ultimoDateInput, {
                            dateFormat: "d/m/Y",
                            locale: "es"
                        });
                    }
                    actualizarDisponibles();
                });
            }

            // Mostrar/ocultar banco según pago inmediato
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('pagoInmediatoCheck')) {
                    const row = e.target.closest('.gasto-item');
                    const bancoCol = row.querySelector('.banco-col');
                    const fechaCol = row.querySelector('.fecha-col');

                    bancoCol.style.display = e.target.checked ? 'block' : 'none';
                    fechaCol.style.display = e.target.checked ? 'block' : 'none';

                    if (!e.target.checked) {
                        bancoCol.querySelector('select').value = '';
                        fechaCol.querySelector('input').value = '';
                    }
                }

                if (e.target.classList.contains('gasto-select')) {
                    actualizarDisponibles();
                }
            });

            // Eliminar gasto
            document.addEventListener('click', function(e) {
                if (e.target.closest('.removeGastoBtn')) {
                    e.target.closest('.gasto-item').remove();
                    actualizarDisponibles();
                }
            });

            function actualizarDisponibles() {
                const selects = Array.from(container.querySelectorAll('.gasto-select'));
                const seleccionados = selects.map(s => s.value).filter(v => v !== '');

                selects.forEach((select) => {
                    const valorActual = select.value;
                    const opciones = ['<option value="">Seleccione un motivo</option>'];

                    opcionesGasto.forEach(op => {
                        const ocupadoPorOtro = seleccionados.includes(op.value) && op.value !==
                            valorActual;
                        opciones.push(
                            `<option value="${op.value}" ${ocupadoPorOtro ? 'disabled' : ''}>${op.text}</option>`
                        );
                    });

                    select.innerHTML = opciones.join('');
                    select.value = valorActual;
                });
            }

            // Formatear monedas en inputs
            const moneyInputs = ['txtSueldoOperador', 'txtDineroViaje'];
            moneyInputs.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.addEventListener('blur', function() {
                        let val = parseFloat(input.value.replace(/[$,]/g, ''));
                        if (!isNaN(val)) {
                            input.value = val.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    });
                }
            });

            // Envío del formulario vía AJAX con Loading y Swal
            const form = document.getElementById('formularioEditPlaneacion');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validar sueldo operador
                const sueldoInput = document.getElementById("txtSueldoOperador");
                const sueldoVal = parseFloat(sueldoInput.value.replace(/[$,]/g, '').trim()) || 0;
                if (sueldoVal <= 0) {
                    Swal.fire("Sueldo inválido", "El sueldo del operador debe ser mayor a 0.", "error");
                    return;
                }

                // Validar dinero viaje
                const dineroInput = document.getElementById("txtDineroViaje");
                if (dineroInput.value.trim() === "") {
                    Swal.fire("Dinero de viaje requerido", "Debe ingresar el dinero de viaje.", "error");
                    return;
                }

                // Validar banco
                const bancoSelect = document.getElementById("cmbBanco");
                if (bancoSelect.value === "") {
                    Swal.fire("Banco requerido", "Debe seleccionar un banco.", "error");
                    return;
                }

                // Validar fecha de aplicación
                const fechaAppInput = document.getElementById("FechaAplicacionDinero");
                if (fechaAppInput.value.trim() === "") {
                    Swal.fire("Fecha de aplicación requerida", "Debe ingresar la fecha de aplicación.",
                        "error");
                    return;
                }

                // Validar litros diesel
                const dieselInput = document.getElementById("litros_diesel");
                const dieselVal = parseFloat(dieselInput.value) || 0;
                if (dieselVal <= 0) {
                    Swal.fire("Litros diésel inválido", "Los litros de diésel deben ser mayores a 0.",
                        "error");
                    return;
                }

                const gastosValidos = [];
                let errGasto = false;

                container.querySelectorAll('.gasto-item').forEach(row => {
                    const selectMotivo = row.querySelector('.gasto-select');
                    const inputMonto = row.querySelector('input[name="gasto_monto[]"]');
                    const checkPago = row.querySelector('.pagoInmediatoCheck');
                    const selectBanco = row.querySelector('select[name="gasto_banco_id[]"]');
                    const inputFecha = row.querySelector('input[name="fechaAplicacion[]"]');

                    if (selectMotivo) {
                        if (!selectMotivo.value) {
                            Swal.fire("Gasto inválido",
                                "Debe seleccionar el motivo para todos los gastos adicionales.",
                                "error");
                            errGasto = true;
                            return;
                        }

                        const montoVal = parseFloat(inputMonto.value) || 0;
                        if (montoVal <= 0) {
                            Swal.fire("Monto inválido",
                                "El monto de los gastos adicionales debe ser mayor a 0.",
                                "error");
                            errGasto = true;
                            return;
                        }

                        if (checkPago && checkPago.checked) {
                            if (!selectBanco.value) {
                                Swal.fire("Banco requerido",
                                    "Para gastos con pago inmediato debe seleccionar un banco.",
                                    "error");
                                errGasto = true;
                                return;
                            }
                            if (!inputFecha.value) {
                                Swal.fire("Fecha de aplicación requerida",
                                    "Para gastos con pago inmediato debe seleccionar una fecha de aplicación.",
                                    "error");
                                errGasto = true;
                                return;
                            }
                        }

                        gastosValidos.push({
                            motivo: selectMotivo.value,
                            monto: montoVal,
                            pagoInmediato: checkPago ? checkPago.checked : false,
                            banco: selectBanco ? selectBanco.value : null,
                            fechaAplicacion: (checkPago && checkPago.checked) ? inputFecha
                                .value : null
                        });
                    }
                });

                if (errGasto) return;

                const formData = new FormData(form);
                if (gastosValidos.length > 0) {
                    formData.set('filasOtrosGastos', JSON.stringify(gastosValidos));
                }

                $.ajax({
                    url: form.action,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        mostrarLoading("Actualizando datos de viaje... espere un momento");
                    },
                    success: function(data) {
                        ocultarLoading();
                        Swal.fire({
                            title: data.Titulo || "Éxito",
                            text: data.Mensaje ||
                                "Planeación actualizada correctamente",
                            icon: data.TMensaje || "success"
                        }).then(function() {
                            if (data.TMensaje === "success" || data.success) {
                                window.location.replace("/planeaciones");
                            }
                        });
                    },
                    error: function(xhr) {
                        ocultarLoading();
                        let errMsg = "Ocurrió un error al procesar la solicitud.";
                        if (xhr.responseJSON && xhr.responseJSON.Mensaje) {
                            errMsg = xhr.responseJSON.Mensaje;
                        }
                        Swal.fire("Error", errMsg, "error");
                    }
                });
            });

            // Inicializar flatpickr en gastos pre-cargados al cargar la página
            if (typeof flatpickr !== 'undefined') {
                container.querySelectorAll('.gasto-item .dateInput').forEach(input => {
                    flatpickr(input, {
                        dateFormat: "d/m/Y",
                        locale: "es"
                    });
                });
            }
            actualizarDisponibles();
        });
    </script>
@endsection
