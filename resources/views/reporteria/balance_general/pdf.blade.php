<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance General - PDF</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
            color: #1e3d59;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 10pt;
            color: #555;
            font-style: italic;
        }
        .balance-container {
            width: 100%;
        }
        .column {
            width: 48%;
            float: left;
        }
        .column-left {
            margin-right: 2%;
        }
        .column-right {
            margin-left: 2%;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table th, .table td {
            padding: 6px 8px;
            font-size: 9pt;
            text-align: left;
        }
        .table th {
            font-size: 10pt;
            font-weight: bold;
            border-bottom: 2px solid #ccc;
        }
        .table td {
            border-bottom: 1px solid #eee;
        }
        .section-header {
            background-color: #f7f9fa;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e3d59;
            border-left: 3px solid #1e3d59;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f0f4f8;
        }
        .clear {
            clear: both;
        }
        .summary-box {
            margin-top: 30px;
            border-top: 2px solid #1e3d59;
            padding-top: 15px;
        }
        .summary-box table {
            width: 100%;
        }
        .summary-box td {
            font-size: 11pt;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Balance General</h2>
        <p>Fecha de Corte: {{ \Carbon\Carbon::parse($fechaCorte)->format('d/m/Y') }}</p>
    </div>

    @php
        $activos = collect($balance['rows'])->where('grupo', 'activo');
        $pasivos = collect($balance['rows'])->where('grupo', 'pasivo');
        $capital = collect($balance['rows'])->where('grupo', 'capital');
    @endphp

    <div class="balance-container">
        <!-- LEFT COLUMN: ACTIVOS -->
        <div class="column column-left">
            <table class="table">
                <thead>
                    <tr class="section-header">
                        <th colspan="2">Activos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activos as $row)
                        <tr>
                            <td>{{ $row['concepto'] }}</td>
                            <td class="text-right">${{ number_format($row['valor'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>SUMA ACTIVOS</td>
                        <td class="text-right">${{ number_format($balance['totales']['activo'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- RIGHT COLUMN: PASIVOS & CAPITAL -->
        <div class="column column-right">
            <!-- PASIVOS -->
            <table class="table">
                <thead>
                    <tr class="section-header" style="border-left-color: #e74c3c;">
                        <th colspan="2" style="color: #c0392b;">Pasivos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pasivos as $row)
                        <tr>
                            <td>{{ $row['concepto'] }}</td>
                            <td class="text-right">${{ number_format($row['valor'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>SUMA PASIVOS</td>
                        <td class="text-right" style="color: #c0392b;">${{ number_format($balance['totales']['pasivo'], 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- CAPITAL -->
            <table class="table">
                <thead>
                    <tr class="section-header" style="border-left-color: #2ecc71;">
                        <th colspan="2" style="color: #27ae60;">Capital</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($capital as $row)
                        <tr>
                            <td>{{ $row['concepto'] }}</td>
                            <td class="text-right">${{ number_format($row['valor'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>SUMA CAPITAL</td>
                        <td class="text-right" style="color: #27ae60;">${{ number_format($balance['totales']['capital'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="clear"></div>

    <!-- FOOTER / SUMMARY TOTALS -->
    <div class="summary-box">
        <table>
            <tr>
                <td style="width: 50%; color: #1e3d59;">TOTAL ACTIVOS: ${{ number_format($balance['totales']['activo'], 2) }}</td>
                <td style="width: 50%; text-align: right;">TOTAL PASIVO + CAPITAL: ${{ number_format($balance['totales']['pasivo'] + $balance['totales']['capital'], 2) }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
