# Spec 006 — Resumen Mensual de Utilidad, Desglose Drill-Down y Selector de Años en Módulo de Socios

Estado: completado

## 1. Taxonomía
- **Nombre:** Resumen Mensual de Utilidades, Desglose por Mes y Selector Rápido de Años en Socios
- **Tipo:** Feature / UI & Calculation Refactor
- **Módulo Afectado:** Socios (`/socios`, `SociosController`, `SociosService`, vista `resources/views/socios/index.blade.php`)
- **Prioridad:** Alta

---

## 2. Contexto y Problema

En el ERP SGTSITA, en la ruta `/socios` (pestaña "2. Cálculo de Periodo"), los socios de negocio y administradores analizan la rentabilidad y reparto de utilidades generadas por tractocamiones y viajes durante un periodo de fechas determinado (ej. del 01/08/2025 al 30/09/2026).

Actualmente:
1. **Navegación lenta de fechas:** El componente de fechas (`daterangepicker`) carece de desplegables de mes y año (`showDropdowns: false`). Cuando el usuario necesita evaluar rangos multianuales (por ejemplo, desde agosto 2025 hasta septiembre 2026), tiene que presionar manualmente el botón de fecha anterior/siguiente mes a mes repetidamente.
2. **Falta de consolidación ejecutiva mensual:** Al presionar "Calcular Utilidades", la parte inferior muestra una grilla con un listado plano de cientos de viajes individuales desordenados temporalmente en vez de una visión mensual sintetizada.
3. **Necesidad de desglose drill-down bajo demanda:** Los directivos de empresas como Oliva necesitan visualizar en la tabla principal un **resumen cronológico mensual** (Agosto 2025, Septiembre 2025... hasta Septiembre 2026) con:
   - Mes y Año.
   - Cantidad de Viajes realizados en ese mes.
   - Cantidad de Contenedores movilizados.
   - Utilidad Total obtenida en ese mes.
   - Promedio de utilidad por viaje.
   - Botón de acción interactivo para ver el desglose completo de ese mes.
4. **Consistencia matemática estricta:**
   - La suma acumulada de las utilidades de todos los meses debe cuadrar exactamente ($0.00 de diferencia) con la "Utilidad Bruta Viajes" reportada en las tarjetas del periodo general.
   - Al abrir el detalle/modal de un mes específico (ej. Octubre 2025), la suma de utilidades de sus contenedores y viajes debe cuadrar con el total mensual presentado en la fila de dicho mes.

---

## 3. Historias de Usuario

- **HU-1 (Selector Rápido de Años y Rangos):** Como administrador de socios, al abrir el selector de fechas del periodo, quiero poder seleccionar directamente el año (2024, 2025, 2026, 2027) y el mes desde listas desplegables, o usar botones de atajo como "Año Actual" o "Año Anterior", para no tener que retroceder mes a mes manualmente.
- **HU-2 (Resumen Ejecutivo Mensual):** Como socio o directivo de Oliva, tras calcular el periodo (ej. 01/08/2025 al 30/09/2026), quiero ver una tabla consolidada mes a mes que muestre el volumen de viajes y la utilidad acumulada de cada mes del rango.
- **HU-3 (Auditoría y Detalle Drill-Down por Mes):** Como usuario que revisa un mes en particular (ej. Octubre 2025), quiero presionar "Ver Desglose" en la fila del mes para abrir un modal con todos los viajes y contenedores de ese mes, verificando fechas, clientes, unidades y utilidades unitarias.
- **HU-4 (Consistencia Financiera):** Como responsable de finanzas, requiero la certeza de que la suma de los meses individuales coincide exactamente con la utilidad bruta global de la empresa para dicho periodo.

---

## 4. Requisitos Funcionales (Notación EARS)

### Módulo Selector de Fechas (`daterangepicker`)
- **RF-1 (Selectores de Año y Mes):**
  - CUANDO el usuario hace clic en el input de rango de fechas `#utilidadDaterange`,
  - EL SISTEMA debe desplegar selectores nativos `<select>` de Mes y de Año en la cabecera de ambos calendarios (`showDropdowns: true`), configurando un rango de años desde 2022 hasta el año en curso + 1.
- **RF-2 (Rangos Predefinidos):**
  - EL SISTEMA debe ofrecer atajos rápidos (`ranges`) que incluyan: "Este Mes", "Mes Anterior", "Año Actual", "Año Anterior (2025)", y "Últimos 12 Meses".

### Módulo de Agrupación Financiera (`SociosService`)
- **RF-3 (Estructuración de `meses_resumen` en Backend):**
  - CUANDO `SociosService::calculatePartnerUtility()` procesa el arreglo `$viajesDesglose`,
  - EL SISTEMA debe agrupar automáticamente los viajes por clave mensual `Y-m` en orden cronológico ascendente.
  - Para cada mes, debe calcular:
    - `periodo_clave`: Ej. "2025-08"
    - `mes_nombre`: Ej. "Agosto 2025"
    - `cantidad_viajes`: Conteo total de viajes en ese mes.
    - `cantidad_contenedores`: Conteo de contenedores individuales (contabilizando dobles si aplican).
    - `utilidad_total_mes`: Suma algebraica exacta de `utilidad_viaje` de los viajes de ese mes (redondeada a 2 decimales).
    - `promedio_por_viaje`: Utilidad total del mes dividida entre la cantidad de viajes.
    - `viajes`: Sub-arreglo con los viajes específicos de dicho mes.
  - EL SISTEMA debe incorporar `meses_resumen` en el objeto JSON de respuesta devuelto a la interfaz.

### Interfaz de Usuario y Drill-Down (`resources/views/socios/index.blade.php`)
- **RF-4 (Tabla de Resumen Mensual en Lugar de Lista Plana):**
  - En la pestaña "2. Cálculo de Periodo", en sustitución de la grilla plana de viajes desordenados,
  - EL SISTEMA debe mostrar la grilla o tabla de "Resumen Mensual de Operaciones y Utilidad", con totales al pie de la tabla (Suma de Viajes, Suma de Contenedores y Suma de Utilidad).
- **RF-5 (Modal Drill-Down de Detalle Mensual):**
  - CUANDO el usuario hace clic en "Ver Desglose" en cualquier fila mensual (ej. Octubre 2025),
  - EL SISTEMA debe abrir un modal (`modalDetalleMes`) que presente:
    - Encabezado con el nombre del mes seleccionado y badges de cantidad de viajes y utilidad total.
    - Grilla interactiva (AG Grid / Data Table) con los viajes de ese mes: Fecha de viaje, Contenedor(es), Cliente, Unidad, Estatus y Utilidad de cada viaje.
    - Pie del modal con el totalizado coincidente al 100% con la fila del mes.

---

## 5. Criterios de Aceptación (Definition of Done - DoD)

- [x] **DoD-1 (Selector de Año):** Al abrir el calendario en `/socios`, se pueden cambiar de forma directa el año y el mes mediante selectores desplegables sin tener que avanzar o retroceder mes por mes.
- [x] **DoD-2 (Generación Multianual):** Al seleccionar el rango del `01/08/2025` al `30/09/2026` con el usuario `oliva@gmail.com` y presionar "Calcular Utilidades", se visualiza la tabla mensual con los 14 meses (Agosto 2025 a Septiembre 2026).
- [x] **DoD-3 (Cuadratura General):** La suma total de las utilidades de todos los renglones mensuales mostrados cuadra de forma exacta con la "Utilidad Bruta Viajes" de las tarjetas superiores ($3,650,992.83 en el caso de prueba).
- [x] **DoD-4 (Modal de Desglose):** Al hacer clic en "Ver Desglose" de cualquier mes (ej. Octubre 2025), se abre el modal con sus 10 viajes y la suma de sus utilidades cuadra exactamente con los $311,480.44 del mes.
- [x] **DoD-5 (Compatibilidad):** El guardado de cortes históricos (`guardarCortePeriodo`), el registro de pagos a socios y la exportación de reportes continúan operando sin regresiones.
- [x] **DoD-6 (Verificación MCP):** Verificado visualmente en navegador con el MCP Chrome DevTools autenticado como `oliva@gmail.com`.
