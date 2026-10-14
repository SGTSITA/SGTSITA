# Tareas de Implementación — Spec 006

- [x] **Tarea 1 — Backend: Generación y Agrupación de `meses_resumen` en `SociosService`**
  - **RF:** RF-3
  - **Archivos:** `app/Services/SociosService.php`
  - **Hecho cuando:** `SociosService::calculatePartnerUtility()` retorne el arreglo `'meses_resumen'` ordenado cronológicamente con nombre de mes, cantidad de viajes, cantidad de contenedores, utilidad total mensual, promedio por viaje y el desglose de viajes por mes, con cuadratura exacta frente a `total_utilidad_bruta_viajes`.

- [x] **Tarea 2 — Frontend: Configuración de Selectores de Año y Menú de Rangos en `daterangepicker`**
  - **RF:** RF-1, RF-2
  - **Archivos:** `resources/views/socios/index.blade.php`
  - **Hecho cuando:** El input `#utilidadDaterange` (y `#historialDaterange`) incluya `showDropdowns: true`, `minYear: 2022`, `maxYear: moment().year() + 2` y accesos directos de rangos (`ranges`), permitiendo seleccionar directamente el año sin paginar mes a mes.

- [x] **Tarea 3 — Frontend: Sustitución de Grilla Plana por Grilla de Resumen Mensual**
  - **RF:** RF-4
  - **Archivos:** `resources/views/socios/index.blade.php`
  - **Hecho cuando:** En la pestaña "2. Cálculo de Periodo" se renderice la grilla `gridMesesResumen` con las columnas Mes, Viajes, Contenedores, Utilidad Mes, Promedio por Viaje y botón "Ver Desglose", alimentada por `meses_resumen`.

- [x] **Tarea 4 — Frontend: Modal Drill-Down de Detalle de Viajes y Contenedores por Mes**
  - **RF:** RF-5
  - **Archivos:** `resources/views/socios/index.blade.php`
  - **Hecho cuando:** Al hacer clic en "Ver Desglose" de un mes, se abra el modal Bootstrap 5 `#modalDetalleMes` mostrando la grilla de todos los viajes y contenedores de ese mes y sus totales cuadrados.

- [x] **Tarea 5 — Verificación E2E en Navegador con Chrome DevTools**
  - **RF:** DoD-1 al DoD-6
  - **Archivos:** Flujo completo en navegador (`http://localhost:8080/socios`)
  - **Hecho cuando:** Se pruebe con el usuario `oliva@gmail.com` en el rango 01/08/2025 al 30/09/2026, validando la tabla mensual de 14 meses, la cuadratura contra $3,650,992.83, el modal de Octubre 2025 ($311,480.44), y ausencia de errores de consola.

- [x] **Tarea 6 — Refinamiento Financiero: Gastos Indirectos e Imputación Mensual Cuadrada**
  - **Archivos:** `app/Services/SociosService.php`, `resources/views/socios/index.blade.php`
  - **Hecho cuando:** Se agrupen los gastos generales/indirectos por mes (`fecha_imputacion`), se calculen las columnas `Total Utilidad Bruta`, `Gastos Indirectos` ($2,599,012.91) y `Utilidad Mensual` ($1,051,979.92), cuadrando al centavo con las tarjetas superiores tanto en la grilla mensual como en el modal de desglose.

- [x] **Tarea 7 — Simplificación de Grilla: Retiro de Contenedores y Promedio por Viaje**
  - **Archivos:** `resources/views/socios/index.blade.php`
  - **Hecho cuando:** Se eliminen las columnas redundantes "Contenedores" y "Promedio / Viaje", dejando exclusivamente las columnas clave (Mes, Cantidad Viajes, Total Utilidad Bruta, Gastos Indirectos, Utilidad Mensual, Acciones) y la fila inferior fija (`TOTALES`) perfectamente alineada.

- [x] **Tarea 8 — Desacoplamiento de Calendarios (`linkedCalendars: false`)**
  - **Archivos:** `resources/views/socios/index.blade.php`
  - **Hecho cuando:** Al configurar `linkedCalendars: false` en `daterangepicker`, cambiar el año o mes en el calendario derecho no mueva ni altere la vista del calendario izquierdo.
