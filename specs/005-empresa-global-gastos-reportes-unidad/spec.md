# Spec 005 — Modelo de Empresa Multi-Equipo (H00 Global), Relleno de Datos en Edición de Gastos y Filtro Universal de Unidad en Reportes

Estado: borrador

## 1. Contexto y Modelo de Dominio

### Modelo Tradicional vs. Modelo Multi-Equipo
En el funcionamiento original del ERP:
- **Empresas Tradicionales (1 empresa = 1 equipo):** Cada empresa secundaria (ej. Empresa 1, Empresa 2) contenía típicamente una sola unidad de transporte. Los gastos, viajes, movimientos bancarios y reportes quedaban implícitamente aislados por `id_empresa`, por lo que no existía la necesidad de vincular explícitamente cada registro a un camión determinado.
- **Empresa General / Multi-Equipo (H00-GLOBAL / `id_empresa = 28` / `requiere_unidad_gasto`):**
  - Esta empresa concentra **múltiples equipos** (Equipo 1, Equipo 2, Equipo 3...) bajo el mismo `id_empresa`.
  - A diferencia de las empresas individuales, en H00 Global los viajes, gastos y movimientos bancarios ocurren dentro de la misma empresa corporativa, por lo que **cada operación debe imputarse obligatoriamente a la unidad o equipo correspondiente**.
  - Los equipos de esta empresa se consultan con `where('id_empresa', $idEmpresa)`.

### Los Dos Problemas Detectados:
1. **Falla en la Edición de Gastos (`/gastos`):**
   - Al capturar un gasto, el usuario selecciona a qué unidad corresponde (ya sea mediante el selector de unidad de periodo `#selectPeriodoUnidadNew` habilitado por `requiere_unidad_gasto`, o bajo la opción de Equipo `#selectUnidadesNew`).
   - Sin embargo, al pulsar **Editar** sobre ese gasto:
     - Los datos capturados de la unidad **no se rellenan** en el modal. El selector de unidad aparece vacío o deseleccionado.
     - `GastosService::transformarListado()` no envía `id_equipo` ni los IDs estructurados de las unidades vinculadas.
     - `abrirModalEditar(gasto)` no asigna el valor al selector de unidad ni mapea los IDs en el componente `Choices.js`.
     - `GastosController::update()` no persiste el campo `id_equipo` en la tabla `gastos`.
2. **Falta de Filtro de Unidad (Equipo) en la Reportería:**
   - Dado que H00 Global tiene todas sus unidades conviviendo dentro de la misma empresa, al emitir reportes se mezclan los registros de todos los camiones.
   - Para no perder el control de los registros, se requiere **incorporar el filtro de unidad (equipo)** en todos los reportes del sistema (Viajes, CXC, CXP, Utilidad, Documentos, Gastos por Pagar y en el Grid de Gastos), de modo que el sistema pueda emitir reportes específicos de un solo equipo.

---

## 2. Historias de Usuario

- **HU-1 (Relleno Fiel en Edición de Gastos):** Como capturista o administrador en `/gastos`, quiero que al editar cualquier gasto previamente registrado, el modal se abra mostrando la información capturada originalmente, en especial **la unidad o equipo que se seleccionó**, para poder verificarla o corregirla sin tener que seleccionarla nuevamente desde cero.
- **HU-2 (Persistencia de Unidad en Actualización):** Como usuario de finanzas, quiero que al guardar la edición de un gasto, el sistema mantenga o actualice correctamente la asignación del `id_equipo`, sus imputaciones y vínculos, garantizando que el gasto siga imputado a la unidad correcta.
- **HU-3 (Filtro por Unidad en Reportes para Control Individual):** Como gerente de operaciones de la empresa multi-equipo, quiero poder seleccionar una unidad o equipo específico en los filtros de todos los reportes del sistema (Viajes, CXC, CXP, Utilidad, Documentos, Gastos por Pagar y Gastos), para emitir reportes de un solo equipo y tener control total de los ingresos, egresos y operaciones de cada camión.

---

## 3. Requisitos Funcionales (Notación EARS)

### Módulo Central de Gastos (`/gastos`)
- **RF-1 (Serialización de Unidad en Listado de Gastos):**
  - EL SISTEMA debe incluir en `GastosService::transformarListado()`:
    - `id_equipo`: ID de la unidad individual en `gastos.id_equipo`.
    - `equipo`: Objeto con `id`, `id_equipo`, `placas`, `marca`.
    - `unidades_ids`: Array con los IDs numéricos de las unidades vinculadas (`vinculos.vinculable_id`).
    - `viajes_ids`: Array con los IDs de viajes/asignaciones vinculadas.
    - `impacto`: Tipo de imputación (`periodo`, `viaje`, `cotizacion`).
- **RF-2 (Relleno Automático al Abrir Modal de Edición):**
  - CUANDO el usuario hace clic en "Editar" en un gasto,
  - EL SISTEMA debe ejecutar `abrirModalEditar(gasto)` y:
    - Preseleccionar el radio correspondiente (`Periodo`, `Equipo` o `Viaje`).
    - Si el gasto tiene `id_equipo` o es de tipo `periodo`: asignar el valor a `#selectPeriodoUnidadNew` y mostrar `#aplicacion-periodoUnidadNew`.
    - Si la forma de aplicar es `Equipo`: preseleccionar en `choicesUnidades` los IDs exactos mediante `setChoiceByValue(gasto.unidades_ids)`.
    - Si la forma de aplicar es `Viaje`: preseleccionar en `choicesViajes` los IDs exactos mediante `setChoiceByValue(gasto.viajes_ids)`.
    - Cargar la categoría y subcategoría/concepto vía `cargarConceptosPorCategoria(gasto.categoria_gasto_id, gasto.gasto_concepto_id)`.
    - Precargar el campo `#impacto`, fechas de diferido (si aplica) y cuenta bancaria de retiro.
- **RF-3 (Persistencia de id_equipo en GastosController::update):**
  - CUANDO se actualiza un gasto vía PUT a `/gastos/{id}`,
  - EL SISTEMA debe resolver `$selectedEquipoId`, actualizar el atributo `id_equipo` en el modelo `Gasto`, y regenerar los vínculos e imputaciones de la unidad bajo transacción `DB::transaction()`.
- **RF-4 (Filtro por Unidad en Grid de Gastos):**
  - EL SISTEMA debe agregar un selector de Unidad (`id_equipo`) en la barra superior de filtros de `/gastos`, permitiendo filtrar el grid para consultar exclusivamente los gastos de una unidad seleccionada.

### Módulo de Reportes (`/reporteria`)
- **RF-5 (Filtro de Unidad en Reporte de Viajes / Asignaciones):**
  - En `/reporteria/viajes`, agregar selector de Unidad (`id_camion`), filtrar las asignaciones en `getViajesFiltrados` y `advance_viajes` por `asignaciones.id_camion`, e incluir la unidad en la exportación Excel/PDF.
- **RF-6 (Filtro de Unidad en Cuentas por Cobrar CXC):**
  - En `/reporteria/cxc`, agregar selector de Unidad (`id_unidad`), y filtrar en `CuentasCobrarService::getCuentasPorCobrar()` por `asignaciones.id_camion`.
- **RF-7 (Filtro de Unidad en Cuentas por Pagar CXP):**
  - En `/reporteria/cxp`, agregar selector de Unidad (`id_unidad`), y filtrar en `advance_cxp` por `asignaciones.id_camion`.
- **RF-8 (Filtro de Unidad en Reporte de Resultados / Utilidades):**
  - En `/reporteria/utilidad`, agregar selector de Unidad (`id_equipo`) en la cabecera, y filtrar viajes y gastos unitarios en `ReporteriaService::getContenedorUtilidad()`.
- **RF-9 (Filtro de Unidad en Reporte de Documentación):**
  - En `/reporteria/documentos`, agregar selector de Unidad (`id_unidad`), y filtrar por `asignaciones.id_camion`.
- **RF-10 (Filtro de Unidad en Gastos por Pagar GXP):**
  - En `/reporteria/gastos-pagar`, agregar selector de Unidad (`id_unidad`), y filtrar en `getGastosPorPagarData` por `asignaciones.id_camion` o `gastos.id_equipo`.

---

## 4. Criterios de Aceptación (DoD)

1. [ ] Al abrir el modal de edición de un gasto previamente registrado, se rellena fielmente la unidad que fue capturada (`#selectPeriodoUnidadNew` o `#selectUnidadesNew`).
2. [ ] Al modificar la unidad y guardar el gasto editado, la nueva unidad se persiste correctamente en `gastos.id_equipo` y en sus tablas relacionales.
3. [ ] El grid de `/gastos` permite filtrar todos los gastos por una unidad específica.
4. [ ] Todos los reportes principales (Viajes, CXC, CXP, Utilidad, Documentos, Gastos por Pagar) cuentan con selector de Unidad (Equipo) alimentado con los equipos de la empresa (`id_empresa = 28` o de la empresa en sesión).
5. [ ] Al seleccionar una unidad en cualquier reporte, la consulta devuelve únicamente las operaciones, viajes, cuentas o gastos asociados a ese equipo específico.
6. [ ] Verificación en navegador mediante Chrome DevTools (cero errores en consola JavaScript y peticiones de red 200 OK).
