# Tareas de Implementación — Spec 005: Modelo Multi-Equipo, Relleno de Datos en Edición de Gastos y Filtro Universal de Unidad en Reportes

- [x] **Fase 1: Corrección Integral de Captura, Edición y Relleno de Gastos**
  - [x] 1.1 Enriquecer `GastosService::transformarListado()` retornando `id_equipo`, objeto `equipo`, `unidades_ids`, `viajes_ids`, `impacto`.
  - [x] 1.2 Agregar `vinculable_id` y `vinculable_type` en `GastosService::transformarVinculos()`.
  - [x] 1.3 Refactorizar `GastosController::update()` para asignar y actualizar `id_equipo`, y soportar compatibilidad con asignación directa de equipo único.
  - [x] 1.4 Corregir `abrirModalEditar(gasto)` en `resources/views/gastos/index.blade.php` para preseleccionar la unidad en `#selectPeriodoUnidadNew` o en `choicesUnidades` usando IDs numéricos exactos.
  - [x] 1.5 Asegurar que la categoría y subcategoría se precarguen y queden seleccionadas en el modal de edición.
  - [x] 1.6 Agregar filtro por Unidad (`id_equipo`) en la barra superior de filtros de `/gastos` y soportarlo en `GastosService::listar()`.

- [x] **Fase 2: Filtro Universal de Unidad (Equipo) en Todos los Reportes**
  - [x] 2.1 **Reporte de Viajes / Asignaciones (`/reporteria/viajes`):**
    - [x] Agregar selector de Unidad en `resources/views/reporteria/asignaciones/index.blade.php`.
    - [x] Enviar `$equipos` y filtrar por `asignaciones.id_camion` en `ReporteriaController::getViajesFiltrados`, `advance_viajes` y `export_viajes`.
  - [x] 2.2 **Reporte de Cuentas por Cobrar CXC (`/reporteria/cotizaciones/cxc`):**
    - [x] Agregar selector de Unidad en `resources/views/reporteria/cxc/index.blade.php`.
    - [x] Enviar `$equipos` en `ReporteriaController::index` y `advance`.
    - [x] Filtrar por `asignaciones.id_camion` en `CuentasCobrarService::getCuentasPorCobrar()`.
  - [x] 2.3 **Reporte de Cuentas por Pagar CXP (`/reporteria/cotizaciones/cxp`):**
    - [x] Agregar selector de Unidad en `resources/views/reporteria/cxp/index.blade.php`.
    - [x] Enviar `$equipos` en `ReporteriaController::index_cxp` y `advance_cxp`.
    - [x] Filtrar por `asignaciones.id_camion` en la consulta de `advance_cxp` y `export_cxp`.
  - [x] 2.4 **Reporte de Resultados / Utilidades (`/reporteria/utilidad`):**
    - [x] Agregar selector de Unidad en la barra superior de `resources/views/reporteria/utilidad/index.blade.php`.
    - [x] Enviar `$equipos` en `ReporteriaController::index_utilidad`.
    - [x] Soportar `$idEquipo` en `ReporteriaService::getContenedorUtilidad()` y filtrar viajes y gastos de unidad.
  - [x] 2.5 **Reporte de Documentación (`/reporteria/documentos`):**
    - [x] Agregar selector de Unidad en `resources/views/reporteria/documentos/index.blade.php`.
    - [x] Enviar `$equipos` en `ReporteriaController::index_documentos` y `advance_documentos`.
    - [x] Filtrar por `asignaciones.id_camion` en la consulta base de documentos.
  - [x] 2.6 **Reporte de Gastos por Pagar GXP (`/reporteria/gastos-pagar`):**
    - [x] Agregar selector de Unidad en `resources/views/reporteria/gxp/index.blade.php`.
    - [x] Filtrar por `asignaciones.id_camion` o `gastos.id_equipo` en `ReporteriaController::getGastosPorPagarData`.

- [x] **Fase 3: Verificación Integral y Pruebas con Chrome DevTools**
  - [x] 3.1 Probar creación y guardado de un gasto imputado a unidad en H00 Global (`id_empresa = 28`).
  - [x] 3.2 Probar edición del gasto: verificar que la unidad seleccionada se rellene automáticamente en el modal.
  - [x] 3.3 Modificar la unidad o los datos y guardar la edición; comprobar la persistencia en base de datos.
  - [x] 3.4 Abrir cada reporte en el navegador con Chrome DevTools, filtrar por un equipo específico y comprobar que los datos corresponden a esa unidad.
  - [x] 3.5 Actualizar `MEMORY.md` y presentar el resumen final de resultados.
