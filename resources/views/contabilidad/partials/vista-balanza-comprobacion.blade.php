{{-- ENCABEZADO FORMAL BALANZA DE COMPROBACIÓN --}}
<div class="text-center border-bottom pb-4 mb-4">
    <span class="badge bg-light text-primary border px-3 py-1 mb-2 fw-semibold" style="letter-spacing: 1px;">
        SISTEMA GESTIÓN TRANSPORTE CONTABLE • FORMATO BC-03 • CATÁLOGO GENERAL DE CUENTAS
    </span>
    <h3 class="fw-bolder text-dark mb-1 text-uppercase" style="letter-spacing: 1.5px;">BALANZA DE COMPROBACIÓN</h3>
    
    <h5 class="text-secondary fw-normal mb-1">
        @if ($equipoSeleccionado)
            UNIDAD: <strong class="text-dark">{{ $equipoSeleccionado->texto_select }}</strong>
        @else
            CONSOLIDADO GLOBAL DE UNIDADES
        @endif
    </h5>

    <p class="text-muted text-sm mb-0">
        PERIODO: DEL <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($balanza['fecha_inicio'] ?? $fechaCorte)->isoFormat('D [DE] MMMM [DE] YYYY') }}</span>
        AL <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($fechaCorte)->isoFormat('D [DE] MMMM [DE] YYYY') }}</span>
        @if ($esAdmin && in_array('todas', $seleccionEmpresas))
            <span class="badge bg-secondary text-white ms-2">Consolidado Multi-Empresa</span>
        @endif
    </p>

    {{-- Status de Cuadre de Balanza --}}
        @if ($balanza['cuadrado'])
            <span class="badge bg-success-light text-success border border-success px-3 py-1 text-xs">
                <i class="fas fa-check-circle me-1"></i> Balanza Cuadrada en Sumas Iguales: Total Debe = Total Haber ($ {{ number_format($balanza['totales']['debe'], 2) }})
            </span>
        @else
            <span class="badge bg-danger-light text-danger border border-danger px-3 py-1 text-xs">
                <i class="fas fa-exclamation-triangle me-1"></i> Descuadre en Movimientos: Diferencia de $ {{ number_format($balanza['diferencia_movimientos'] ?? 0, 2) }}
            </span>
        @endif
    </div>
</div>

{{-- BARRA DE BÚSQUEDA Y CONTROL DE LA BALANZA --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary text-white px-2 py-1">
            <i class="fas fa-list-ol me-1"></i> {{ count($balanza['cuentas'] ?? []) }} Cuentas Contables
        </span>
        <small class="text-muted text-xs">Mostrando cuentas con movimientos o saldo al corte.</small>
    </div>
    <div style="min-width: 280px; max-width: 350px;">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
            <input type="text" id="inputBuscarBalanza" class="form-control" placeholder="Buscar cuenta o código CONAC...">
        </div>
    </div>
</div>

{{-- TABLA FORMAL CONAC BC-03 --}}
<div class="table-responsive shadow-sm rounded border bg-white mb-4" style="max-height: 650px; overflow-y: auto;">
    <table class="table table-sm table-bordered table-hover align-middle mb-0" id="tablaBalanzaComprobacion">
        <thead class="table-dark sticky-top" style="z-index: 10;">
            <tr>
                <th rowspan="2" class="text-center align-middle" style="width: 100px;">CÓDIGO</th>
                <th rowspan="2" class="align-middle" style="min-width: 240px;">NOMBRE DE LA CUENTA</th>
                <th rowspan="2" class="text-center align-middle" style="width: 50px;">NAT</th>
                <th colspan="2" class="text-center py-1 bg-secondary text-white border-bottom border-light">SALDO INICIAL</th>
                <th colspan="2" class="text-center py-1 bg-primary text-white border-bottom border-light">MOVIMIENTOS</th>
                <th colspan="2" class="text-center py-1 bg-dark text-white border-bottom border-light">SALDO FINAL</th>
            </tr>
            <tr>
                <th class="text-end py-1 text-xs" style="width: 110px;">DEUDOR</th>
                <th class="text-end py-1 text-xs" style="width: 110px;">ACREEDOR</th>
                <th class="text-end py-1 text-xs text-primary bg-light" style="width: 115px;">DEBE (+)</th>
                <th class="text-end py-1 text-xs text-danger bg-light" style="width: 115px;">HABER (-)</th>
                <th class="text-end py-1 text-xs" style="width: 115px;">DEUDOR</th>
                <th class="text-end py-1 text-xs" style="width: 115px;">ACREEDOR</th>
            </tr>
        </thead>
        <tbody id="tbodyBalanza">
            @forelse ($balanza['cuentas'] as $c)
                @php
                    $isMajor = $c['nivel'] <= 2;
                    $paddingLeft = ($c['nivel'] - 1) * 16;
                    $tieneMovimientos = ($c['debe'] > 0 || $c['haber'] > 0);
                @endphp
                <tr class="fila-balanza {{ $isMajor ? 'table-light fw-bold' : '' }}" data-codigo="{{ $c['codigo'] }}" data-nombre="{{ strtolower($c['nombre']) }}">
                    <td class="text-center {{ $isMajor ? 'fw-bold text-dark' : 'text-muted' }}">
                        <code>{{ $c['codigo'] }}</code>
                    </td>
                    <td style="padding-left: {{ 8 + $paddingLeft }}px;">
                        @if ($isMajor)
                            <strong class="text-dark">{{ $c['nombre'] }}</strong>
                        @else
                            <span class="text-secondary">{{ $c['nombre'] }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $c['naturaleza'] === 'D' ? 'bg-primary' : 'bg-warning text-dark' }}" style="font-size: 9px;">
                            {{ $c['naturaleza'] }}
                        </span>
                    </td>
                    <td class="text-end text-xs {{ $c['saldo_ini_deudor'] > 0 ? 'text-dark fw-semibold' : 'text-muted' }}">
                        {{ $c['saldo_ini_deudor'] > 0 ? number_format($c['saldo_ini_deudor'], 2) : '-' }}
                    </td>
                    <td class="text-end text-xs {{ $c['saldo_ini_acreedor'] > 0 ? 'text-dark fw-semibold' : 'text-muted' }}">
                        {{ $c['saldo_ini_acreedor'] > 0 ? number_format($c['saldo_ini_acreedor'], 2) : '-' }}
                    </td>
                    <td class="text-end text-xs {{ $c['debe'] > 0 ? 'text-primary fw-bold' : 'text-muted' }}">
                        {{ $c['debe'] > 0 ? number_format($c['debe'], 2) : '-' }}
                    </td>
                    <td class="text-end text-xs {{ $c['haber'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                        {{ $c['haber'] > 0 ? number_format($c['haber'], 2) : '-' }}
                    </td>
                    <td class="text-end text-xs {{ $c['saldo_fin_deudor'] > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                        {{ $c['saldo_fin_deudor'] > 0 ? number_format($c['saldo_fin_deudor'], 2) : '-' }}
                    </td>
                    <td class="text-end text-xs {{ $c['saldo_fin_acreedor'] > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                        {{ $c['saldo_fin_acreedor'] > 0 ? number_format($c['saldo_fin_acreedor'], 2) : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">No se encontraron cuentas contables con movimientos en este periodo.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="table-dark sticky-bottom" style="z-index: 10;">
            <tr class="fw-bolder">
                <th colspan="3" class="text-uppercase text-center py-2">SUMAS IGUALES:</th>
                <th class="text-end py-2 text-xs">$ {{ number_format($balanza['totales']['saldo_ini_deudor'], 2) }}</th>
                <th class="text-end py-2 text-xs">$ {{ number_format($balanza['totales']['saldo_ini_acreedor'], 2) }}</th>
                <th class="text-end py-2 text-xs text-info fs-6">$ {{ number_format($balanza['totales']['debe'], 2) }}</th>
                <th class="text-end py-2 text-xs text-info fs-6">$ {{ number_format($balanza['totales']['haber'], 2) }}</th>
                <th class="text-end py-2 text-xs text-success">$ {{ number_format($balanza['totales']['saldo_fin_deudor'], 2) }}</th>
                <th class="text-end py-2 text-xs text-success">$ {{ number_format($balanza['totales']['saldo_fin_acreedor'], 2) }}</th>
            </tr>
        </tfoot>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputBuscar = document.getElementById('inputBuscarBalanza');
    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', function () {
            const query = this.value.toLowerCase().trim();
            document.querySelectorAll('#tbodyBalanza tr.fila-balanza').forEach(tr => {
                const codigo = tr.dataset.codigo || '';
                const nombre = tr.dataset.nombre || '';
                if (codigo.includes(query) || nombre.includes(query)) {
                    tr.style.display = '';
                } else {
                    tr.style.display = 'none';
                }
            });
        });
    }
});
</script>
