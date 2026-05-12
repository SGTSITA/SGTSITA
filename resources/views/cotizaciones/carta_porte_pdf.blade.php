<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Orden de Entrega / Datos Carta Porte {{ $numContenedor }}</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            font-size: 11pt;
            line-height: 1.4;
        }

        .encabezado {
            border: 2px solid #0b2a4a;
            padding: 10px;
            margin-bottom: 12px;
        }

        .brand-title {
            color: #0b2a4a;
            font-weight: bold;
            font-size: 14pt;
        }

        .ref {
            text-align: right;
            font-size: 11pt;
        }

        .tarjeta {
            border: 1px solid #e5e7eb;
            margin-bottom: 12px;
        }

        .tarjeta .titulo {
            background: #0b2a4a;
            color: #fff;
            padding: 6px;
            font-weight: bold;
        }

        .contenido {
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f3f4f6;
            color: #0b2a4a;
            padding: 6px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }

        td {
            padding: 6px;
            border: 1px solid #e5e7eb;
        }

        .dl-table td:first-child {
            width: 200px;
            font-weight: bold;
            color: #374151;
        }

        .nota {
            font-size: 10pt;
            color: #374151;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            padding: 6px;
            margin-top: 8px;
        }

        .small {
            font-size: 10pt;
        }

        .text-azul {
            color: #0b2a4a;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            border: 1px solid #0b2a4a;
            color: #0b2a4a;
            padding: 2px 6px;
            font-size: 9pt;
        }
    </style>
</head>

<body>

    <!-- ENCABEZADO -->
    <div class="encabezado">
        <table>
            <tr>
                <td class="brand-title">
                    Orden de Entrega / Datos Carta Porte
                </td>
                <td class="ref">
                    REFERENCIA:
                    <strong>{{ $numContenedor }}</strong>
                </td>
            </tr>
        </table>
        <p class="small">Documento de control con fines informativos y logísticos.</p>
    </div>

    <!-- IMPORTADOR -->
    <div class="tarjeta">
        <div class="titulo">Datos del Importador</div>
        <div class="contenido">
            <table class="dl-table">
                <tr>
                    <td>Importador</td>
                    <td>{{ $subCliente->nombre }}</td>
                </tr>
                <tr>
                    <td>RFC</td>
                    <td>{{ $subCliente->rfc }}</td>
                </tr>
                <tr>
                    <td>Domicilio de Carta Porte</td>
                    <td>{{ $subCliente->direccion }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- DATOS ADUANALES -->
    <div class="tarjeta">
        <div class="titulo">Datos Aduanales y de Mercancía</div>
        <div class="contenido">
            <table>
                <thead>
                    <tr>
                        <th>Fracción</th>
                        <th>Clave SAT</th>
                        <th>Pedimento</th>
                        <th>Clase de Pedimento</th>
                        <th>Cantidad</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $cotizaciones->cp_fraccion }}</td>
                        <td>{{ $cotizaciones->cp_clave_sat }}</td>
                        <td>{{ $cotizaciones->cp_pedimento }}</td>
                        <td><span class="badge">{{ $cotizaciones->cp_clase_ped }}</span></td>
                        <td>{{ $cotizaciones->cp_cantidad }}</td>
                        <td>{{ $cotizaciones->cp_moneda }}{{ $cotizaciones->cp_valor }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- LOGISTICA -->
    <div class="tarjeta">
        <div class="titulo">Información Logística</div>
        <div class="contenido">
            <table class="dl-table">
                <tr>
                    <td>Puerto / Lugar de salida</td>
                    <td>{{ $cotizaciones->origen }}</td>
                </tr>
                <tr>
                    <td>Domicilio de entrega</td>
                    <td>
                        {{ $cotizaciones->direccion_entrega }}<br>
                        <span class="small" style="word-break: keep-all; white-space: nowrap;">
                            https://maps.google.com/?q={{ $cotizaciones->latitud }},{{ $cotizaciones->longitud }}
                        </span>

                    </td>
                </tr>
                <tr>
                    <td>Contacto (entrega)</td>
                    <td>{{ $cotizaciones->cp_contacto_entrega }}</td>
                </tr>
                <tr>
                    <td>Fecha tentativa de entrega</td>
                    <td>{{ $cotizaciones->cp_fecha_tentativa_entrega }}</td>
                </tr>
                <tr>
                    <td>Hora tentativa</td>
                    <td>{{ $cotizaciones->cp_hora_tentativa_entrega }}hrs</td>
                </tr>
                <tr>
                    <td>Comentarios</td>
                    <td>{{ $cotizaciones->cp_comentarios }}</td>
                </tr>
            </table>

            <div class="nota">
                <span class="text-azul">Nota:</span>
                La fecha y hora indicadas son tentativas y están sujetas a cambios por condiciones operativas o de
                tránsito.
            </div>
        </div>
    </div>

    <!-- PIE -->
    <p class="small">
        Este documento se genera para fines de control logístico. Cualquier discrepancia deberá notificarse de inmediato
        al coordinador correspondiente.
    </p>

</body>

</html>
