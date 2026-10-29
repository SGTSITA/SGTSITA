@extends('layouts.app')

@section('template_title')
    Centro de Mapeo y Desglose Contable por Módulos
@endsection

@section('content')
<div class="container-fluid py-4">

    {{-- ENCABEZADO Y SELECTOR DE EMPRESA --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-light text-primary border px-3 py-1 mb-2 fw-semibold">
                    <i class="fas fa-network-wired me-1"></i> PANEL EXCLUSIVO DEL CONTADOR
                </span>
                <h4 class="fw-bold mb-1 text-dark">Centro de Mapeo y Desglose por Módulos</h4>
                <p class="text-muted text-sm mb-0">
                    Administra qué cuenta contable CONAC atiende a cada cliente, banco o proveedor. La operación diaria se mantiene automatizada con cuentas concentradoras por defecto.
                </p>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                {{-- SELECTOR DE EMPRESA CON RESTRICCIÓN DE SEGURIDAD ES_ADMIN --}}
                <div class="d-flex align-items-center gap-2 bg-light p-2 rounded border">
                    <label class="form-label mb-0 text-xs fw-bold text-secondary">
                        <i class="fas fa-building text-primary me-1"></i> Empresa:
                    </label>
                    @if ($puedeCambiarEmpresa)
                        <select class="form-select form-select-sm fw-bold border-primary shadow-none" style="min-width: 220px;" onchange="cambiarEmpresa(this.value)">
                            @foreach ($todasEmpresas as $emp)
                                <option value="{{ $emp->id }}" {{ $emp->id == $idEmpresa ? 'selected' : '' }}>
                                    {{ $emp->nombre }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-white text-dark border px-3 py-2 fw-bold text-sm">
                                {{ $empresaActual?->nombre ?? 'Empresa Asignada' }}
                            </span>
                            <span class="badge bg-secondary-light text-muted border text-xxs" title="Tu usuario tiene asignada esta empresa fija">
                                <i class="fas fa-lock me-1"></i> Fija
                            </span>
                        </div>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('contabilidad.configuracion.index', ['id_empresa' => $idEmpresa]) }}" class="btn btn-outline-secondary btn-sm mb-0 shadow-sm" title="Matriz CONAC">
                        <i class="fas fa-sliders-h me-1"></i> Catálogo CONAC
                    </a>
                    <a href="{{ route('contabilidad.balance-general.index', ['id_empresa' => $idEmpresa]) }}" class="btn btn-outline-primary btn-sm mb-0 shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Balance General
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- TARJETA PRINCIPAL CON PESTAÑAS POR MÓDULOS --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs nav-justified" id="mapeoModulosTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold text-dark border-0 border-bottom border-3 border-primary" id="clientes-tab" data-bs-toggle="tab" data-bs-target="#tab-clientes" type="button" role="tab">
                        <i class="fas fa-users text-primary me-2"></i> 1. Clientes (Cuentas por Cobrar - CXC)
                        <span class="badge bg-primary text-white ms-1 rounded-pill">{{ $clientes->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-secondary border-0" id="bancos-tab" data-bs-toggle="tab" data-bs-target="#tab-bancos" type="button" role="tab">
                        <i class="fas fa-university text-info me-2"></i> 2. Bancos y Cuentas de Tesorería
                        <span class="badge bg-info text-white ms-1 rounded-pill">{{ $bancos->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-secondary border-0" id="proveedores-tab" data-bs-toggle="tab" data-bs-target="#tab-proveedores" type="button" role="tab">
                        <i class="fas fa-truck-moving text-warning me-2"></i> 3. Proveedores (Cuentas por Pagar - CXP)
                        <span class="badge bg-warning text-dark ms-1 rounded-pill">{{ $proveedores->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-secondary border-0" id="gastos-tab" data-bs-toggle="tab" data-bs-target="#tab-gastos" type="button" role="tab">
                        <i class="fas fa-gas-pump text-danger me-2"></i> 4. Categorías de Gastos
                        <span class="badge bg-danger text-white ms-1 rounded-pill">{{ $categoriasGastos->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="mapeoModulosContent">

                {{-- ==================== TAB 1: CLIENTES ==================== --}}
                <div class="tab-pane fade show active" id="tab-clientes" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 bg-light p-3 rounded border">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle text-primary fs-5"></i>
                            <div>
                                <span class="fw-bold text-dark text-sm">Cuenta Concentradora Default de Clientes:</span>
                                <span class="badge bg-white text-primary border font-monospace ms-2">
                                    {{ $cuentaDefaultClientes->codigo }} - {{ $cuentaDefaultClientes->nombre }}
                                </span>
                                <div class="text-xs text-muted mt-1">
                                    Los clientes operan por defecto en esta cuenta. Si deseas ver un cliente desglosado en el Auxiliar Contable, presiona <strong>Desglosar Analítica</strong>. Todas las subcuentas suman automáticamente a la cuenta de mayor <code>1.1.2.1</code> en el Balance General.
                                </div>
                            </div>
                        </div>
                        <div>
                            <input type="text" id="filtroClientes" class="form-control form-control-sm" placeholder="Buscar cliente..." onkeyup="filtrarTabla('tablaClientes', this.value)">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaClientes">
                            <thead class="table-light">
                                <tr>
                                    <th># ID</th>
                                    <th>Cliente</th>
                                    <th>RFC</th>
                                    <th>Tratamiento Contable</th>
                                    <th>Cuenta CONAC Asignada</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $mapeosClientes = $mapeos->get('clientes', collect())->keyBy('entidad_id'); @endphp
                                @forelse ($clientes as $cli)
                                    @php $map = $mapeosClientes->get($cli->id); @endphp
                                    <tr id="row_clientes_{{ $cli->id }}">
                                        <td class="text-xs text-muted font-monospace">{{ $cli->id }}</td>
                                        <td class="fw-bold text-dark text-sm">{{ $cli->nombre }}</td>
                                        <td class="text-xs text-secondary font-monospace">{{ $cli->rfc ?: 'N/A' }}</td>
                                        <td>
                                            @if ($map && $map->cuentaContable)
                                                <span class="badge bg-success-light text-success border border-success">
                                                    <i class="fas fa-check-circle me-1"></i> Subcuenta Analítica
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border">
                                                    Cuenta Concentradora Default
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($map && $map->cuentaContable)
                                                <span class="badge bg-white text-dark border font-monospace text-xs">
                                                    {{ $map->cuentaContable->codigo }} - {{ $map->cuentaContable->nombre }}
                                                </span>
                                            @else
                                                <span class="text-xs text-muted font-monospace">
                                                    {{ $cuentaDefaultClientes->codigo }} - {{ $cuentaDefaultClientes->nombre }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($map && $map->cuentaContable)
                                                <button class="btn btn-outline-danger btn-xs mb-0 shadow-none" onclick="revertirDesglose('clientes', {{ $cli->id }}, '{{ addslashes($cli->nombre) }}')">
                                                    <i class="fas fa-undo me-1"></i> Restablecer a Default
                                                </button>
                                            @else
                                                <button class="btn btn-outline-primary btn-xs mb-0 shadow-sm" onclick="desglosarEntidad('clientes', {{ $cli->id }}, '{{ addslashes($cli->nombre) }}')">
                                                    <i class="fas fa-bolt text-warning me-1"></i> Desglosar Analítica
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No se encontraron clientes registrados en esta empresa.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ==================== TAB 2: BANCOS ==================== --}}
                <div class="tab-pane fade" id="tab-bancos" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 bg-light p-3 rounded border">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle text-info fs-5"></i>
                            <div>
                                <span class="fw-bold text-dark text-sm">Cuenta Concentradora Default de Bancos:</span>
                                <span class="badge bg-white text-info border font-monospace ms-2">
                                    {{ $cuentaDefaultBancos->codigo }} - {{ $cuentaDefaultBancos->nombre }}
                                </span>
                                <div class="text-xs text-muted mt-1">
                                    Puedes asociar a cada cuenta bancaria su propia subcuenta analítica bajo <code>1.1.1.2</code> para que las transferencias y conciliaciones de tesorería impacten individualmente.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaBancos">
                            <thead class="table-light">
                                <tr>
                                    <th># ID</th>
                                    <th>Banco</th>
                                    <th>Número de Cuenta / CLABE</th>
                                    <th>Moneda</th>
                                    <th>Tratamiento Contable</th>
                                    <th>Subcuenta CONAC</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $mapeosBancos = $mapeos->get('bancos', collect())->keyBy('entidad_id'); @endphp
                                @forelse ($bancos as $b)
                                    @php $mapB = $mapeosBancos->get($b->id); @endphp
                                    <tr id="row_bancos_{{ $b->id }}">
                                        <td class="text-xs text-muted font-monospace">{{ $b->id }}</td>
                                        <td class="fw-bold text-dark text-sm">
                                            {{ $b->catBanco?->nombre ?: ($b->nombre_banco ?: 'Banco') }}
                                        </td>
                                        <td class="text-xs text-secondary font-monospace">
                                            {{ $b->cuenta_bancaria ?: ($b->clabe ?: 'S/N') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $b->moneda ?: 'MXN' }}</span>
                                        </td>
                                        <td>
                                            @if ($mapB && $mapB->cuentaContable)
                                                <span class="badge bg-success-light text-success border border-success">
                                                    <i class="fas fa-check-circle me-1"></i> Subcuenta Individual
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border">
                                                    Concentradora General
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($mapB && $mapB->cuentaContable)
                                                <span class="badge bg-white text-dark border font-monospace text-xs">
                                                    {{ $mapB->cuentaContable->codigo }} - {{ $mapB->cuentaContable->nombre }}
                                                </span>
                                            @else
                                                <span class="text-xs text-muted font-monospace">
                                                    {{ $cuentaDefaultBancos->codigo }} - {{ $cuentaDefaultBancos->nombre }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($mapB && $mapB->cuentaContable)
                                                <button class="btn btn-outline-danger btn-xs mb-0 shadow-none" onclick="revertirDesglose('bancos', {{ $b->id }}, '{{ addslashes($b->nombre_banco ?? 'Banco') }}')">
                                                    <i class="fas fa-undo me-1"></i> Restablecer a Default
                                                </button>
                                            @else
                                                <button class="btn btn-outline-primary btn-xs mb-0 shadow-sm" onclick="desglosarEntidad('bancos', {{ $b->id }}, '{{ addslashes($b->nombre_banco ?? 'Banco') }}')">
                                                    <i class="fas fa-bolt text-warning me-1"></i> Desglosar Analítica
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No se encontraron cuentas bancarias registradas en esta empresa.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ==================== TAB 3: PROVEEDORES ==================== --}}
                <div class="tab-pane fade" id="tab-proveedores" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 bg-light p-3 rounded border">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle text-warning fs-5"></i>
                            <div>
                                <span class="fw-bold text-dark text-sm">Cuenta Concentradora Default de Proveedores (CXP):</span>
                                <span class="badge bg-white text-dark border font-monospace ms-2">
                                    {{ $cuentaDefaultProveedores->codigo }} - {{ $cuentaDefaultProveedores->nombre }}
                                </span>
                                <div class="text-xs text-muted mt-1">
                                    Permite crear subcuentas analíticas individuales bajo <code>2.1.1.2</code> para proveedores estratégicos (Fletes subcontratados, talleres, etc.).
                                </div>
                            </div>
                        </div>
                        <div>
                            <input type="text" id="filtroProveedores" class="form-control form-control-sm" placeholder="Buscar proveedor..." onkeyup="filtrarTabla('tablaProveedores', this.value)">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaProveedores">
                            <thead class="table-light">
                                <tr>
                                    <th># ID</th>
                                    <th>Proveedor</th>
                                    <th>RFC</th>
                                    <th>Tipo</th>
                                    <th>Tratamiento Contable</th>
                                    <th>Cuenta CONAC</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $mapeosProv = $mapeos->get('proveedores', collect())->keyBy('entidad_id'); @endphp
                                @forelse ($proveedores as $prv)
                                    @php $mapP = $mapeosProv->get($prv->id); @endphp
                                    <tr id="row_proveedores_{{ $prv->id }}">
                                        <td class="text-xs text-muted font-monospace">{{ $prv->id }}</td>
                                        <td class="fw-bold text-dark text-sm">{{ $prv->nombre }}</td>
                                        <td class="text-xs text-secondary font-monospace">{{ $prv->rfc ?: 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border text-xxs">{{ $prv->tipo ?: 'General' }}</span>
                                        </td>
                                        <td>
                                            @if ($mapP && $mapP->cuentaContable)
                                                <span class="badge bg-success-light text-success border border-success">
                                                    <i class="fas fa-check-circle me-1"></i> Subcuenta Analítica
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border">
                                                    Concentradora Default
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($mapP && $mapP->cuentaContable)
                                                <span class="badge bg-white text-dark border font-monospace text-xs">
                                                    {{ $mapP->cuentaContable->codigo }} - {{ $mapP->cuentaContable->nombre }}
                                                </span>
                                            @else
                                                <span class="text-xs text-muted font-monospace">
                                                    {{ $cuentaDefaultProveedores->codigo }} - {{ $cuentaDefaultProveedores->nombre }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($mapP && $mapP->cuentaContable)
                                                <button class="btn btn-outline-danger btn-xs mb-0 shadow-none" onclick="revertirDesglose('proveedores', {{ $prv->id }}, '{{ addslashes($prv->nombre) }}')">
                                                    <i class="fas fa-undo me-1"></i> Restablecer a Default
                                                </button>
                                            @else
                                                <button class="btn btn-outline-primary btn-xs mb-0 shadow-sm" onclick="desglosarEntidad('proveedores', {{ $prv->id }}, '{{ addslashes($prv->nombre) }}')">
                                                    <i class="fas fa-bolt text-warning me-1"></i> Desglosar Analítica
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No se encontraron proveedores registrados en esta empresa.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ==================== TAB 4: CATEGORÍAS DE GASTOS ==================== --}}
                <div class="tab-pane fade" id="tab-gastos" role="tabpanel">
                    <div class="alert alert-light border py-2 px-3 mb-3 text-xs text-secondary d-flex align-items-center gap-2">
                        <i class="fas fa-info-circle text-danger fs-6"></i>
                        <div>
                            Los gastos operativos se imputan automáticamente a su cuenta de Clase 5 (Gastos de Funcionamiento) según la categoría y concepto capturado en el viaje.
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th># ID</th>
                                    <th>Categoría de Gasto</th>
                                    <th>Cuenta CONAC Sugerida</th>
                                    <th>Rubro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categoriasGastos as $cat)
                                    <tr>
                                        <td class="text-xs text-muted font-monospace">{{ $cat->id }}</td>
                                        <td class="fw-bold text-dark text-sm">{{ $cat->categoria }}</td>
                                        <td>
                                            @php
                                                $nombreCat = strtoupper($cat->categoria);
                                                $sug = '5.1.3.1 - Gastos Generales';
                                                if (str_contains($nombreCat, 'DIESEL') || str_contains($nombreCat, 'COMBUSTIBLE')) $sug = '5.1.1.1 - Combustibles y Lubricantes';
                                                elseif (str_contains($nombreCat, 'CASETA') || str_contains($nombreCat, 'PEAJE')) $sug = '5.1.1.2 - Casetas y Peajes';
                                                elseif (str_contains($nombreCat, 'MANIOBRA') || str_contains($nombreCat, 'BURREO')) $sug = '5.1.1.3 - Maniobras y Burreo';
                                                elseif (str_contains($nombreCat, 'TALLER') || str_contains($nombreCat, 'LLANTA') || str_contains($nombreCat, 'MANTENIMIENTO')) $sug = '5.1.2.1 - Mantenimiento y Refacciones';
                                            @endphp
                                            <span class="badge bg-white text-dark border font-monospace text-xs">{{ $sug }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger-light text-danger border border-danger text-xxs">GASTOS OPERATIVOS</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function cambiarEmpresa(idEmpresa) {
        window.location.href = "{{ route('contabilidad.mapeo-modulos.index') }}?id_empresa=" + idEmpresa;
    }

    function filtrarTabla(tablaId, valor) {
        const filtro = valor.toLowerCase();
        const filas = document.querySelectorAll('#' + tablaId + ' tbody tr');
        filas.forEach(row => {
            const texto = row.innerText.toLowerCase();
            row.style.display = texto.includes(filtro) ? '' : 'none';
        });
    }

    function desglosarEntidad(modulo, entidadId, nombre) {
        Swal.fire({
            title: '¿Desglosar Subcuenta Analítica?',
            html: `Se creará automáticamente una subcuenta analítica propia para <strong>${nombre}</strong> bajo su cuenta de mayor CONAC.<br><br><small class="text-muted">A partir de ahora, sus pólizas individuales se registrarán en esta subcuenta y sumarán en el Balance General.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, desglosar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#007bff'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Generando subcuenta...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch("{{ route('contabilidad.mapeo-modulos.desglosar') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        modulo: modulo,
                        entidad_id: entidadId,
                        id_empresa: {{ $idEmpresa }}
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Desglose Exitoso!',
                            text: data.message,
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo realizar el desglose', 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
                });
            }
        });
    }

    function revertirDesglose(modulo, entidadId, nombre) {
        Swal.fire({
            title: '¿Restablecer a Cuenta Default?',
            html: `<strong>${nombre}</strong> volverá a operar usando la cuenta concentradora por defecto de la empresa.<br><br><small class="text-muted">La subcuenta creada en el catálogo se conservará para auditoría histórica.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, restablecer',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Restableciendo...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch("{{ route('contabilidad.mapeo-modulos.revertir') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        modulo: modulo,
                        entidad_id: entidadId,
                        id_empresa: {{ $idEmpresa }}
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Restablecido',
                            text: data.message,
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo restablecer', 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
                });
            }
        });
    }
</script>
@endpush
