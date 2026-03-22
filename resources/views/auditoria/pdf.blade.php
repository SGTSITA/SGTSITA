<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Auditoría</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            border-bottom: 2px solid #444;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 12px;
            color: #777;
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 4px;
        }

        .badge {
            padding: 3px 8px;
            color: #fff;
            border-radius: 4px;
            font-size: 10px;
        }

        .created {
            background: #28a745;
        }

        .updated {
            background: #ffc107;
            color: #000;
        }

        .deleted {
            background: #dc3545;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f2f2f2;
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        td {
            border: 1px solid #ddd;
            padding: 6px;
        }

        .old {
            color: #c0392b;
        }

        .new {
            color: #27ae60;
        }

        .changed {
            background: #fff8e1;
        }

        .payload {
            margin-top: 15px;
        }

        .payload pre {
            white-space: pre-wrap;
            word-break: break-all;
            font-size: 10px;
            background: #111;
            color: #0f0;
            padding: 10px;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            text-align: right;
            color: #999;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <div class="title">Reporte de Auditoría SITA Software</div>
        <div class="subtitle">Sistema de control de cambios</div>
    </div>

    <!-- INFO -->
    <table class="info-table">
        <tr>
            <td><b>Acción:</b></td>
            <td>
                <span class="badge {{ $data['accion'] }}">
                    {{ strtoupper($data['accion']) }}
                </span>
            </td>

            <td><b>Fecha:</b></td>
            <td>{{ $data['fecha'] }}</td>
        </tr>
        <tr>
            <td><b>Modelo:</b></td>
            <td>{{ $data['modelo'] }} #{{ $data['modelo_id'] }}</td>

            <td><b>Usuario:</b></td>
            <td>{{ $data['usuario'] }}</td>
        </tr>
        <tr>
            <td><b>Empresa:</b></td>
            <td>{{ $data['empresa'] }}</td>

            <td><b>Referencia:</b></td>
            <td>{{ $data['referencia'] }}</td>
        </tr>
    </table>

    <!-- CAMBIOS -->
    <h4>Cambios realizados</h4>

    <table>
        <thead>
            <tr>
                <th>Campo</th>
                <th>Antes</th>
                <th>Después</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['cambios'] as $campo => $val)
                @php
                    $old = $val['old'];
                    $new = $val['new'];
                    $changed = $old !== $new;
                @endphp
                <tr class="{{ $changed ? 'changed' : '' }}">
                    <td><b>{{ str_replace('_', ' ', ucfirst($campo)) }}</b></td>
                    <td class="old">{{ $old }}</td>
                    <td class="new">{{ $new }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- PAYLOAD -->
    @if ($data['payload'])
        <div class="payload">
            <h4>Datos enviados (Request)</h4>

            <table>
                <thead>
                    <tr>
                        <th>Campo</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['payload'] as $key => $val)
                        <tr>
                            <td>{{ $key }}</td>
                            <td style="word-break: break-all;">
                                {{ is_array($val) ? json_encode($val) : $val }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        Generado automáticamente el {{ now() }}
    </div>

</body>

</html>
