<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Validación de Documentos - Cotización #{{ $cotizacion->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        @page {
            margin: 120px 40px 60px 40px;
        }

        header {
            position: fixed;
            left: 0px;
            top: -100px;
            right: 0px;
            height: 80px;
            border-bottom: 2px solid #47A0CD;
        }

        header .logo-container {
            float: left;
            width: 40%;
        }

        header .title-container {
            float: right;
            width: 60%;
            text-align: right;
        }

        header h2 {
            margin: 5px 0 0 0;
            color: #47A0CD;
            font-size: 18px;
        }

        header p {
            margin: 2px 0;
            font-size: 10px;
            color: #666;
        }

        footer {
            position: fixed;
            left: 0px;
            bottom: -40px;
            right: 0px;
            height: 30px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        footer table {
            width: 100%;
        }

        footer td {
            font-size: 9px;
            color: #777;
        }

        .page-number:after {
            content: counter(page);
        }

        .clear {
            clear: both;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            background-color: #47A0CD;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 3px;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 5px;
            border: 1px solid #eee;
            text-align: left;
            vertical-align: top;
        }

        .info-table td.label {
            font-weight: bold;
            background-color: #f9f9f9;
            width: 25%;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 10px;
        }

        .data-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }

        .text-center {
            text-align: center !important;
        }

        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            color: white;
            font-weight: bold;
            display: inline-block;
        }

        .bg-success {
            background-color: #2dce89;
        }

        .bg-warning {
            background-color: #fb6340;
        }

        .bg-danger {
            background-color: #f5365c;
        }

        .bg-secondary {
            background-color: #8898aa;
        }

        .audit-list {
            margin-top: 10px;
        }

        .audit-item {
            border-bottom: 1px solid #eee;
            padding: 8px 0;
        }

        .audit-header {
            font-weight: bold;
            color: #47A0CD;
            font-size: 10px;
            margin-bottom: 4px;
        }

        .audit-details {
            margin: 0;
            padding-left: 15px;
            font-size: 9.5px;
            color: #555;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo-container">
            @if(file_exists(public_path('img/logo.jpg')))
                <img src="{{ public_path('img/logo.jpg') }}" style="height: 50px;">
            @else
                <h3 style="margin:0; color:#47A0CD;">{{ $configuracion->nombre_sistema ?? 'SGTSITA' }}</h3>
            @endif
        </div>
        <div class="title-container">
            <h2>Reporte de Validación de Documentos</h2>
            <p>Cotización: #{{ $cotizacion->id }} | Contenedor: {{ $documentacion->num_contenedor ?? 'N/A' }}</p>
            <p>Fecha de generación: {{ $fechaCarbon->format('d-m-Y H:i:s') }}</p>
        </div>
        <div class="clear"></div>
    </header>

    <footer>
        <table>
            <tr>
                <td>{{ $configuracion->nombre_sistema ?? 'SGTSITA' }} - Reporte de Validación de Documentos</td>
                <td style="text-align: right;">Página <span class="page-number"></span></td>
            </tr>
        </table>
    </footer>

    <div class="section">
        <div class="section-title">Datos del Viaje y Contenedor</div>
        <table class="info-table">
            <tr>
                <td class="label">Cliente:</td>
                <td>{{ $cotizacion->Cliente->nombre ?? 'N/A' }}</td>
                <td class="label">Núm. Contenedor:</td>
                <td>{{ $documentacion->num_contenedor ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de Viaje:</td>
                <td>{{ ucfirst($cotizacion->tipo_viaje_seleccion ?? 'N/A') }}</td>
                <td class="label">Terminal:</td>
                <td>{{ $documentacion->terminal ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Operador:</td>
                <td>{{ $documentacion->Asignaciones->Operador->nombre ?? 'Sin Asignar' }}</td>
                <td class="label">Placas:</td>
                <td>{{ $documentacion->Asignaciones->Camion->placas ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Validación de Archivos Cargados</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Folio / Valor en BD</th>
                    <th>Nombre Archivo</th>
                    <th class="text-center" style="width: 10%;">Formato</th>
                    <th class="text-center" style="width: 12%;">Tamaño</th>
                    <th class="text-center" style="width: 15%;">Fecha de Carga</th>
                    <th class="text-center" style="width: 15%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($docs as $doc)
                    <tr>
                        <td style="font-weight: bold;">{{ $doc['tipo'] }}</td>
                        <td>{{ $doc['folio'] }}</td>
                        <td style="word-break: break-all;">{{ $doc['archivo'] }}</td>
                        <td class="text-center">{{ $doc['extension'] }}</td>
                        <td class="text-center">{{ $doc['tamanio'] }}</td>
                        <td class="text-center">{{ $doc['fecha_carga'] }}</td>
                        <td class="text-center">
                            @if($doc['existe'])
                                <span class="badge bg-success">Cargado</span>
                            @elseif($doc['archivo'] !== 'No cargado')
                                <span class="badge bg-danger" title="El archivo está registrado pero no existe físicamente en el servidor">Error Archivo</span>
                            @else
                                <span class="badge bg-warning">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($incluirAuditoria)
        <div class="section" style="page-break-before: always;">
            <div class="section-title">Historial de Auditoría de Documentos</div>
            @if(count($auditLogs) > 0)
                <div class="audit-list">
                    @foreach($auditLogs as $log)
                        <div class="audit-item">
                            <div class="audit-header">
                                {{ $log['fecha'] }} - Usuario: {{ $log['usuario'] }} (Acción: {{ ucfirst($log['accion']) }})
                            </div>
                            <ul class="audit-details">
                                @foreach($log['detalles'] as $detalle)
                                    <li>{{ $detalle }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #666; font-style: italic; margin-top: 10px;">No se encontraron registros de auditoría de documentos para este contenedor.</p>
            @endif
        </div>
    @endif

</body>
</html>
