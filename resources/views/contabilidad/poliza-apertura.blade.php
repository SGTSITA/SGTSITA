@extends('layouts.app')

@section('template_title')
    Póliza de Apertura y Saldos Iniciales
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- ENCABEZADO Y ACCIONES --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bolder text-dark mb-0">
                <i class="fas fa-file-invoice-dollar text-primary me-2"></i> Póliza de Apertura / Saldos Iniciales
            </h4>
            <span class="text-muted text-sm">
                Registro formal de saldos iniciales por partida doble bajo lineamientos CONAC para arrancar el ejercicio.
            </span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('contabilidad.manual') }}" class="btn btn-outline-warning btn-sm mb-0">
                <i class="fas fa-book me-1"></i> Manual Contable
            </a>
            <a href="{{ route('contabilidad.balance-general.index') }}" class="btn btn-outline-secondary btn-sm mb-0">
                <i class="fas fa-arrow-left me-1"></i> Ir al Balance General
            </a>
            <a href="{{ route('contabilidad.configuracion.index') }}" class="btn btn-outline-primary btn-sm mb-0">
                <i class="fas fa-cogs me-1"></i> Configuración y Matriz
            </a>
        </div>
    </div>

    {{-- FORMULARIO PRINCIPAL --}}
    <form id="formPolizaApertura">
        @csrf
        <div class="row g-3">

            {{-- PANEL SUPERIOR DE PARÁMETROS --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-3">
                        <div class="row g-3 align-items-end">

                            {{-- Empresa --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark text-xs mb-1">
                                    <i class="fas fa-building text-primary me-1"></i> Empresa:
                                </label>
                                <select name="id_empresa" id="empresaSelect" class="form-select form-select-sm" {{ !$esAdmin ? 'disabled' : '' }}>
                                    @foreach ($todasEmpresas as $emp)
                                        <option value="{{ $emp->id }}" {{ (string)$idEmpresa === (string)$emp->id ? 'selected' : '' }}>
                                            {{ $emp->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (!$esAdmin)
                                    <input type="hidden" name="id_empresa" value="{{ $idEmpresa }}">
                                @endif
                            </div>

                            {{-- Ejercicio / Año --}}
                            <div class="col-md-2">
                                <label class="form-label fw-bold text-dark text-xs mb-1">
                                    <i class="fas fa-calendar-alt text-primary me-1"></i> Ejercicio / Año:
                                </label>
                                <input type="number" id="inputAnio" name="anio" class="form-control form-control-sm" value="{{ $anio }}" min="2020" max="2035">
                            </div>

                            {{-- Fecha de Apertura --}}
                            <div class="col-md-2">
                                <label class="form-label fw-bold text-dark text-xs mb-1">
                                    <i class="fas fa-clock text-primary me-1"></i> Fecha de Corte:
                                </label>
                                <input type="date" id="inputFecha" name="fecha" class="form-control form-control-sm" value="{{ $polizaExistente ? $polizaExistente->fecha->format('Y-m-d') : ($anio . '-01-01') }}" required>
                            </div>

                            {{-- Concepto --}}
                            <div class="col-md-5">
                                <label class="form-label fw-bold text-dark text-xs mb-1">
                                    <i class="fas fa-comment-alt text-primary me-1"></i> Concepto General:
                                </label>
                                <input type="text" id="inputConcepto" name="concepto" class="form-control form-control-sm" value="{{ $polizaExistente ? $polizaExistente->concepto : ('Póliza de Apertura / Saldos Iniciales Ejercicio ' . $anio) }}" required>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- PANEL DE RESUMEN DE CUADRE (STATUS BAR) --}}
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body p-3">
                        <div class="row align-items-center text-center">
                            <div class="col-md-3">
                                <span class="text-xs text-uppercase text-muted fw-bold d-block">Folio Póliza</span>
                                <h5 class="fw-bolder mb-0 text-dark" id="folioLabel">
                                    {{ $polizaExistente ? $polizaExistente->numero : ('APE-' . $anio . '01-0001') }}
                                </h5>
                                @if ($polizaExistente)
                                    <span class="badge bg-success text-xxs mt-1">Registrada en BD</span>
                                @else
                                    <span class="badge bg-secondary text-xxs mt-1">Nueva Apertura</span>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <span class="text-xs text-uppercase text-muted fw-bold d-block">Total Debe (Cargos)</span>
                                <h4 class="fw-bolder text-primary mb-0" id="totalDebeDisplay">$0.00</h4>
                            </div>
                            <div class="col-md-3">
                                <span class="text-xs text-uppercase text-muted fw-bold d-block">Total Haber (Abonos)</span>
                                <h4 class="fw-bolder text-info mb-0" id="totalHaberDisplay">$0.00</h4>
                            </div>
                            <div class="col-md-3">
                                <span class="text-xs text-uppercase text-muted fw-bold d-block">Diferencia (Cuadre)</span>
                                <h4 class="fw-bolder mb-0" id="diferenciaDisplay">$0.00</h4>
                                <span id="badgeCuadre" class="badge bg-success text-xxs">Cuadrada</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLA DE ASIENTOS DE APERTURA --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bolder text-dark mb-0">
                            <i class="fas fa-list text-primary me-2"></i> Desglose de Cuentas Afectables y Saldos Iniciales
                        </h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-success btn-sm mb-0" id="btnAgregarFila">
                                <i class="fas fa-plus me-1"></i> Agregar Renglón
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-items-center table-hover mb-0" id="tablaAsientos">
                                <thead class="bg-gray-100 text-xxs text-uppercase fw-bold text-dark">
                                    <tr>
                                        <th style="width: 5%;" class="text-center">#</th>
                                        <th style="width: 40%;">Cuenta Contable Afectable (CONAC)</th>
                                        <th style="width: 15%;">Referencia</th>
                                        <th style="width: 15%;" class="text-end">Debe / Cargo ($)</th>
                                        <th style="width: 15%;" class="text-end">Haber / Abono ($)</th>
                                        <th style="width: 10%;" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="asientosBody">
                                    {{-- Se llena dinámicamente con JS o con la póliza existente --}}
                                </tbody>
                                <tfoot class="bg-gray-100 fw-bold text-sm">
                                    <tr>
                                        <td colspan="3" class="text-end">SUMAS TOTALES:</td>
                                        <td class="text-end text-primary" id="footerTotalDebe">$0.00</td>
                                        <td class="text-end text-info" id="footerTotalHaber">$0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle text-info me-1"></i> Todas las cuentas deben ser afectables de último nivel y la partida doble debe ser exacta ($\Delta = 0.00$).
                        </small>
                        <button type="button" class="btn btn-primary btn-md shadow-sm mb-0" id="btnGuardarPoliza">
                            <i class="fas fa-save me-1"></i> Guardar y Aplicar Póliza de Apertura
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>

{{-- SCRIPT INTERACTIVO DE CUADRE Y GESTIÓN --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cuentasAfectables = @json($cuentasAfectables);
    const polizaExistente = @json($polizaExistente);

    const asientosBody = document.getElementById('asientosBody');
    const btnAgregarFila = document.getElementById('btnAgregarFila');
    const btnGuardarPoliza = document.getElementById('btnGuardarPoliza');
    const totalDebeDisplay = document.getElementById('totalDebeDisplay');
    const totalHaberDisplay = document.getElementById('totalHaberDisplay');
    const diferenciaDisplay = document.getElementById('diferenciaDisplay');
    const badgeCuadre = document.getElementById('badgeCuadre');
    const footerTotalDebe = document.getElementById('footerTotalDebe');
    const footerTotalHaber = document.getElementById('footerTotalHaber');

    let filaIndex = 0;

    // Helper para generar opciones de cuentas
    function generarOpcionesCuentas(seleccionadaId = null) {
        let html = '<option value="">-- Seleccionar Cuenta Afectable --</option>';
        cuentasAfectables.forEach(c => {
            const sel = (seleccionadaId && String(seleccionadaId) === String(c.id)) ? 'selected' : '';
            html += `<option value="${c.id}" ${sel}>${c.codigo} - ${c.nombre} (${c.naturaleza.toUpperCase()})</option>`;
        });
        return html;
    }

    // Agregar nueva fila
    function agregarFila(cuentaId = null, referencia = 'SALDO_INICIAL', debe = 0, haber = 0) {
        filaIndex++;
        const tr = document.createElement('tr');
        tr.id = `fila_${filaIndex}`;
        tr.innerHTML = `
            <td class="text-center fw-bold text-xs text-muted">${filaIndex}</td>
            <td>
                <select name="movimientos[${filaIndex}][cuenta_contable_id]" class="form-select form-select-sm cuenta-select" required>
                    ${generarOpcionesCuentas(cuentaId)}
                </select>
            </td>
            <td>
                <input type="text" name="movimientos[${filaIndex}][referencia]" class="form-control form-control-sm" value="${referencia}">
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="movimientos[${filaIndex}][debe]" class="form-control form-control-sm text-end input-monto input-debe" value="${parseFloat(debe).toFixed(2)}">
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="movimientos[${filaIndex}][haber]" class="form-control form-control-sm text-end input-monto input-haber" value="${parseFloat(haber).toFixed(2)}">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm p-1 px-2 mb-0 btn-eliminar-fila" title="Eliminar fila">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        asientosBody.appendChild(tr);

        // Event listeners en la nueva fila
        tr.querySelectorAll('.input-monto').forEach(inp => {
            inp.addEventListener('input', calcularTotales);
        });

        tr.querySelector('.btn-eliminar-fila').addEventListener('click', function () {
            if (asientosBody.children.length > 2) {
                tr.remove();
                renumerarFilas();
                calcularTotales();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Mínimo de asientos',
                    text: 'Una póliza requiere al menos dos renglones por partida doble.'
                });
            }
        });

        calcularTotales();
    }

    function renumerarFilas() {
        Array.from(asientosBody.children).forEach((tr, idx) => {
            tr.querySelector('td:first-child').innerText = idx + 1;
        });
    }

    // Calcular totales y cuadre
    function calcularTotales() {
        let totalDebe = 0;
        let totalHaber = 0;

        document.querySelectorAll('.input-debe').forEach(inp => {
            const val = parseFloat(inp.value) || 0;
            totalDebe += val;
        });

        document.querySelectorAll('.input-haber').forEach(inp => {
            const val = parseFloat(inp.value) || 0;
            totalHaber += val;
        });

        totalDebe = Math.round(totalDebe * 100) / 100;
        totalHaber = Math.round(totalHaber * 100) / 100;
        const diferencia = Math.round(Math.abs(totalDebe - totalHaber) * 100) / 100;

        totalDebeDisplay.innerText = '$' + totalDebe.toLocaleString('es-MX', { minimumFractionDigits: 2 });
        totalHaberDisplay.innerText = '$' + totalHaber.toLocaleString('es-MX', { minimumFractionDigits: 2 });
        footerTotalDebe.innerText = '$' + totalDebe.toLocaleString('es-MX', { minimumFractionDigits: 2 });
        footerTotalHaber.innerText = '$' + totalHaber.toLocaleString('es-MX', { minimumFractionDigits: 2 });

        diferenciaDisplay.innerText = '$' + diferencia.toLocaleString('es-MX', { minimumFractionDigits: 2 });

        if (diferencia === 0 && totalDebe > 0) {
            diferenciaDisplay.className = 'fw-bolder mb-0 text-success';
            badgeCuadre.className = 'badge bg-success text-xxs';
            badgeCuadre.innerText = 'Cuadrada (Partida Doble OK)';
            btnGuardarPoliza.disabled = false;
        } else {
            diferenciaDisplay.className = 'fw-bolder mb-0 text-danger';
            badgeCuadre.className = 'badge bg-danger text-xxs';
            badgeCuadre.innerText = 'Descuadrada (Diferencia > $0.00)';
            btnGuardarPoliza.disabled = (totalDebe === 0 && totalHaber === 0) ? false : true;
        }
    }

    // Precargar si existía póliza previa
    if (polizaExistente && polizaExistente.movimientos && polizaExistente.movimientos.length > 0) {
        polizaExistente.movimientos.forEach(m => {
            agregarFila(m.cuenta_contable_id, m.referencia || 'SALDO_INICIAL', m.debe, m.haber);
        });
    } else {
        // Filas default para arrancar
        agregarFila(null, 'SALDO_INICIAL', 0, 0);
        agregarFila(null, 'SALDO_INICIAL', 0, 0);
    }

    btnAgregarFila.addEventListener('click', function () {
        agregarFila();
    });

    // Cambio de empresa / año para recargar
    document.getElementById('empresaSelect')?.addEventListener('change', function () {
        const empresaId = this.value;
        const anio = document.getElementById('inputAnio').value;
        window.location.href = `{{ route('contabilidad.poliza-apertura.index') }}?id_empresa=${empresaId}&anio=${anio}`;
    });

    document.getElementById('inputAnio')?.addEventListener('change', function () {
        const empresaId = document.getElementById('empresaSelect').value;
        const anio = this.value;
        window.location.href = `{{ route('contabilidad.poliza-apertura.index') }}?id_empresa=${empresaId}&anio=${anio}`;
    });

    // Guardar Póliza con AJAX
    btnGuardarPoliza.addEventListener('click', function () {
        const filas = asientosBody.querySelectorAll('tr');
        if (filas.length < 2) {
            Swal.fire({
                icon: 'warning',
                title: 'Partida Doble Requerida',
                text: 'Debes capturar al menos 2 asientos para balancear la póliza.'
            });
            return;
        }

        let faltanCuentas = false;
        filas.forEach(tr => {
            const sel = tr.querySelector('.cuenta-select');
            if (!sel || !sel.value) faltanCuentas = true;
        });

        if (faltanCuentas) {
            Swal.fire({
                icon: 'warning',
                title: 'Cuentas incompletas',
                text: 'Por favor selecciona la cuenta contable en todos los renglones.'
            });
            return;
        }

        // Serializar movimientos
        const movimientos = [];
        filas.forEach(tr => {
            movimientos.push({
                cuenta_contable_id: parseInt(tr.querySelector('.cuenta-select').value),
                referencia: tr.querySelector('input[name*="[referencia]"]').value,
                debe: parseFloat(tr.querySelector('.input-debe').value) || 0,
                haber: parseFloat(tr.querySelector('.input-haber').value) || 0,
            });
        });

        const payload = {
            id_empresa: parseInt(document.querySelector('select[name="id_empresa"]')?.value || document.querySelector('input[name="id_empresa"]')?.value),
            fecha: document.getElementById('inputFecha').value,
            concepto: document.getElementById('inputConcepto').value,
            movimientos: movimientos
        };

        Swal.fire({
            title: '¿Confirmar Póliza de Apertura?',
            text: 'Se registrarán los saldos iniciales del ejercicio para esta empresa.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, Guardar y Aplicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Guardando...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch("{{ route('contabilidad.poliza-apertura.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Póliza Registrada!',
                            text: data.message,
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Cuadre',
                            text: data.message || 'No se pudo guardar la póliza.'
                        });
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Servidor',
                        text: 'Ocurrió un error inesperado al procesar la solicitud.'
                    });
                });
            }
        });
    });
});
</script>
@endpush
@endsection
