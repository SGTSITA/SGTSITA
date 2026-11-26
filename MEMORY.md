# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~100 líneas).

## Estado actual
- **Spec 030 (Columna Proveedor y Copiado en Seguimiento + Reporte Unificado y Reclasificación de Gastos):**
  - Columna PROVEEDOR agregada en `/cotizaciones/index` (AG Grid) inmediatamente después de `Destino`, visible exclusivamente para usuarios con permiso `'ver-proveedor-seguimiento'` (con migración de permiso Spatie).
  - Copiado de celdas habilitado en AG Grid (`enableCellTextSelection: true`, `ensureDomOrder: true`, CSS `user-select: text !important`) más botón de copiado rápido al portapapeles en `# Contenedor` con feedback Toast; ancho ampliado a `width: 230, minWidth: 215` y `white-space: nowrap` para dar holgura a folios largos (como `TCNU5981912-IS340` y superiores) evitando desbordes o saltos de línea indeseados en la grilla.
  - Reporte Unificado de Gastos en Matriz Pivote en vista dedicada estandarizada Metronic (`/gastos/reporte-unificado`), abierta en nueva pestaña con sincronización automática del periodo activo en pantalla; calendario unificado con `initSgtDateRange` (exactas mismas opciones, rangos rápidos 'Últimos 7 días', 'Últimos 30 días', 'Este mes', 'Mes anterior', 'Personalizado' y límites de BD que en `/gastos`); switch interactivo `checkOmitirVacias` para ocultar columnas en cero (mostrando solo categorías con movimientos como en el Excel del contador) o alternar a ver todas; selector de agrupación dinámica (`Subcategorías`, `Concepto libre`, y modo compacto `Solo Categorías` de 2 columnas: Categoría e Importe Total tanto en web como en Excel sin columnas redundantes); empty-state con botones de acción rápida y exportación Excel 100% sincronizada con los filtros.
  - Catálogo interactivo de Categorías y Subcategorías en `/gastos` con creación y edición in-situ (`CategoriasGastos` y `GastoConcepto`); desactivado el focus-trap de Bootstrap (`data-bs-focus="false"`) para permitir foco y escritura fluida en los prompts de edición de SweetAlert2.
  - Módulo y endpoints de Reclasificación de Gastos individuales y masivos (`reclasificarGasto` y `reclasificarMultiple`).
- **Spec 029 (Columna Cliente y Filtro Multi-Check Sincronizado — `/cotizaciones/costos-multiple`):**
  - Columna CLIENTE añadida después de `# CONTENEDOR` en ambas pestañas Handsontable con renderizador truncado (`width: 170px`) y fijación izquierda (`fixedColumnsLeft: 2`).
  - Barra de Filtros compacta en una sola fila continua; checkboxes nítidos (`19x19px` azul con palomita blanca), resaltado integral de fila seleccionada (`#ebf5ff`, borde `#b9dcff`).
  - Sincronización bi-tabular reactiva: cambios en `#dropdownContenedoresWrapper` o en filtros de columna `# CONTENEDOR` se replican en ambas grillas con prevención de bucles.
  - Botón condicional "Guardar Todo" en el tab header si ambas grillas tienen cambios; modal SweetAlert estandarizado para prevenir pérdida de datos en `keydown` (F5, Ctrl+R), links `<a>`, consultar/limpiar y navegación atrás (`popstate`).
- **Spec 028 (Gestión Inteligente y Reasignación de Gastos al Deshacer Planeación — `/planeaciones`):**
  - Detección elemental vs completa: si no hay movimientos bancarios, desprograma directo.
  - Dinero para viaje a Préstamo: si el operador no devolvió el anticipo, se preserva el movimiento bancario y se crea `Prestamo` a su nombre con póliza contable (`1.1.2.3.001` vs `Bancos`) para amortización en liquidación.
  - Diésel en tanque como precarga: marcado con `es_precarga_combustible = true`, `id_equipo_precarga` y trazabilidad histórica en JSON (`historial_reasignaciones`).
  - Detección y absorción de precarga en nueva planeación (`GET /planeaciones/equipos/{id}/combustible-precargado` y `/resumen-gastos-cancelacion`) tanto en step como en edit sin generar nueva salida bancaria.
- **Segregación y Gestión de Comprobaciones en Editar Planeación (`/planeaciones/viajes/editar/{id}`):**
  - Comprobaciones del chofer (`tipo_gasto = 'operador'` o comprobaciones legacy) segregadas de campos editables de planeación; no exigen banco. Botón de eliminación/rechazo (`/cotizaciones/gastos-operador/eliminar`) sincronizado con `gastos`, `viaticos_operadores` y `gastos_operadores`. Sueldo y Dinero de viaje excluidos de esta tabla. Acceso flexibilizado para empresa asignada o cliente.
- **Captura Ágil de Campos Numéricos y Moneda en Planeación (Step Propios y Editar Planeación):**
  - Implementada la misma estrategia ágil de captura de cotizaciones en `public/js/sgt/common.js`: al enfocar (`focus`) un campo con valor `0`, `0.00`, `$0.00` o vacío, se limpia automáticamente para teclear directo sin tener que borrar; si contiene un valor mayor a cero, se desformatea a número limpio y se selecciona todo el texto (`select()`) con prevención de deselección en `mouseup` para sustitución inmediata.
  - Al desenfocar (`blur`), se restaura el formato correspondiente (`moneyFormat` para `.moneyformat` y formato decimal para litros y montos).
  - Aplicado a sueldo operador, dinero de viaje, litros diésel, litros urea y gastos adicionales (tanto en `/planeaciones/planeacion-step` como en `/planeaciones/viajes/editar/{id}`). En `PlaneacionService` se blindó la recepción de valores monetarios con limpieza regex defensiva.
- **Spec 019 (Thin Controllers & Reusable Services) — VALIDADA EN DEMO (SGT, MEC y MEP):**
  - `PlaneacionService`, `CotizacionesService`, `CuentasCobrarService`, `MepService`, `EquipoService` y `OperadorService` centralizan lógica de negocio.
  - Unicidad de `id_viaje` por cotización en `resolverViajeDesdeRequest` y `CoordenadasService::baseQuery` evitando duplicados en rastreo.
  - Conversión Full / Local-Foráneo con normalización de IDs; validación defensiva de CURP y carpetas en operadores; desacople de WhatsApp y blindaje null-safe en `ExternosController`.
- **Persistencia de Estados de Cuenta en CXC (`/reporteria/cotizaciones/cxc`):**
  - Renombre in-situ al editar folio completo (`update(['numero' => $nuevo])`); creación de grupo al seleccionar subconjunto; limpieza automática de registros con 0 cotizaciones (`doesntHave('cotizaciones')->delete()`).
- **Spec 012 (Separación por Unidades en Reportería):**
  - Switch `separar_unidades` operativo en CXC, Viajes, GXP, Utilidad, Documentos (`/reporteria/documentos`), Liquidados CxC (`/reporteria/liquidados/cxc`) y Liquidados CxP (`/reporteria/liquidados/cxp`).
  - En Liquidados CxC y CxP: interruptor agregado tanto junto a "Seleccionar todo" como en barra de exportación para empresas globales (`requiere_unidad_gasto == 1`), sincronizados por JS y con fallback desmarcado por defecto.
  - En CxP (`ReporteriaService::exportLiquidadosCxp`, `LiquidadosCxpExport` y `pdf.blade.php`), soporta `$separarUnidades` para agrupar por unidad, mostrar "Unidad / Tracto" en lugar de contratista y totalizar por unidades distintas.
  - Corregido `pdf.blade.php` de documentos para evaluar `$esEmpresaGlobal = !empty($separarUnidades)` en lugar de hardcodear `esGlobalMultiEquipo()`, respetando cuando el switch está desmarcado.
  - Comando `php artisan empresa:reset-pruebas` para reestablecer contenedores a Aprobada, limpiar gastos y contabilidad operativa conservando saldos iniciales de bancos.
- **Modal Detalle Planeación (`/planeaciones/monitor/board/info-viaje`):**
  - Mapeo de proveedor, transportista, operador, teléfono, marca e IMEI en consulta SQL por `docum_cotizacion.id` con fallbacks automáticos mediante relaciones Eloquent (`Asignaciones` -> `Operador`, `Camion`, `Chasis`, `Proveedor`).
  - Variables de frontend blindadas en `boardCarpos.js` y `boardClient.js` con soporte para `Transporte Propio` y `response.nombre`.
- **Specs operativas activas:** Spec 002–018 (`/liquidaciones/ajustes`, `#filtro_unidad`, Reporte Personalizado CXC, Naanix GPS, Multi-equipo `requiere_unidad_gasto == 1`).

## Decisiones arquitectónicas (y por qué)
- Thin Controllers estrictos: los controladores solo reciben requests, validan con FormRequests y delegan a Services bajo `DB::transaction()`.
- Precargas de Diésel y Préstamos: el combustible físico permanece en la unidad y el anticipo se convierte en pasivo del chofer sin duplicar salidas de banco.
- Deduplicación de Viáticos y Aislamiento de Sueldos: `tipo_gasto = 'operador'` se filtra fuera de gastos de planeación.
- Multi-equipo H00 Global: activado estrictamente con `requiere_unidad_gasto == 1`.

## Aprendizajes y errores a evitar
- En la tabla `cotizaciones`, `jerarquia` debe ser `'Principal'` (nunca `null`), ya que cláusulas `where('jerarquia', '!=', 'Secundario')` excluyen registros con `null` en SQL.
- La tabla `prestamos` no tiene `id_empresa`; se vincula mediante `id_operador`, `id_banco` o `origen_asignacion_id`.
- En la tabla `prestamos`, la columna `pagos` no tiene default; asignar siempre `pagos => 0` y `saldo_actual` explícitos.
- En la tabla `cotizaciones`, la clave primaria es `id` (no `id_cotizacion`) y se vincula a través de `docum_cotizacion.id_cotizacion`.
- NUNCA alterar la firma JSON de rutas en `routes/api.php` consumidas por la app Flutter `operador_appsgt`.
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.
