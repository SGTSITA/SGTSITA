# Plan de Implementación — Spec 005: Modelo Multi-Equipo, Relleno de Datos en Edición de Gastos y Filtro Universal de Unidad en Reportes

## 1. Arquitectura y Enfoque Técnico

El objetivo es resolver la falta de persistencia y precarga de unidades en la edición de gastos, e implementar el filtro de unidad (equipo) en toda la reportería para soportar el modelo multi-equipo de la empresa H00 Global (`id_empresa = 28`), donde conviven múltiples unidades dentro de una misma empresa corporativa.

### A. Catálogo de Equipos por Empresa
- En cada empresa (incluyendo H00 Global `id_empresa = 28`), los equipos se consultan por su pertenencia a la empresa actual:
  `Equipo::where('id_empresa', $idEmpresa)->where('tipo', 'Tractos / Camiones')->orderBy('id_equipo')->get()`.
- En H00 Global, al tener múltiples equipos registrados bajo `id_empresa = 28`, este selector permite imputar cada viaje, gasto o movimiento bancario a la unidad puntual correspondiente.

### B. Corrección del Relleno de Datos en Edición de Gastos (`/gastos`)
1. **Serialización Backend (`app/Services/GastosService.php`):**
   - En `transformarListado()`, exponer explícitamente:
     - `id_equipo`: ID del equipo asignado en `gastos.id_equipo`.
     - `equipo`: Objeto con `id`, `id_equipo`, `placas`, `marca`.
     - `unidades_ids`: Array numérico con los IDs vinculados de las unidades (`vinculos->where('tipo_vinculo', 'unidad')->pluck('vinculable_id')`).
     - `viajes_ids`: Array numérico con los IDs de viajes vinculados.
     - `impacto`: Tipo de imputación registrada (`periodo`, `viaje`, `cotizacion`).
   - En `transformarVinculos()`, retornar `vinculable_id` y `vinculable_type`.
2. **Controlador (`app/Http/Controllers/GastosController.php`):**
   - En `update()`:
     - Extraer `$selectedEquipoId = $request->id_equipo ?: ($request->equipo_id ?: (is_array($request->unidades) && count($request->unidades) === 1 ? $request->unidades[0] : null));`.
     - Incluir `'id_equipo' => $selectedEquipoId` en `$storeData` para que el servicio `GastosService::registrar()` actualice la columna `id_equipo` de la tabla `gastos`.
     - Manejar el caso de unidad única (`id_equipo`) para generar correctamente los registros en `gasto_vinculos` y `gasto_imputaciones`.
3. **Frontend (`resources/views/gastos/index.blade.php`):**
   - En `abrirModalEditar(gasto)`:
     - Identificar la forma de aplicar:
       - Si es `periodo` o tiene `id_equipo`: asignar `document.getElementById('selectPeriodoUnidadNew').value = gasto.id_equipo || ''`, y mostrar `#aplicacion-periodoUnidadNew`.
       - Si es `unidad` ("Equipo"): asignar en `choicesUnidades.setChoiceByValue(gasto.unidades_ids.map(String))` usando IDs exactos.
       - Si es `viaje`: asignar en `choicesViajes.setChoiceByValue(gasto.viajes_ids.map(String))`.
     - Preseleccionar categoría y llamar a `cargarConceptosPorCategoria(gasto.categoria_gasto_id, gasto.gasto_concepto_id)`.
     - Precargar impacto (`#impacto`).
     - Configurar diferido o contado adecuadamente.
   - En la barra superior de filtros de `/gastos`:
     - Agregar `<select id="gastosNewEquipo">` con las unidades de la empresa.
     - En `GastosService::listar()`, soportar filtro `id_equipo` (en `gastos.id_equipo` o en vínculos de unidad).

### C. Filtro Universal de Unidad (Equipo) en Reportes
1. **Reporte de Viajes / Asignaciones (`/reporteria/viajes`):**
   - Controlador: Enviar `$equipos` (filtrados por `id_empresa`) en `index_viajes` y `advance_viajes`.
   - Query: En `getViajesFiltrados` y `advance_viajes`, filtrar por `asignaciones.id_camion = $request->id_camion`.
   - Vista: Agregar selector `#selectUnidadViajes` en `resources/views/reporteria/asignaciones/index.blade.php`.
   - Exportación: Incluir parámetro en `export_viajes`.
2. **Reporte de Cuentas por Cobrar CXC (`/reporteria/cxc`):**
   - Controlador: Enviar `$equipos` a `reporteria/cxc/index.blade.php`.
   - Servicio: En `CuentasCobrarService::getCuentasPorCobrar()`, si viene `id_unidad`, filtrar donde exista `asignaciones.id_camion = $filtros['id_unidad']`.
   - Vista: Agregar `<select name="id_unidad">` en el formulario de filtros.
3. **Reporte de Cuentas por Pagar CXP (`/reporteria/cxp`):**
   - Controlador: Enviar `$equipos` a `reporteria/cxp/index.blade.php`.
   - Query: En `advance_cxp`, agregar filtro `where('asignaciones.id_camion', $request->id_unidad)`.
   - Vista: Agregar `<select name="id_unidad">` en el formulario de filtros.
4. **Reporte de Utilidades (`/reporteria/utilidad`):**
   - Controlador: Enviar `$equipos` a `reporteria/utilidad/index.blade.php`.
   - Servicio: En `ReporteriaService::getContenedorUtilidad()`, aceptar `?int $idEquipo = null`, filtrar `$viajesQuery->where('a.id_camion', $idEquipo)` y `$gastosUnidad->where('id_camion', $idEquipo)`.
   - Vista: Agregar `<select id="selEquipoUtilidad">` en la barra superior junto al daterange y proveedor.
5. **Reporte de Documentación (`/reporteria/documentos`):**
   - Controlador: Enviar `$equipos` a `index_documentos` y `advance_documentos`.
   - Query: Filtrar por `asignaciones.id_camion = $request->id_unidad`.
   - Vista: Agregar selector `<select name="id_unidad">`.
6. **Reporte de Gastos por Pagar GXP (`/reporteria/gastos-pagar`):**
   - Controlador: Enviar `$equipos` a `index_gxp`.
   - Query: En `getGastosPorPagarData`, filtrar por `asignacion.id_camion = $request->id_unidad` o `gastos.id_equipo = $request->id_unidad`.
   - Vista: Agregar selector de unidad en `reporteria/gxp/index.blade.php`.

---

## 2. Puntos de Decisión y Buenas Prácticas

1. **Aislamiento Multi-Equipo:** Toda consulta de equipos mantiene el ámbito por empresa `id_empresa`, garantizando que en H00 Global (`id_empresa = 28`) se listen sus camiones asignados para imputación y filtros.
2. **Atomicidad:** Toda actualización de gastos con sus vínculos e imputaciones en `GastosController::update` opera dentro de `DB::transaction()`.
3. **Optimización de consultas:** Usar `with()` y `where()` sobre la columna indexada `asignaciones.id_camion` y `gastos.id_equipo`.
4. **Verificación visual con Chrome DevTools:** Comprobar la carga de los selects, apertura del modal de edición, persistencia de datos y filtros de los reportes.
