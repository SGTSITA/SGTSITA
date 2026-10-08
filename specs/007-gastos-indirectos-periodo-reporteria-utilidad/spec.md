# Spec 007 — Visualización de Gastos Indirectos del Periodo sin Contenedor en Reporte de Resultados (/reporteria/utilidad)

Estado: completado

## 1. Taxonomía
- **Nombre:** Soporte de Gastos Indirectos por Periodo sin Dependencia de Selección de Contenedor
- **Tipo:** Bugfix / Functional Refactor
- **Módulo Afectado:** Reportería / Utilidad (`/reporteria/utilidad`, `ReporteriaController`, `public/js/sgt/reporteria/rpt-utilidades.js`, `resources/views/reporteria/utilidad/index.blade.php`)
- **Prioridad:** Alta

---

## 2. Contexto y Problema

En el ERP SGTSITA, en la ruta `/reporteria/utilidad` ("Reporte de Resultados"), los administradores y directivos analizan la rentabilidad por viaje y los gastos operativos. 

Previamente, el botón superior **"Ver Gastos"** (`#btnVerDetalle`) invocaba `verDetalleGastos()`, la cual exigía obligatoriamente que el usuario seleccionara un contenedor de la grilla (`apiGrid.getSelectedRows()`). Si no había fila seleccionada, se mostraba una alerta bloqueante SweetAlert2: *"Seleccione un contenedor: Debe seleccionar un contenedor de la lista"*.

### Caso de Falla Detectado
En empresas como **H20-TERA** (usuario `tera@gmail.com`), existen periodos (por ejemplo, **Junio 2026**: `2026-06-01` al `2026-06-30`) donde **no hubo viajes operativos** (`apiGrid` vacío con 0 registros), pero **sí se devengaron gastos indirectos o de empresa** (en este caso 8 gastos que suman **$76,913.30**: seguros, permisos de carga, derechos de placas, TAG, combustible diésel, urea, etc.).

Al no haber viajes en la grilla, el usuario no podía seleccionar ningún contenedor, quedando completamente impedido de consultar y auditar los gastos indirectos del mes desde la interfaz.

---

## 3. Historias de Usuario

- **HU-1 (Consulta de Gastos sin Viajes):** Como administrador de empresa (ej. H20-TERA), cuando consulto un periodo sin viajes (ej. Junio 2026), quiero presionar el botón "Ver Gastos" y ver de inmediato el desglose de los gastos indirectos y generales del mes, sin recibir advertencias ni bloqueos que me obliguen a seleccionar un contenedor.
- **HU-2 (Desglose Completo de Gastos Indirectos):** Como auditor financiero, quiero ver para cada gasto indirecto su concepto, fecha de aplicación en formato amigable, categoría del gasto, estatus de pago, método de imputación y monto exacto en pesos mexicanos, con la suma total del periodo calculada.
- **HU-3 (Navegación Dual cuando sí hay Viajes):** Como usuario que analiza un viaje seleccionado, quiero poder ver los gastos directos del contenedor y opcionalmente alternar a los gastos indirectos del periodo mediante pestañas interactivas sin salir del modal.
- **HU-4 (Selector Rápido de Año y Mes):** Como usuario de reportería, quiero que el selector de fechas me permita escoger el año (ej. 2026) y el mes mediante menús desplegables (`showDropdowns: true`) con calendarios desacoplados (`linkedCalendars: false`) para no paginar mes a mes manualmente.

---

## 4. Requisitos Funcionales (Notación EARS)

### Módulo de Interfaz y Lógica (`public/js/sgt/reporteria/rpt-utilidades.js`)
- **RF-1 (Desacoplamiento de Validación de Contenedor):**
  - CUANDO el usuario hace clic en "Ver Gastos" (`#btnVerDetalle`),
  - EL SISTEMA debe verificar si existe una fila seleccionada en `apiGrid`:
    - **Si hay un contenedor seleccionado:** Activar el tab de *Gastos de Viaje* y poblar sus gastos operativos directos.
    - **Si NO hay contenedor seleccionado (o la grilla no tiene registros):** Activar automáticamente la vista de *Gastos Indirectos del Periodo* pasando las fechas (`startDate` y `endDate`) del filtro actual, sin lanzar alertas de error.
- **RF-2 (Renderizado de Gastos Indirectos):**
  - EL SISTEMA debe listar los elementos de `window.latestGastosGenerales` mostrando:
    - Concepto o motivo (`concepto || motivo_gasto || categoria`).
    - Fecha en español formateada (`obtenerFechaFormateada`).
    - Categoría (`categoria.categoria || tipo_gasto`).
    - Método de imputación (`metodo_imputacion`) y estatus (`estatus`).
    - Monto formateado con `moneyFormat()`.
    - Total acumulado en badge superior (`badgeTotalGastosModal`) y pie de modal.
- **RF-3 (Control Dinámico de Pestañas):**
  - SI no hay un contenedor seleccionado, EL SISTEMA debe ocultar la barra de pestañas para presentar una vista limpia centrada en los gastos indirectos.
  - SI hay un contenedor seleccionado, EL SISTEMA debe mostrar las pestañas permitiendo alternar entre *Gastos de Viaje* y *Gastos Indirectos*.

### Módulo de Vista y Calendario (`resources/views/reporteria/utilidad/index.blade.php`)
- **RF-4 (Modal Moderno con Pestañas y Scroll):**
  - EL SISTEMA debe estructurar `#miModal` con ancho adaptable (`580px`), cabecera con icono, subtítulo del periodo o contenedor, contenedor scrollable (`max-height: 420px; overflow-y: auto`) y pie con resumen de conteo y botón de cierre.
- **RF-5 (Navegación de Años en `daterangepicker`):**
  - EL SISTEMA debe configurar `#daterange` con `showDropdowns: true` y `linkedCalendars: false`, más accesos rápidos de "Año Actual" y "Año Anterior", permitiendo saltar inmediatamente a Junio 2026 o cualquier otro ejercicio.

---

## 5. Criterios de Aceptación (Definition of Done - DoD)

- [x] **DoD-1 (Sin Bloqueos):** Al consultar Junio 2026 en empresa Tera (0 viajes), hacer clic en "Ver Gastos" no muestra la alerta "Seleccione un contenedor".
- [x] **DoD-2 (Carga de Gastos Indirectos):** El modal se abre y muestra los 8 gastos indirectos imputados en Junio 2026 por un total de $76,913.30.
- [x] **DoD-3 (Detalle de Gastos):** Cada elemento muestra concepto, fecha formateada, categoría, método y monto formateado.
- [x] **DoD-4 (Soporte Dual con Viajes):** Si se selecciona un contenedor, el modal muestra los gastos del viaje y permite ver también los gastos indirectos.
- [x] **DoD-5 (Selector de Fechas Desacoplado):** El selector de fechas en `/reporteria/utilidad` permite seleccionar año y mes desde selects desplegables y no altera la vista del calendario izquierdo al cambiar el derecho.
- [x] **DoD-6 (Verificación en Navegador):** Verificado de punta a punta con Chrome DevTools en `http://localhost:8080/reporteria/utilidad` con el usuario `tera@gmail.com`.
