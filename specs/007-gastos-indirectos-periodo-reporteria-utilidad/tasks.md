# Tareas de Implementación — Spec 007

- [x] **Tarea 1 — Frontend Blade: Reestructuración y Estilos de `#miModal`**
  - **RF:** RF-4
  - **Archivos:** `resources/views/reporteria/utilidad/index.blade.php`
  - **Hecho cuando:** `#miModal` cuente con cabecera moderna, barra de pestañas `#gastosModalTabs`, lista scrollable `#infoGastos`, badges de totales y pie con botón de cierre.

- [x] **Tarea 2 — Frontend Blade: Configuración de Selectores en `daterangepicker`**
  - **RF:** RF-5
  - **Archivos:** `resources/views/reporteria/utilidad/index.blade.php`
  - **Hecho cuando:** `#daterange` tenga `showDropdowns: true`, `linkedCalendars: false` y rangos para año actual y año anterior.

- [x] **Tarea 3 — Frontend JS: Conexión Asíncrona y Almacenamiento de Gastos Generales**
  - **RF:** RF-1, RF-2
  - **Archivos:** `public/js/sgt/reporteria/rpt-utilidades.js`
  - **Hecho cuando:** `getUtilidadesViajes()` retorne la promesa de `$.ajax` y guarde `window.latestGastosGenerales`.

- [x] **Tarea 4 — Frontend JS: Eliminación del Bloqueo y Renderizado de Gastos Indirectos**
  - **RF:** RF-1, RF-2, RF-3
  - **Archivos:** `public/js/sgt/reporteria/rpt-utilidades.js`
  - **Hecho cuando:** `verDetalleGastos()` evalúe si hay contenedor seleccionado y, de no haberlo, abra de inmediato la vista de gastos indirectos con `renderizarGastosIndirectos()`, calculando el total y ocultando la barra de pestañas para una presentación limpia.

- [x] **Tarea 5 — Verificación E2E con Chrome DevTools**
  - **RF:** DoD-1 al DoD-6
  - **Archivos:** Flujo completo en navegador (`http://localhost:8080/reporteria/utilidad`)
  - **Hecho cuando:** Con `tera@gmail.com` en Junio 2026 se pulse "Ver Gastos", aparezcan los 8 gastos por $76,913.30 sin errores de consola y se valide la captura visual.
