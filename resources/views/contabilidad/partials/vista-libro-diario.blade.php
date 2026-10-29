{{-- VISTA PREVIA WEB: LIBRO DIARIO DE PÓLIZAS CONTABLES [LD-06] --}}
<div class="libro-diario-container">

    {{-- BARRA DE CONTROLES LOCALES DEL DIARIO --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between p-3 mb-4 rounded bg-light border shadow-sm gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark text-white px-3 py-2 text-xs">
                <i class="fas fa-file-invoice text-warning me-1"></i> Total Pólizas: <strong>{{ number_format($libroDiario['total_polizas']) }}</strong>
            </span>
            @if($libroDiario['cuadrado'])
                <span class="badge bg-success text-white px-3 py-2 text-xs">
                    <i class="fas fa-check-circle me-1"></i> Sumas Iguales Cuadradas ($ 0.00)
                </span>
            @else
                <span class="badge bg-danger text-white px-3 py-2 text-xs">
                    <i class="fas fa-exclamation-triangle me-1"></i> Descuadre en Sumas
                </span>
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="max-width: 380px;">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="inputBuscarDiario" class="form-control" placeholder="Buscar por póliza, cuenta, contenedor..." onkeyup="filtrarLibroDiario()">
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()" title="Imprimir Libro Diario">
                <i class="fas fa-print me-1"></i> Imprimir
            </button>
        </div>
    </div>

    {{-- KPI RESUMEN DEL LIBRO DIARIO --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border border-primary bg-primary-light p-3 rounded text-center shadow-sm">
                <span class="text-xs text-uppercase text-primary fw-bold"><i class="fas fa-layer-group me-1"></i>Total de Pólizas</span>
                <h4 class="fw-bolder text-primary mb-0 mt-1">{{ number_format($libroDiario['total_polizas']) }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-success bg-success-light p-3 rounded text-center shadow-sm">
                <span class="text-xs text-uppercase text-success fw-bold"><i class="fas fa-arrow-down me-1"></i>Suma Total Debe</span>
                <h4 class="fw-bolder text-success mb-0 mt-1">$ {{ number_format($libroDiario['total_debe'], 2) }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-danger bg-danger-light p-3 rounded text-center shadow-sm">
                <span class="text-xs text-uppercase text-danger fw-bold"><i class="fas fa-arrow-up me-1"></i>Suma Total Haber</span>
                <h4 class="fw-bolder text-danger mb-0 mt-1">$ {{ number_format($libroDiario['total_haber'], 2) }}</h4>
            </div>
        </div>
    </div>

    {{-- TABLA COMPLETA DE PÓLIZAS DEL DIARIO --}}
    <div class="table-responsive shadow-sm border rounded">
        <table class="table table-sm table-hover align-middle mb-0 text-xs" id="tablaLibroDiario">
            <thead class="table-dark sticky-top">
                <tr>
                    <th style="width: 90px;">Fecha</th>
                    <th style="width: 80px;">Tipo</th>
                    <th style="width: 140px;">Póliza #</th>
                    <th style="width: 120px;">Cuenta CONAC</th>
                    <th>Nombre de la Cuenta / Partida</th>
                    <th style="width: 180px;">Referencia / Contenedor</th>
                    <th class="text-end" style="width: 120px;">Debe (Cargos)</th>
                    <th class="text-end" style="width: 120px;">Haber (Abonos)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($libroDiario['polizas'] as $p)
                    {{-- ENCABEZADO DE LA PÓLIZA --}}
                    <tr class="table-light fw-bold border-top border-2 border-primary poliza-fila-header"
                        data-poliza="{{ strtolower($p['numero']) }}"
                        data-concepto="{{ strtolower($p['concepto']) }}">
                        <td class="text-center font-monospace">{{ $p['fecha_formato'] }}</td>
                        <td class="text-center">
                            @php
                                $badgeColor = match($p['tipo']) {
                                    'INGRESO'  => 'bg-success',
                                    'EGRESO'   => 'bg-danger',
                                    'APERTURA' => 'bg-info',
                                    default    => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeColor }} text-white text-xxs">{{ $p['tipo'] }}</span>
                        </td>
                        <td class="font-monospace text-primary fs-6">{{ $p['numero'] }}</td>
                        <td colspan="3" class="text-dark">
                            <i class="fas fa-file-alt text-primary me-1"></i>{{ $p['concepto'] }}
                            @if($p['equipo'])
                                <span class="badge bg-light text-dark border ms-2 text-xxs">
                                    <i class="fas fa-truck text-secondary me-1"></i>{{ $p['equipo'] }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end text-success">$ {{ number_format($p['total_debe'], 2) }}</td>
                        <td class="text-end text-danger">$ {{ number_format($p['total_haber'], 2) }}</td>
                    </tr>

                    {{-- PARTIDAS / MOVIMIENTOS DE LA PÓLIZA --}}
                    @foreach ($p['partidas'] as $part)
                        <tr class="poliza-fila-partida"
                            data-poliza="{{ strtolower($p['numero']) }}"
                            data-cuenta="{{ strtolower($part['cuenta_codigo']) }}"
                            data-nombre="{{ strtolower($part['cuenta_nombre']) }}"
                            data-ref="{{ strtolower($part['referencia']) }}">
                            <td colspan="3" class="text-muted text-end text-xxs border-0">
                                <em>Línea {{ $part['linea'] }}</em>
                            </td>
                            <td class="font-monospace fw-bold text-dark text-center">
                                {{ $part['cuenta_codigo'] }}
                            </td>
                            <td>
                                <strong class="text-dark">{{ $part['cuenta_nombre'] }}</strong>
                                @if($part['concepto_detalle'] && $part['concepto_detalle'] !== $p['concepto'])
                                    <div class="text-muted text-xxs mt-0">{{ $part['concepto_detalle'] }}</div>
                                @endif
                            </td>
                            <td>
                                @if($part['contenedor'])
                                    <span class="badge bg-info text-dark font-monospace text-xxs me-1">
                                        <i class="fas fa-box me-1"></i>{{ $part['contenedor'] }}
                                    </span>
                                @endif
                                <span class="text-muted">{{ str_replace('['.$part['contenedor'].']', '', $part['referencia']) }}</span>
                            </td>
                            <td class="text-end text-success fw-semibold">
                                {{ $part['debe'] > 0 ? '$ ' . number_format($part['debe'], 2) : '-' }}
                            </td>
                            <td class="text-end text-danger fw-semibold">
                                {{ $part['haber'] > 0 ? '$ ' . number_format($part['haber'], 2) : '-' }}
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted fst-italic">
                            <i class="fas fa-info-circle me-1"></i> No se encontraron pólizas registradas para el periodo y filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-dark fw-bold border-top">
                <tr class="fs-6">
                    <td colspan="6" class="text-end text-uppercase">SUMAS TOTALES DEL DIARIO:</td>
                    <td class="text-end text-success">$ {{ number_format($libroDiario['total_debe'], 2) }}</td>
                    <td class="text-end text-danger">$ {{ number_format($libroDiario['total_haber'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>

<script>
function filtrarLibroDiario() {
    const q = (document.getElementById('inputBuscarDiario').value || '').toLowerCase().trim();
    const headers = document.querySelectorAll('.poliza-fila-header');

    headers.forEach(h => {
        const poliza = h.getAttribute('data-poliza') || '';
        const concepto = h.getAttribute('data-concepto') || '';
        const partidas = document.querySelectorAll(`.poliza-fila-partida[data-poliza="${poliza}"]`);

        let coincideHeader = poliza.includes(q) || concepto.includes(q);
        let coincideAlgunaPartida = false;

        partidas.forEach(p => {
            const cuenta = p.getAttribute('data-cuenta') || '';
            const nombre = p.getAttribute('data-nombre') || '';
            const ref = p.getAttribute('data-ref') || '';
            if (cuenta.includes(q) || nombre.includes(q) || ref.includes(q)) {
                coincideAlgunaPartida = true;
            }
        });

        const mostrar = (q.length === 0) || coincideHeader || coincideAlgunaPartida;
        h.style.display = mostrar ? '' : 'none';
        partidas.forEach(p => {
            p.style.display = mostrar ? '' : 'none';
        });
    });
}
</script>
