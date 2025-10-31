<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Orden de Entrega / Datos Carta Porte {{$numContenedor}}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    /* Pensado para DOMPDF: evita fuentes web y usa colores sobrios (gris y azul marino) */
    :root{
      --azul:#0b2a4a;         /* azul marino */
      --gris-900:#1f2937;     /* texto principal */
      --gris-700:#374151;     /* subtítulos */
      --gris-200:#e5e7eb;     /* bordes suaves */
      --gris-100:#f3f4f6;     /* fondos sutiles */
    }
    @page{
      margin: 15mm 18mm 18mm 10mm; /* top right bottom left */
    }
    *{ box-sizing:border-box; }
    html, body{
      font-family:  Arial, Helvetica, sans-serif, "DejaVu Sans"; /* DejaVu Sans soporta caracteres especiales en DOMPDF */
      color: var(--gris-900);
      line-height: 1.35;
      font-size: 11pt;
    }
    .encabezado{
      border: 2px solid var(--azul);
      padding: 14px 16px;
      border-radius: 8px;
      margin-bottom: 14px;
    }
    .brand-line{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
    }
    .brand-title{
      color: var(--azul);
      font-weight: 700;
      font-size: 16pt;
      letter-spacing: .5px;
    }
    .ref{
      text-align:right;
      font-size: 11pt;
      color: var(--gris-700);
    }
    .ref strong{
      color: var(--azul);
      font-size: 12pt;
    }

    .tarjeta{
      border: 1px solid var(--gris-200);
      border-radius: 8px;
      margin: 10px 0 14px;
      overflow:hidden;
    }
    .tarjeta .titulo{
      background: var(--azul);
      color: #fff;
      padding: 8px 12px;
      font-weight: 700;
      font-size: 12.5pt;
      letter-spacing: .2px;
    }
    .contenido{
      padding: 10px 12px;
      background: #fff;
    }

    /* Listas tipo definición alineadas */
    .dl{
      display: grid;
      grid-template-columns: 200px 1fr;
      gap: 6px 14px;
    }
    .dl dt{
      color: var(--gris-700);
      font-weight: 600;
    }
    .dl dd{
      margin: 0;
    }

    /* Tabla de mercancía / datos aduanales */
    table{
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      font-size: 11pt;
    }
    thead th{
      background: var(--gris-100);
      color: var(--azul);
      text-align: left;
      border-bottom: 1px solid var(--gris-200);
      padding: 8px 8px;
      font-weight: 700;
    }
    tbody td{
      padding: 8px 8px;
      border-bottom: 1px solid var(--gris-200);
      vertical-align: top;
    }

    .nota{
      font-size: 10.5pt;
      color: var(--gris-700);
      background: var(--gris-100);
      border: 1px solid var(--gris-200);
      border-radius: 6px;
      padding: 8px 10px;
      margin-top: 8px;
    }

    /* Pie de firma */
    .firmas{
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 22px;
      margin-top: 24px;
      page-break-inside: avoid;
    }
    .firma{
      border-top: 2px solid var(--azul);
      padding-top: 10px;
      min-height: 70px;
    }
    .firma .rol{
      color: var(--gris-700);
      font-size: 10.5pt;
    }

    /* Pequeños utilitarios */
    .mt-0{ margin-top:0; }
    .mb-0{ margin-bottom:0; }
    .mb-6{ margin-bottom:6px; }
    .mb-10{ margin-bottom:10px; }
    .small{ font-size: 10.5pt; }
    .strong{ font-weight:700; }
    .text-azul{ color: var(--azul); }
    .text-right{ text-align:right; }
    .badge{
      display:inline-block;
      border:1px solid var(--azul);
      color: var(--azul);
      padding: 2px 8px;
      border-radius: 999px;
      font-size: 10.5pt;
      letter-spacing:.3px;
    }

    /* Evitar quebrar bloques importantes en salto de página */
    .avoid-break{ page-break-inside: avoid; }
  </style>
</head>
<body>

  <!-- ENCABEZADO -->
  <section class="encabezado avoid-break">
    <div class="brand-line">
      <div class="brand-title">Orden de Entrega / Datos Carta Porte</div>
      <div class="ref">
        REFERENCIA:&nbsp;<strong>{{$numContenedor}}</strong>
      </div>
    </div>
    <p class="mb-0 small">Documento de control con fines informativos y logísticos.</p>
  </section>

  <!-- IMPORTADOR -->
  <section class="tarjeta avoid-break">
    <div class="titulo">Datos del Importador</div>
    <div class="contenido">
      <dl class="dl">
        <dt>Importador</dt><dd>{{$subCliente->nombre}}</dd>
        <dt>RFC</dt><dd>{{$subCliente->rfc}}</dd>
        <dt>Domicilio de Carta Porte</dt>
        <dd>{{$subCliente->direccion}}</dd>
      </dl>
    </div>
  </section>

  <!-- DATOS ADUANALES / MERCANCÍA -->
  <section class="tarjeta avoid-break">
    <div class="titulo">Datos Aduanales y de Mercancía</div>
    <div class="contenido">
      <table aria-label="Datos aduanales y mercancía">
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
            <td>{{$cotizaciones->cp_fraccion}}</td>
            <td>{{$cotizaciones->cp_clave_sat}}</td>
            <td>{{$cotizaciones->cp_pedimento}}</td>
            <td><span class="badge">{{$cotizaciones->cp_clase_ped}}</span></td>
            <td>{{$cotizaciones->cp_cantidad}}</td>
            <td>{{$cotizaciones->cp_moneda}}{{$cotizaciones->cp_valor}}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- LOGÍSTICA -->
  <section class="tarjeta avoid-break">
    <div class="titulo">Información Logística</div>
    <div class="contenido">
      <dl class="dl">
        <dt>Puerto / Lugar de salida</dt><dd>{{$cotizaciones->origen}}</dd>
        <dt>Domicilio de entrega</dt>
        <dd>
          {{$cotizaciones->direccion_entrega}}.<br />
          <span class="small">Mapa: <a href="https://www.google.com/maps/search/?api=1&query={{$cotizaciones->latitud}},{{$cotizaciones->longitud}}" target="_blank">https://www.google.com/maps/search/?api=1&query={{$cotizaciones->latitud}},{{$cotizaciones->longitud}}</a></span>
        </dd>
        <dt>Contacto (entrega)</dt><dd>{{$cotizaciones->cp_contacto_entrega}}</dd>
        <dt>Fecha tentativa de entrega</dt><dd>{{$cotizaciones->cp_fecha_tentativa_entrega}}</dd>
        <dt>Hora tentativa</dt><dd>{{$cotizaciones->cp_hora_tentativa_entrega}}hrs</dd>
        <dt>Comentarios</dt><dd>{{$cotizaciones->cp_comentarios}}</dd>
      </dl>

      <div class="nota">
        <strong class="text-azul">Nota:</strong> La fecha y hora indicadas son tentativas y están sujetas a cambios por condiciones operativas o de tránsito.
      </div>
    </div>
  </section>

  <!-- PIE -->
  <p class="small" style="margin-top:8px;">
    Este documento se genera para fines de control logístico. Cualquier discrepancia deberá notificarse de inmediato al coordinador correspondiente.
  </p>

</body>
</html>