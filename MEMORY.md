# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~100 líneas).

## Estado actual
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
- **Spec 019 (Thin Controllers & Reusable Services) — VALIDADA EN DEMO (SGT, MEC y MEP):**
  - `PlaneacionService`, `CotizacionesService`, `CuentasCobrarService`, `MepService`, `EquipoService` y `OperadorService` centralizan lógica de negocio.
  - Unicidad de `id_viaje` por cotización en `resolverViajeDesdeRequest` y `CoordenadasService::baseQuery` evitando duplicados en rastreo.
  - Conversión Full / Local-Foráneo con normalización de IDs; validación defensiva de CURP y carpetas en operadores; desacople de WhatsApp y blindaje null-safe en `ExternosController`.
- **Persistencia de Estados de Cuenta en CXC (`/reporteria/cotizaciones/cxc`):**
  - Renombre in-situ al editar folio completo (`update(['numero' => $nuevo])`); creación de grupo al seleccionar subconjunto; limpieza automática de registros con 0 cotizaciones (`doesntHave('cotizaciones')->delete()`).
- **Spec 012 (Separación por Unidades en Reportería):**
  - Switch `separar_unidades` operativo en CXC, Viajes, GXP, Utilidad y Documentos (`/reporteria/documentos`).
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
- En la tabla `prestamos`, la columna `pagos` no tiene default; asignar siempre `pagos => 0` y `saldo_actual` explícitos.
- En la tabla `cotizaciones`, la clave primaria es `id` (no `id_cotizacion`) y se vincula a través de `docum_cotizacion.id_cotizacion`.
- NUNCA alterar la firma JSON de rutas en `routes/api.php` consumidas por la app Flutter `operador_appsgt`.
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.
