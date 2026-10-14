<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance General - {{ $equipoSeleccionado ? $equipoSeleccionado->texto_select : 'Consolidado' }} al {{ $fechaCorte }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #fff;
            color: #111;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 12mm; size: letter portrait; }
        }
        .header-title {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-title h4 {
            font-size: 16px;
            font-weight: 800;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-title h5 {
            font-size: 13px;
            margin: 3px 0;
            font-weight: 600;
        }
        .header-title p {
            font-size: 10px;
            margin: 0;
            color: #555;
        }
        .table-balance th, .table-balance td {
            padding: 3px 6px;
            font-size: 10px;
        }
        .table-balance tfoot th {
            border-top: 1.5px solid #222;
            border-bottom: 2px solid #222;
            font-weight: bold;
        }
        .section-header {
            background-color: #f1f3f5;
            font-weight: bold;
            padding: 4px 6px;
            border: 1px solid #dee2e6;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-size: 10px;
        }
        .signature-box {
            text-align: center;
            margin-top: 30px;
            font-size: 9px;
        }
        .signature-line {
            border-top: 1px solid #444;
            padding-top: 4px;
            margin: 0 20px;
        }
    </style>
</head>
<body>

    {{-- Botón de Imprimir visible solo en pantalla --}}
    <div class="no-print d-flex justify-content-between align-items-center mb-3 p-2 bg-light border rounded">
        <div>
            <strong>Reporte Contable CONAC: {{ ($tipoReporte ?? '') === 'estado_resultados' ? 'Estado de Resultados' : 'Balance General' }}</strong>
            <span class="text-muted ms-2">(Usa el botón para Imprimir o Guardar como PDF)</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Imprimir / Guardar como PDF
            </button>
            <a href="{{ request()->fullUrlWithQuery(['download' => 1]) }}" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-file-pdf me-1"></i> Descargar Archivo PDF
            </a>
        </div>
    </div>

    {{-- ENCABEZADO FORMAL --}}
    <div class="header-title">
        <p style="font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #777;">SISTEMA CONTABLE CONAC • PARTIDA DOBLE</p>
        <h4>{{ $nombresEmpresas }}</h4>
        <h5>{{ ($tipoReporte ?? '') === 'estado_resultados' ? 'ESTADO DE ACTIVIDADES / RESULTADOS' : 'BALANCE GENERAL (ESTADO DE SITUACIÓN FINANCIERA)' }}</h5>
        <p>
            @if ($equipoSeleccionado)
                UNIDAD: <strong>{{ $equipoSeleccionado->texto_select }}</strong> &nbsp;|&nbsp;
            @else
                CONSOLIDADO DE UNIDADES &nbsp;|&nbsp;
            @endif
            AL {{ \Carbon\Carbon::parse($fechaCorte)->isoFormat('D [DE] MMMM [DE] YYYY') }}
        </p>
    </div>

    {{-- DOS COLUMNAS BALANCEADAS --}}
    <div class="row g-3">

        {{-- ACTIVO --}}
        <div class="col-6">
            <div class="section-header">ACTIVO</div>
            <table class="table table-sm table-borderless table-balance w-100 mb-0">
                <tbody>
                    @foreach ($balance['activo']['items'] as $item)
                        <tr>
                            <td class="{{ $item['es_calculado'] ? 'fw-bold' : 'ps-3 text-secondary' }}">
                                {{ $item['concepto'] }}
                            </td>
                            <td class="text-end fw-semibold" style="width: 100px;">
                                $ {{ number_format($item['monto'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-uppercase">SUMA DEL ACTIVO</th>
                        <th class="text-end">$ {{ number_format($balance['activo']['total'], 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- PASIVO Y PATRIMONIO --}}
        <div class="col-6">
            <div class="section-header">PASIVO</div>
            <table class="table table-sm table-borderless table-balance w-100 mb-2">
                <tbody>
                    @foreach ($balance['pasivo']['items'] as $item)
                        <tr>
                            <td class="{{ $item['es_calculado'] ? 'fw-bold' : 'ps-3 text-secondary' }}">
                                {{ $item['concepto'] }}
                            </td>
                            <td class="text-end fw-semibold" style="width: 100px;">
                                $ {{ number_format($item['monto'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-uppercase" style="font-size: 9px;">TOTAL PASIVO</th>
                        <th class="text-end" style="font-size: 9px;">$ {{ number_format($balance['pasivo']['total'], 2) }}</th>
                    </tr>
                </tfoot>
            </table>

            <div class="section-header mt-2">CAPITAL CONTABLE / PATRIMONIO</div>
            <table class="table table-sm table-borderless table-balance w-100 mb-0">
                <tbody>
                    @foreach ($balance['patrimonio']['items'] as $item)
                        <tr>
                            <td class="{{ $item['es_calculado'] ? 'fw-bold' : 'ps-3 text-secondary' }}">
                                {{ $item['concepto'] }}
                            </td>
                            <td class="text-end fw-semibold" style="width: 100px;">
                                $ {{ number_format($item['monto'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-uppercase" style="font-size: 9px;">TOTAL CAPITAL CONTABLE</th>
                        <th class="text-end" style="font-size: 9px;">$ {{ number_format($balance['patrimonio']['total'], 2) }}</th>
                    </tr>
                    <tr>
                        <th class="text-uppercase">SUMA DEL PASIVO Y CAPITAL</th>
                        <th class="text-end">$ {{ number_format($balance['total_pasivo_patrimonio'], 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    {{-- ESTADO DE RESULTADOS INFERIOR --}}
    <div class="mt-3 pt-2" style="border-top: 1.5px dashed #aaa;">
        <div class="section-header">RESUMEN DEL ESTADO DE RESULTADOS (DEL EJERCICIO AL CORTE)</div>
        <table class="table table-sm table-bordered table-balance w-100 mb-0 bg-white">
            <tbody>
                <tr>
                    <td class="ps-3 fw-bold">(+) Ingresos por Fletes y Servicios</td>
                    <td class="text-end fw-bold" style="width: 120px;">$ {{ number_format($balance['estado_resultados']['ingresos_fletes'], 2) }}</td>
                </tr>
                <tr style="background-color: #f0f7ff;">
                    <td class="ps-3 fw-bold text-primary">
                        (+) Intereses Bancarios Ganados (CONAC 4.3.1.1 - Separado de Fletes)
                    </td>
                    <td class="text-end fw-bold text-primary" style="width: 120px;">$ {{ number_format($balance['estado_resultados']['intereses_ganados'], 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-3 text-danger">(-) Gastos Operativos y de Funcionamiento</td>
                    <td class="text-end text-danger fw-bold" style="width: 120px;">- $ {{ number_format($balance['estado_resultados']['gastos_totales'], 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr style="background-color: #f8f9fa;">
                    <th class="ps-3 text-uppercase fw-bold">(=) UTILIDAD NETA DEL EJERCICIO</th>
                    <th class="text-end fw-bold" style="width: 120px;">$ {{ number_format($balance['estado_resultados']['utilidad_neta'], 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- FIRMAS FORMALES --}}
    <div class="row signature-box">
        <div class="col-4">
            <div class="signature-line">
                ELABORÓ<br>
                <strong>CONTABILIDAD</strong>
            </div>
        </div>
        <div class="col-4">
            <div class="signature-line">
                REVISÓ<br>
                <strong>AUDITORÍA / FINANZAS</strong>
            </div>
        </div>
        <div class="col-4">
            <div class="signature-line">
                AUTORIZÓ<br>
                <strong>DIRECCIÓN GENERAL</strong>
            </div>
        </div>
    </div>

</body>
</html>
