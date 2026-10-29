@extends('layouts.app')

@section('template_title')
    Configuración Contable y Matriz de Conversión
@endsection

@section('content')
<div class="container-fluid py-4">

    {{-- ENCABEZADO --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-light text-primary border px-3 py-1 mb-2 fw-semibold">
                    <i class="fas fa-sliders-h me-1"></i> CONFIGURACIÓN CONAC &amp; MOTOR CONTABLE
                </span>
                <h4 class="fw-bold mb-1 text-dark">Matriz de Conversión y Desglose de Cuentas</h4>
                <p class="text-muted text-sm mb-0">
                    Configuración de reglas automáticas por evento, mapeo de renglones del Balance General y catálogo de cuentas CONAC.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('contabilidad.mapeo-modulos.index', ['id_empresa' => $idEmpresa]) }}" class="btn btn-outline-info btn-sm mb-0 shadow-sm" title="Mapeo y Desglose por Módulos">
                    <i class="fas fa-network-wired me-1"></i> Mapeo por Módulos
                </a>
                <a href="{{ route('contabilidad.manual') }}" class="btn btn-outline-warning btn-sm mb-0 shadow-sm" title="Manual Contable y Guía CONAC">
                    <i class="fas fa-book me-1"></i> Guía Contabilizadora
                </a>
                <a href="{{ route('contabilidad.balance-general.index') }}" class="btn btn-outline-primary btn-sm mb-0 shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Balance General
                </a>
            </div>
        </div>
    </div>

    {{-- PESTAÑAS DE CONFIGURACIÓN --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs nav-justified" id="configContableTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold text-dark border-0 border-bottom border-3 border-primary" id="balance-conceptos-tab" data-bs-toggle="tab" data-bs-target="#balance-conceptos" type="button" role="tab">
                        <i class="fas fa-balance-scale text-primary me-2"></i> 1. Mapeo de Conceptos del Balance General
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-secondary border-0" id="guia-contabilizadora-tab" data-bs-toggle="tab" data-bs-target="#guia-contabilizadora" type="button" role="tab">
                        <i class="fas fa-cogs text-info me-2"></i> 2. Guía Contabilizadora (Matriz de Eventos)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-secondary border-0" id="catalogo-conac-tab" data-bs-toggle="tab" data-bs-target="#catalogo-conac" type="button" role="tab">
                        <i class="fas fa-sitemap text-success me-2"></i> 3. Catálogo Jerárquico CONAC (5 Niveles)
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="configContableTabsContent">

                {{-- TAB 1: MAPEO DE CONCEPTOS DEL BALANCE GENERAL --}}
                <div class="tab-pane fade show active" id="balance-conceptos" role="tabpanel">
                    <div class="alert alert-light border py-2 px-3 mb-4 text-xs text-secondary d-flex align-items-center gap-2">
                        <i class="fas fa-info-circle text-primary fs-6"></i>
                        <div>
                            Cada renglón visual del <strong>Modelo de Balance General (TP 002)</strong> se alimenta recursivamente sumando las subcuentas que coincidan con el prefijo CONAC asignado (ej. <code>1.1.1.2%</code> para todas las cuentas analíticas de bancos, <code>4.3.1.1%</code> para intereses separados).
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sección</th>
                                    <th>Concepto en Balance</th>
                                    <th>Prefijo CONAC Asignado</th>
                                    <th>Tipo de Renglón</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($conceptosBalance as $c)
                                    <tr>
                                        <td>
                                            @if ($c->seccion === 'activo')
                                                <span class="badge bg-primary-light text-primary border border-primary">ACTIVO</span>
                                            @elseif ($c->seccion === 'pasivo')
                                                <span class="badge bg-danger-light text-danger border border-danger">PASIVO</span>
                                            @elseif ($c->seccion === 'patrimonio')
                                                <span class="badge bg-success-light text-success border border-success">PATRIMONIO</span>
                                            @else
                                                <span class="badge bg-info-light text-info border border-info">RESULTADOS</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-dark text-sm">
                                            {{ $c->concepto }}
                                            @if (strtoupper($c->concepto) === 'INTERESES')
                                                <span class="badge bg-info text-dark ms-1" style="font-size: 10px;">Intereses CONAC</span>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm font-monospace text-xs" 
                                                id="inputPrefijo_{{ $c->id }}" 
                                                value="{{ $c->cuenta_codigo_prefijo }}" 
                                                placeholder="Ej. 1.1.1.2%" 
                                                style="max-width: 180px;">
                                        </td>
                                        <td>
                                            @if ($c->es_calculado)
                                                <span class="badge bg-light text-secondary border">Fórmula Calculada</span>
                                            @else
                                                <span class="badge bg-light text-dark border">Agregación CONAC</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-success btn-xs mb-0 shadow-sm" onclick="guardarMapeoBalance({{ $c->id }})">
                                                <i class="fas fa-save me-1"></i> Guardar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB 2: GUÍA CONTABILIZADORA / MATRIZ DE CONVERSIÓN --}}
                <div class="tab-pane fade" id="guia-contabilizadora" role="tabpanel">
                    <div class="alert alert-light border py-2 px-3 mb-4 text-xs text-secondary d-flex align-items-center gap-2">
                        <i class="fas fa-info-circle text-info fs-6"></i>
                        <div>
                            La <strong>Guía Contabilizadora</strong> desacopla los procesos operativos (captura de gastos, abono de intereses, fletes de viajes, liquidaciones a socios) asignando automáticamente las cuentas contables de Cargo y Abono correspondientes con estricta <strong>partida doble</strong>.
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Evento Operativo</th>
                                    <th>Tipo Póliza</th>
                                    <th>Cuenta Cargo (Debe)</th>
                                    <th>Cuenta Abono (Haber)</th>
                                    <th>Regla Dinámica</th>
                                    <th>Descripción de Asiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reglasGuia as $r)
                                    <tr>
                                        <td>
                                            <span class="badge bg-dark text-white font-monospace text-xs">{{ $r->evento }}</span>
                                            @if ($r->subtipo)
                                                <br><small class="text-muted text-xxs">Subtipo: {{ $r->subtipo }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($r->tipo_poliza === 'ingreso')
                                                <span class="badge bg-success text-white">ING - Ingreso</span>
                                            @elseif ($r->tipo_poliza === 'egreso')
                                                <span class="badge bg-danger text-white">EGR - Egreso</span>
                                            @else
                                                <span class="badge bg-secondary text-white">DIA - Diario</span>
                                            @endif
                                        </td>
                                        <td class="text-xs">
                                            @if ($r->cuentaCargo)
                                                <strong class="text-dark">{{ $r->cuentaCargo->codigo }}</strong><br>
                                                <span class="text-muted">{{ $r->cuentaCargo->nombre }}</span>
                                            @elseif ($r->regla_dinamica === 'cuenta_banco' && $r->tipo_poliza === 'ingreso')
                                                <span class="badge bg-light text-primary border">Dinámica: Banco Receptor</span>
                                            @elseif ($r->regla_dinamica === 'cuenta_categoria_gasto_y_banco')
                                                <span class="badge bg-light text-primary border">Dinámica: Categoría Gasto (5.1.*)</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-xs">
                                            @if ($r->cuentaAbono)
                                                <strong class="text-dark">{{ $r->cuentaAbono->codigo }}</strong><br>
                                                <span class="text-muted">{{ $r->cuentaAbono->nombre }}</span>
                                            @elseif ($r->regla_dinamica === 'cuenta_banco' && $r->tipo_poliza === 'egreso')
                                                <span class="badge bg-light text-primary border">Dinámica: Banco Emisor</span>
                                            @elseif ($r->regla_dinamica === 'cuenta_categoria_gasto_y_banco')
                                                <span class="badge bg-light text-primary border">Dinámica: Cuenta Bancaria Emisora</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($r->regla_dinamica)
                                                <span class="badge bg-info-light text-info border border-info font-monospace text-xxs">
                                                    {{ $r->regla_dinamica }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border">Cuentas Fijas</span>
                                            @endif
                                        </td>
                                        <td class="text-xs text-secondary">
                                            {{ $r->descripcion_asiento }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB 3: CATÁLOGO JERÁRQUICO CONAC (5 NIVELES) --}}
                <div class="tab-pane fade" id="catalogo-conac" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="text-xs text-muted">
                            Total de cuentas sembradas: <strong class="text-dark">{{ $cuentasContables->count() }}</strong> cuentas (Niveles 1 a 5 con validación estricta de cuentas afectables).
                        </div>
                        <div>
                            <input type="text" id="buscadorCuentas" class="form-control form-control-sm" placeholder="Buscar por código o nombre..." onkeyup="filtrarCuentasCatalogo(this.value)">
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" id="tablaCuentasCONAC">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre de la Cuenta</th>
                                    <th>Nivel</th>
                                    <th>Rubro</th>
                                    <th>Naturaleza</th>
                                    <th>¿Es Afectable?</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cuentasContables as $cc)
                                    <tr class="cuenta-row">
                                        <td class="font-monospace text-xs fw-bold text-dark">{{ $cc->codigo }}</td>
                                        <td class="text-xs {{ $cc->nivel <= 2 ? 'fw-bolder text-dark' : ($cc->es_afectable ? 'text-dark' : 'text-secondary') }}">
                                            <span style="padding-left: {{ ($cc->nivel - 1) * 16 }}px;">
                                                @if ($cc->es_afectable)
                                                    <i class="fas fa-circle text-primary me-1" style="font-size: 7px;"></i>
                                                @else
                                                    <i class="fas fa-folder text-warning me-1" style="font-size: 11px;"></i>
                                                @endif
                                                {{ $cc->nombre }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">Nivel {{ $cc->nivel }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary text-uppercase border">{{ $cc->rubro }}</span>
                                        </td>
                                        <td>
                                            @if ($cc->naturaleza === 'deudora')
                                                <span class="text-primary text-xs fw-semibold">Deudora</span>
                                            @else
                                                <span class="text-success text-xs fw-semibold">Acreedora</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($cc->es_afectable)
                                                <span class="badge bg-success-light text-success border border-success text-xxs">
                                                    <i class="fas fa-check me-1"></i> Afectable (Recibe cargos/abonos)
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border text-xxs">
                                                    Título / Acumulativa
                                                </span>
                                            @endif
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
    function guardarMapeoBalance(id) {
        const input = document.getElementById('inputPrefijo_' + id);
        const prefijo = input ? input.value : '';

        Swal.fire({
            title: 'Actualizando concepto...',
            text: 'Guardando mapeo CONAC',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("{{ route('contabilidad.configuracion.balance-conceptos.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                cuenta_codigo_prefijo: prefijo
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: data.message,
                    timer: 1600,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', data.message || 'No se pudo actualizar el concepto.', 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Ocurrió un error en la solicitud.', 'error');
        });
    }

    function filtrarCuentasCatalogo(query) {
        const q = query.toLowerCase().trim();
        const filas = document.querySelectorAll('#tablaCuentasCONAC tbody tr.cuenta-row');

        filas.forEach(fila => {
            const texto = fila.innerText.toLowerCase();
            if (texto.includes(q)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }
</script>
@endpush
