{{-- VISTA PREVIA WEB: AUXILIAR CONTABLE / LIBRO MAYOR POR CUENTAS [AC-07] --}}
<div class="auxiliar-contable-container">

    {{-- BARRA DE CONTROLES LOCALES DEL AUXILIAR --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between p-3 mb-4 rounded bg-light border shadow-sm gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="checkSoloConMovs" checked onchange="filtrarCuentasAuxiliar()">
                <label class="form-check-label fw-bold text-dark text-xs mb-0" for="checkSoloConMovs">
                    <i class="fas fa-filter text-primary me-1"></i> Ver solo cuentas con saldo o movimientos
                </label>
            </div>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="colapsarTodas(true)" title="Colapsar todas las cuentas">
                    <i class="fas fa-compress-alt me-1"></i> Colapsar
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="colapsarTodas(false)" title="Expandir todas las cuentas">
                    <i class="fas fa-expand-alt me-1"></i> Expandir
                </button>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="max-width: 320px;">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="inputBuscarCuenta" class="form-control" placeholder="Buscar cuenta o concepto..." onkeyup="filtrarCuentasAuxiliar()">
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()" title="Imprimir auxiliar">
                <i class="fas fa-print me-1"></i> Imprimir
            </button>
        </div>
    </div>

    {{-- KPI RESUMEN DEL AUXILIAR --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border border-success bg-success-light p-3 rounded text-center shadow-sm">
                <span class="text-xs text-uppercase text-success fw-bold"><i class="fas fa-arrow-down me-1"></i>Total Cargos (Debe)</span>
                <h4 class="fw-bolder text-success mb-0 mt-1">$ {{ number_format($auxiliar['total_debe'], 2) }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-danger bg-danger-light p-3 rounded text-center shadow-sm">
                <span class="text-xs text-uppercase text-danger fw-bold"><i class="fas fa-arrow-up me-1"></i>Total Abonos (Haber)</span>
                <h4 class="fw-bolder text-danger mb-0 mt-1">$ {{ number_format($auxiliar['total_haber'], 2) }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-primary bg-primary-light p-3 rounded text-center shadow-sm">
                <span class="text-xs text-uppercase text-primary fw-bold"><i class="fas fa-balance-scale me-1"></i>Equilibrio en Movimientos</span>
                <h4 class="fw-bolder text-primary mb-0 mt-1">
                    @if($auxiliar['cuadrado'])
                        <span class="badge bg-success text-white px-3 py-2"><i class="fas fa-check-circle me-1"></i>Cuadrado ($ 0.00)</span>
                    @else
                        <span class="badge bg-danger text-white px-3 py-2"><i class="fas fa-exclamation-triangle me-1"></i>Dif: $ {{ number_format($auxiliar['diferencia'], 2) }}</span>
                    @endif
                </h4>
            </div>
        </div>
    </div>

    {{-- LISTADO JERÁRQUICO DE CUENTAS CON SUS PÓLIZAS --}}
    <div class="listado-auxiliar">
        @foreach ($auxiliar['cuentas'] as $idx => $c)
            @if (!$c['es_afectable'])
                {{-- ENCABEZADO DE GRUPO ESTRUCTURAL / MAYOR --}}
                <div class="seccion-mayor py-2 px-3 mt-3 mb-2 rounded bg-dark text-white d-flex justify-content-between align-items-center shadow-sm"
                     data-codigo="{{ $c['codigo'] }}">
                    <span class="fw-bold text-sm">
                        <i class="fas fa-folder-open text-warning me-2"></i>{{ $c['codigo'] }} - {{ $c['nombre'] }}
                    </span>
                    <span class="badge bg-secondary text-xxs text-uppercase">Nivel {{ $c['nivel'] }} • Rubro {{ $c['rubro'] }}</span>
                </div>
            @else
                {{-- CUENTA AFECTABLE: TARJETA DE LIBRO MAYOR --}}
                <div class="card card-cuenta-afectable border mb-3 shadow-sm"
                     data-codigo="{{ $c['codigo'] }}"
                     data-nombre="{{ strtolower($c['nombre']) }}"
                     data-tiene-movs="{{ $c['tiene_movimientos'] ? '1' : '0' }}">
                    
                    {{-- CABECERA DE LA CUENTA --}}
                    <div class="card-header bg-light py-2 px-3 d-flex flex-wrap justify-content-between align-items-center cursor-pointer border-bottom"
                         data-bs-toggle="collapse" data-bs-target="#collapseCuenta_{{ $c['id'] }}">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-chevron-down text-primary transition-transform"></i>
                            <span class="font-monospace fw-bold text-dark text-sm">{{ $c['codigo'] }}</span>
                            <span class="fw-bold text-dark text-sm">{{ $c['nombre'] }}</span>
                            <span class="badge {{ $c['naturaleza'] === 'deudora' ? 'bg-info' : 'bg-warning' }} text-dark text-xxs text-uppercase">
                                {{ $c['naturaleza'] }}
                            </span>
                            @if(count($c['movimientos']) > 0)
                                <span class="badge bg-primary text-white text-xxs">{{ count($c['movimientos']) }} movimientos</span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-3 text-xs mt-1 mt-md-0">
                            <div>
                                <span class="text-muted">Saldo Inicial:</span>
                                <strong class="text-dark">$ {{ number_format($c['saldo_inicial'], 2) }}</strong>
                            </div>
                            <div>
                                <span class="text-muted">Cargos:</span>
                                <strong class="text-success">$ {{ number_format($c['total_debe'], 2) }}</strong>
                            </div>
                            <div>
                                <span class="text-muted">Abonos:</span>
                                <strong class="text-danger">$ {{ number_format($c['total_haber'], 2) }}</strong>
                            </div>
                            <div class="border-start ps-3">
                                <span class="text-muted">Saldo Final:</span>
                                <strong class="text-primary fs-6">$ {{ number_format($c['saldo_final'], 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- CUERPO DE PÓLIZAS (DESPLEGABLE) --}}
                    <div class="collapse show" id="collapseCuenta_{{ $c['id'] }}">
                        @if (!empty($c['movimientos']))
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0 text-xs">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 85px;">Fecha</th>
                                            <th style="width: 75px;">Tipo</th>
                                            <th style="width: 140px;">Póliza #</th>
                                            <th>Concepto / Detalle</th>
                                            <th style="width: 170px;">Referencia / Contenedor</th>
                                            <th class="text-end" style="width: 115px;">Cargos (Debe)</th>
                                            <th class="text-end" style="width: 115px;">Abonos (Haber)</th>
                                            <th class="text-end" style="width: 125px;">Saldo Acum.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($c['movimientos'] as $m)
                                            <tr>
                                                <td class="text-center">{{ $m['fecha_formato'] }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary text-white text-xxs">{{ $m['poliza_tipo'] }}</span>
                                                </td>
                                                <td class="font-monospace fw-bold text-primary">
                                                    <a href="{{ route('contabilidad.balance-general.index') }}?tipo_reporte=libro_diario&fecha_inicio={{ $fechaInicio }}&fecha_corte={{ $fechaCorte }}" class="text-decoration-none" title="Ver en Libro Diario">
                                                        {{ $m['poliza_numero'] }}
                                                    </a>
                                                </td>
                                                <td>{{ $m['concepto'] }}</td>
                                                <td>
                                                    @if($m['contenedor'])
                                                        <span class="badge bg-info text-dark font-monospace text-xxs me-1">
                                                            <i class="fas fa-box me-1"></i>{{ $m['contenedor'] }}
                                                        </span>
                                                    @endif
                                                    <span class="text-muted">{{ str_replace('['.$m['contenedor'].']', '', $m['referencia']) }}</span>
                                                </td>
                                                <td class="text-end text-success fw-bold">
                                                    {{ $m['debe'] > 0 ? '$ ' . number_format($m['debe'], 2) : '-' }}
                                                </td>
                                                <td class="text-end text-danger fw-bold">
                                                    {{ $m['haber'] > 0 ? '$ ' . number_format($m['haber'], 2) : '-' }}
                                                </td>
                                                <td class="text-end fw-bold text-dark">
                                                    $ {{ number_format($m['saldo_acumulado'], 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light border-top">
                                        <tr class="fw-bold">
                                            <td colspan="5" class="text-end text-uppercase">Total Cuenta {{ $c['codigo'] }}:</td>
                                            <td class="text-end text-success">$ {{ number_format($c['total_debe'], 2) }}</td>
                                            <td class="text-end text-danger">$ {{ number_format($c['total_haber'], 2) }}</td>
                                            <td class="text-end text-primary fs-6">$ {{ number_format($c['saldo_final'], 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="p-3 text-center text-muted fst-italic text-xs bg-white">
                                <i class="fas fa-info-circle me-1"></i> Cuenta sin movimientos en el periodo seleccionado (Saldo: $ {{ number_format($c['saldo_inicial'], 2) }}).
                            </div>
                        @endif
                    </div>

                </div>
            @endif
        @endforeach
    </div>

</div>

<script>
function filtrarCuentasAuxiliar() {
    const soloConMovs = document.getElementById('checkSoloConMovs').checked;
    const busqueda = (document.getElementById('inputBuscarCuenta').value || '').toLowerCase().trim();
    const tarjetas = document.querySelectorAll('.card-cuenta-afectable');

    tarjetas.forEach(card => {
        const tieneMovs = card.getAttribute('data-tiene-movs') === '1';
        const codigo = (card.getAttribute('data-codigo') || '').toLowerCase();
        const nombre = (card.getAttribute('data-nombre') || '').toLowerCase();
        const textoCompleto = card.innerText.toLowerCase();

        let visible = true;

        if (soloConMovs && !tieneMovs) {
            visible = false;
        }

        if (visible && busqueda.length > 0) {
            if (!codigo.includes(busqueda) && !nombre.includes(busqueda) && !textoCompleto.includes(busqueda)) {
                visible = false;
            }
        }

        card.style.display = visible ? '' : 'none';
    });
}

function colapsarTodas(colapsar) {
    const collapses = document.querySelectorAll('.card-cuenta-afectable .collapse');
    collapses.forEach(el => {
        if (colapsar) {
            el.classList.remove('show');
        } else {
            el.classList.add('show');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    filtrarCuentasAuxiliar();
});
</script>
