# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~100 líneas).

## Estado actual
- Cierre de Sesión Automático y Rastreo de Mapas (`CheckUserSessionTimeout.php` y `sessionAutoLogout.js`):
  - Sincronización multi-pestaña en `localStorage` (`sgt_last_activity_at` y `sgt_map_tracking_active_at`) para evitar que una pestaña en segundo plano dispare `POST /logout` y cierre la sesión activa de otras pestañas.
  - Detección de actividad real en interfaz (`mousemove`, `mousedown`, `keydown`, `scroll`, `touchstart`, `click`, `input`, `change`) con keep-alive automático a `/session/keep-alive` cada 4 minutos mientras haya actividad o rastreo de mapas.
  - En pantallas de rastreo/mapas (`/coordenadas*`, `/mapa-comparacion*`, `/configurar-geocerca*`, `/gps*`, `/reporteria/consumo-unidades/mapa*`, o mapas visibles en pantalla), el cierre automático por inactividad se pausa y mantiene viva la sesión indefinidamente.
  - Rutas autenticadas de `routes/externos.php` integradas en `middleware(['auth', 'idle.timeout'])` para que la navegación de clientes externos actualice `last_user_activity_at`.
- Viajes Locales (Burrero / Vaimas — `/viajes/solicitud/simple-local` y `/viajes/editar-local`): corregido guardado de `naviera_id` en `CotizacionesController::storelocal` (antes leía `$request->naviera` en lugar de `naviera_id`, guardando `NULL` en el primer intento) y sanitización de strings `'null'` en `singleUpdatelocal`. En `cotizaciones-local.js`, `numBoleta` y `numDoda` solo se exigen si se adjunta archivo (`#BoletaLib`/`#Doda`), evitando bloqueos tras 3.8s cuando `#fileUploaderContainer` pierde `.d-none`; el ID activo se aísla por pestaña (`window.currentCotizacionId` / `data-cotizacion-id`) para evitar colisiones por `localStorage.cotizacionId`, y `ExternosController::editFormlocal` filtra por `idCotizacion`/`id_cliente` con `orderByDesc('id')`.
- Spec 024 (Devengo de sueldos, anticipos de operadores y deducciones de liquidación):
  - `OperadorGastosContabilidadService`: deduplicación automática de comprobaciones (`limpiarComprobacionesDuplicadas()`) que elimina viáticos redundantes provenientes de `viaticos_operadores` cuando ya existen en `gastos_operadores` (75 duplicados eliminados en Empresa 8).
  - Sueldos y anticipos segregados: no se muestran en `/planeaciones/viajes/editar/{id}` ni en listados por cotización para evitar confusión o borrado accidental. Accesibles exclusivamente desde `/gastos` mediante el filtro de tipo "Sueldos" o la categoría "Sueldos de Operadores".
  - Conciliación de Sueldo vs Dinero Sin Justificar en `/reporteria/utilidad`:
    - Cuando existen anticipos de viaje no comprobados (`dineroViajeSinJustificar`), la columna `pagoOperacion` refleja el neto pagado de la liquidación y `dineroViajeSinJustificar` muestra el saldo retenido.
    - Capping de `pagoOperacion`: Se limita a `min($sueldo_viaje, $total_pagado)` para evitar que en viajes con viáticos excedentes reembolsados se sume el excedente a la columna de sueldo ($2,020.00 en 5 viajes), preservando `pagoOperacion` en exactamente $487,990.00.
    - Fechas de Viáticos en Sincronización: `OperadorGastosContabilidadService` toma prioritariamente la fecha del viaje (`asignacion->fecha_inicio`) si `fecha_comprobante` y `created_at` son nulos, evitando que caigan en la fecha de ejecución (`now()`).
    - Conciliación acumulada global: Ingresos coinciden 100% ($6,493,640.00) y Utilidad Neta coincide con $0.04 centavos de diferencia por redondeo ($800,507.50 vs $800,507.46 en Contabilidad).
    - Alineación de fechas: Creado comando `php artisan contabilidad:alinear-fechas-gastos-viaje {empresa?}` para reasignar fecha real de viaje a gastos y pólizas que tenían fecha en noviembre. En Empresa 8 al corte 30/09/2026: Reporte $994,289.92 = Contabilidad $994,289.92 (Diferencia $0.00).
    - Caso Contenedor `HMMU4301580-JHK16` (Cotización 2263): Gastos de viaje corregidos a $17,106.26 (sin duplicidad de tránsito y talacha), Pago Operación $4,700.00, Sin Justificar $300.00, Utilidad $31,173.74.
  - Catálogo Balance General: prefijo `1.1.1.1%` configurado como `EFECTIVO Y CAJA`.
  - Balance General Empresa 8 (Oliva) al 2026-09-30: Activo $1,938,007.88 = Pasivo $911,327.96 + Patrimonio $1,026,679.92. Bancos en positivo $432,750.25 (Azteca + Banorte). Cuadrado estrictamente con Diferencia $0.00.
  - Nota Consolidado Multi-empresa: El déficit bancario en la vista consolidada global al 30/09 se debe a que empresas secundarias (2, 6, 9, 27) tienen pagos de gastos cargados a bancos sin pólizas de apertura ni depósitos registrados. Al filtrar por Empresa 8 (Oliva), Bancos es estrictamente positivo ($432,750.25).
- Plataforma ERP de Transporte y Logística (Gologi Pro / SGTSITA) operativa.
- Módulos activos: Cotizaciones, Viajes, Contenedores, Planeación, Monitoreo, Gastos y Liquidaciones.
- Spec 020 (Cierre Contable de Ejercicios, Acumulación Real 3.2.1, SGT DateRangePicker y Reorganización de Navegación):
  - En `/contabilidad/balance-general`: botones "Matriz y Cuentas CONAC" y "Guía Contabilizadora" removidos; botón de acceso a "Guía Contabilizadora" integrado en `/contabilidad/configuracion`.
  - Selector de fechas del Balance General migrado a `initSgtDateRange` con accesos rápidos: Ejercicio 2025, Ejercicio 2026, Este mes, Mes anterior y Personalizado.
  - Acumulación real en `3.2.1`: Si no existen pólizas de cierre contable formal (`cierre_ejercicio`), `3.2.2` permanece estrictamente en $0.00 y `3.2.1` acumula el resultado operativo integral desde inicio de operaciones con Balance General cuadrado ($A = P + C$, Diferencia $0.00).
- Spec 011 (CONAC Pólizas & Verdad Operativa): Balance General al corte 2026-09-30 conciliado y cuadrado con Diferencia $0.00.
  - Cuentas por Cobrar (CXC `1.1.2.1%`): Al corte 30/09 muestra exactamente $297,650.00.
  - Cuentas por Pagar (CXP `2.1.1%`): $594,357.96 exactos reflejando deuda operativa y gastos devengados.
  - Bancos (`1.1.1.2%`): $432,750.25 (Azteca $12,775.96 + Banorte $419,974.29).
<<<<<<< HEAD
=======
  - Activo ($1,328,195.92) = Pasivo ($288,857.96) + Patrimonio ($1,039,337.96). Cuadrado: SI (Diferencia: $0.00).
- `ContabilidadSyncHistoricoCommand`: acepta argumento posicional `{empresa?}` y opción `--empresa=`. En `syncOperacionesHistoricasPreBanco`, sincroniza la amortización de CxP y CxC al corte 30/09 contra el saldo pendiente real de gastos y cotizaciones, asegurando que Cuentas por Pagar (2.1.1) refleje con exactitud la deuda operativa pendiente ($186,857.96 en producción), manteniendo el Balance General estrictamente cuadrado con Diferencia $0.00.
- Conciliación de Ingresos por Fletes y Reporte de Utilidad:
  - En `/reporteria/utilidad`: `precioViaje` toma `$d->total` sin sumar gastos operativos extraordinarios, cuadrando al centavo con la cuenta `4.1.1.1` (Ingresos por Fletes y Servicios) del Estado de Resultados en $3,810,200.00 (Diferencia: $0.00).
  - Spec 023: filtro Cliente/Todos se aplica a grilla y salidas; filas seleccionadas/visibles de AG Grid se respetan con `selectAll: filtered`. Grilla vacía genera reporte con gastos generales del periodo y utilidad neta negativa; la selección vacía nunca se amplía a todos los viajes. El prorrateo por unidad se calcula antes del filtro cliente.
- Desglose Contable de Cobros Multipoliza y Contenedores:
  - En `PolizaService::contabilizarMovimientoBancario`: depósitos bancarios originados en `CobroPago` desglosan una partida individual de abono a Clientes (`1.1.2.1.001`) por cada cotización/contenedor cobrado, evitando asociar montos globales a un solo contenedor.
  - En el Auxiliar Contable de CXC, el contenedor `MSKU1782954` muestra Cargo por $62,530.00 y Abonos por $21,280.00 (Azteca) y $41,250.00 (Banorte), saldo neto: $0.00.
- Regla Contable para Viajes Full:
  - En `CotizacionesService::contabilizarProvisionViaje`: solo se provisiona la cotización `Principal`, concatenando ambos contenedores (`CONT1 / CONT2`) en referencia y concepto. La cotización `Secundaria` no se provisiona para evitar duplicidad de ingresos y saldos falsos en CXC.
- Provisión y Cobro Operativo en tiempo real:
  - Al capturar costos y crear viaje (`CotizacionesController::storeV2` y `update`): provisión automática `PROVISION_VIAJE` (Cargo CXC `1.1.2.1.001`, Abono Ingresos `4.1.1.1`).
  - Al cobrar en CXC (`CuentasCobrarController::aplicar_pagos` -> `BancosService::registrarMovimiento`): generación automática de póliza bancaria (Cargo Bancos `1.1.1.2.xxx`, Abono CXC `1.1.2.1.001`), cancelando de inmediato la provisión.
- `/reporteria/viajes`: columna `peso_contenedor` configurada con `agNumberColumnFilter` y `comparator` numérico (filtros de Mayor que, Menor que, Igual a, En rango en español) y habilitada en exportaciones de Excel y PDF.
- Formatos Oficiales CONAC & Exportación: Vistas previas dinámicas en `/contabilidad/balance-general` para Balance General (ESF-01), Estado de Resultados (ERA-02), Balanza de Comprobación (BC-03), Auxiliar Contable / Libro Mayor por Cuentas (AC-07) con desglose de pólizas por cuenta afectable y saldos acumulados, y Libro Diario de Pólizas (LD-06) con partidas detalladas para conciliación. Soporte completo de exportación a PDF (DomPDF institucional apaisado con firmas) y Excel (Maatwebsite\Excel con estilos y sumas iguales).
- Spec 002: Integración de movimientos y transferencias bancarias en gastos (Draft Autosave).
- Spec 003: Refactorización GPS SIS hacia API REST Naanix (`MClientesExternosJSPY`).
- Spec 004: Sincronización integral Gastos de viaje, Pagos bancarios y App Móvil Admin.
- Spec 005 (demo): Multi-equipo en H00 Global (`id_empresa=28`). Filtro universal de Unidad en reportes y precarga al editar gastos.
- Spec 005 (móvil): Restauración automática de gastos soft-deleted al re-sincronizar desde App Móvil Admin.
- Spec 011 (CONAC Pólizas & Verdad Operativa): Bancos en Balance General CONAC sincronizados directamente de `cat_banco_cuentas_movimientos` (/bancos/bancosv2) alcanzando exactamente $721,400.25 (Azteca $119,175.96 + Banorte $602,224.29). Subcuenta Banorte `1.1.1.2.005` agregada. Préstamos a operadores (`1.1.2.3.001`) conciliados en exactamente $36,000.00 (saldo insoluto real de Oscar de Jesús Moreno) tras incorporar amortizaciones de liquidaciones (`PagoPrestamo`) y contrapartida en `1.1.2.3.004` (Otros Deudores Diversos). Subcuenta `1.1.2.3.004` evita sobregiro en Gastos por Comprobar y corrige duplicidad en el Activo. Balance General perfectamente cuadrado a 0.00.
- Balance General Drill-down: Modal interactivo auxiliar por concepto (`balance-general/detalle-concepto`) con resolución automática y masiva del número de contenedor (`DocumCotizacion` -> `num_contenedor`) cruzando cotizaciones, asignaciones, gastos operativos y amortizaciones; badge visual distintivo y búsqueda por contenedor en tiempo real.
- Formatos Oficiales CONAC & Exportación: Vistas previas dinámicas en `/contabilidad/balance-general` para Balance General (ESF-01), Estado de Resultados (ERA-02) y Balanza de Comprobación (BC-03). Botón "Imprimir" eliminado; incorporadas exportaciones nativas en PDF (DomPDF institucional con CSS2.1, orientación automática, 3 firmas y protesta de ley) y Excel (Maatwebsite\Excel con estilos y auto-ajuste). Balanza de comprobación en estricto equilibrio en Sumas Iguales con filtro reactivo de cuentas.
- Spec 006: Resumen mensual de utilidades, desglose drill-down y selector rápido en `/socios`.
- Spec 007: Visualización de gastos indirectos de periodo sin contenedor en `/reporteria/utilidad`.
- Spec 010: Control universal de calendario DateRangePicker dinámico en módulo central SGT (`window.initSgtDateRange`, años delimitados por BD de cotizaciones y año actual + 1).
- Spec 015: Gasto Burrero Vacío (GBV01) opcional al finalizar viaje (`PlaneacionController::finalizarViaje`) para evitar bloqueos por viajes cancelados o truncados tempranamente por daños físicos.
- Cierre de Sesión Automático y Rastreo de Mapas (`CheckUserSessionTimeout.php` y `sessionAutoLogout.js`):
  - Sincronización multi-pestaña en `localStorage` (`sgt_last_activity_at` y `sgt_map_tracking_active_at`) para evitar que una pestaña en segundo plano dispare `POST /logout` y cierre la sesión activa de otras pestañas.
  - Detección de actividad real en interfaz (`mousemove`, `mousedown`, `keydown`, `scroll`, `touchstart`, `click`, `input`, `change`) con keep-alive automático a `/session/keep-alive` cada 4 minutos mientras haya actividad o rastreo de mapas.
  - En pantallas de rastreo/mapas (`/coordenadas*`, `/mapa-comparacion*`, `/configurar-geocerca*`, `/gps*`, `/reporteria/consumo-unidades/mapa*`, o mapas visibles en pantalla), el cierre automático por inactividad se pausa y mantiene viva la sesión indefinidamente.
  - Rutas autenticadas de `routes/externos.php` integradas en `middleware(['auth', 'idle.timeout'])` para que la navegación de clientes externos actualice `last_user_activity_at`.
- Viajes Locales (Burrero / Vaimas — `/viajes/solicitud/simple-local` y `/viajes/editar-local`): corregido guardado de `naviera_id` en `CotizacionesController::storelocal` (antes leía `$request->naviera` en lugar de `naviera_id`, guardando `NULL` en el primer intento) y sanitización de strings `'null'` en `singleUpdatelocal`. En `cotizaciones-local.js`, `numBoleta` y `numDoda` solo se exigen si se adjunta archivo (`#BoletaLib`/`#Doda`), evitando bloqueos tras 3.8s cuando `#fileUploaderContainer` pierde `.d-none`; el ID activo se aísla por pestaña (`window.currentCotizacionId` / `data-cotizacion-id`) para evitar colisiones por `localStorage.cotizacionId`, y `ExternosController::editFormlocal` filtra por `idCotizacion`/`id_cliente` con `orderByDesc('id')`.
- Fallback Fecha Aplicación Dinero Viaje: En `/planeaciones/viajes/editar/{idCotizacion}`, si `dinero_contenedor` no tiene registro previo, precarga automáticamente `$asignacion->fecha_pago_salida` o `$asignacion->fecha_inicio`.
- API REST activa para Flutter `operador_appsgt` y microservicio WhatsApp (`whatsapp-bot/`).
- Spec 018 (Complementos y Ajustes de Liquidación): Submódulo `/liquidaciones/ajustes` ("Ajustes de Liquidación") en el menú lateral. Permite registrar liquidaciones complementarias vinculadas a una liquidación previa sin modificar el histórico cerrado. Captura dinámica de Percepciones (sueldo, viáticos, reembolsos, bonos) y Deducciones (abonos a préstamos/adelantos con amortización en `pagos_prestamos`, descuentos). Cálculo de neto en tiempo real con salida y validación bancaria vía `BancosService::registrarMovimiento` (polimorfismo a `LiquidacionAjuste`). Generación de comprobante oficial en PDF y listado en AG Grid.
- Spec 017 (Filtro de Unidades en Historial de Liquidaciones - Empresa Global): En `/liquidaciones/historial`, selector reactivo de tractocamiones (`#filtro_unidad`) con `EquipoService::getTractocamiones()`. Búsqueda no destructiva con `whereExists` para preservar la liquidación completa aunque contenga viajes con unidades distintas.
- Spec 016: Reporte Personalizado CXC en PDF y Excel con título/archivo `AAAA-MM-DD GC TRANSPORTE {folio}` usando la misma fecha de envío de metadatos. 20 columnas y metadatos mantienen el diseño de plantilla con anchos fijos. La sección compartida de bancos del Predeterminado aparece al final del Personalizado, conservando empresa propia (`banco_1`), proveedores (`cuenta_1`/`cuenta_2`), cuenta global y Empresa Global. La consulta personalizada usa la empresa de sus cotizaciones; Excel mantiene filas altas, combina A:C solo para datos bancarios y deja importes a la derecha. Folio y contenedores del encabezado van a la derecha y en negrita. No se alteran cálculos ni datos de la tabla personalizada.
- Spec 011 (CONAC Pólizas & Verdad Operativa): Balance General conciliado y cuadrado con Diferencia $0.00. Bancos sincronizados de `cat_banco_cuentas_movimientos`. Subcuenta Banorte `1.1.1.2.005` y `1.1.2.3.004`.
- Stack: Laravel 9 (PHP 8.1/8.2) + MySQL 8 + Docker + Metronic UI.
- Respaldo de Sistema Completo: Comando Artisan `backup:sistema-completo` (`GenerarRespaldoSistemaCompletoCommand.php`) para generar paquete `.tar.gz` con código fuente + documentos físicos (`public/cotizaciones`, `public/uploads`, `public/equipos`, `public/operador`) + volcado SQL. Excluye `.env`, `.env.*`, `vendor/`, `node_modules/`, cachés y `.git/`. Soporta `--sin-gps`, `--listar` y eliminación con `--eliminar` (o `--archivo=...`). Flag `--skip-ssl` en `mysqldump` para compatibilidad local Docker-Host y servidores de producción.
>>>>>>> d8e855aa453c43981df9cd4b75c4893816e4fbe7
  - Activo ($1,938,007.88) = Pasivo ($911,327.96) + Patrimonio ($1,026,679.92). Cuadrado: SI (Diferencia: $0.00).

## Decisiones (y por qué)
- Deduplicación de Viáticos: Al sincronizar comprobaciones desde `viaticos_operadores`, si ya existe un gasto registrado con `origen_legacy = 'gastos_operadores'` para la misma cotización y monto, se omite la creación de duplicados y se purgan las pólizas y registros redundantes para evitar duplicar costos en utilidades.
- Reconciliación Sueldo / Anticipo No Justificado: Cuando un operador no comprueba la totalidad de un anticipo y la diferencia se deduce en su liquidación (`LiquidacionContenedor`), la reportería muestra el sueldo neto efectivamente pagado y el anticipo retenido por separado, reflejando el flujo de efectivo real sin inflar el costo global.
- Aislamiento de Sueldos en UI: Los sueldos de operadores se protegen de modificaciones incidentales en la planeación y cotizaciones; su consulta y filtro se centraliza en el módulo `/gastos`.
- Transacciones `DB::transaction`: obligatorias en cotizaciones, viajes, gastos, liquidaciones, ajustes y movimientos bancarios.

## Aprendizajes y errores a evitar
- NUNCA modificar la respuesta JSON de endpoints en `routes/api.php` sin verificar el impacto en Flutter (`operador_appsgt`).
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.
- En Docker vs Host en Windows: para comandos Artisan que interactúan con MySQL o requieren conexión de red del contenedor (`host.docker.internal`), ejecutar `docker compose exec app php artisan <comando>`.
- `tipo_gasto` en tabla `gastos` es un ENUM de MySQL (`'general','periodo','unidad','viaje','cotizacion','contenedor','operador'`). Para sueldos se mantiene `tipo_gasto = 'operador'` con categoría `'Sueldos de Operadores'` y `origen_legacy = 'asignacion_sueldo_operador'`, filtrando a nivel de servicio/vista para no quebrar la estructura de base de datos.
- En el Balance General, el "Consolidado Global" suma todas las empresas del sistema. Si empresas inactivas o secundarias tienen movimientos sin aperturas bancarias, el saldo de bancos global puede verse afectado negativamente; se debe revisar por empresa individual (ej. Empresa 8, Oliva).

## Próximos pasos
- [ ] Ejecutar en producción `php artisan contabilidad:sync-historico 8 --force` tras desplegar cambios.
- [ ] Validar en producción telemetría continua de Naanix y retirar `SISGPSTrait.php`.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
