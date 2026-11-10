# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~60 líneas).

## Estado actual
- Cambio de Proveedor y Transportista en Viaje Planeado (`/viajes/editar` -> `CotizacionesController::singleUpdate`):
  - Homologado el proceso de deshacer planeación al cambiar `id_proveedor` (empresa) y/o `id_transportista` (proveedor) con `PlaneacionController::anularPlaneacion` y `CotizacionesController::cambiar_empresa`: cancela movimientos bancarios de dinero para viaje (`BancosService::cancelarMovimiento`), cancela pagos y elimina gastos unificados (`origen_legacy like 'asignacion_planeacion%'`), elimina registros dependientes en `GastosOperadores::withoutGlobalScope('no_eliminados')`, `Coordenadas`, `DineroContenedor` y `ViaticosOperador` antes de eliminar la `Asignaciones`, evitando el error `SQLSTATE[23000]: Integrity constraint violation: 1451` en `gastos_operadores_id_asignacion_foreign`.
- Spec 025 (Validación de Peso de Contenedor y Sobrepeso en Solicitudes de Clientes — `/viajes/solicitud/simple`, `/viajes/solicitud/simple-local`, `/viajes/editar/{id}`, `/viajes/editar-local/{id}` y cargas masivas):
  - En `CotizacionesController::validarYCalcularPesoSobrepeso()`: se valida que `peso_contenedor` esté en toneladas (`> 0` y `<= 60` ton) y se recalcula `sobrepeso = max(peso - peso_reglamentario, 0)` en servidor en `store`, `singleUpdate`, `storelocal`, `singleUpdatelocal`, `storeV2`, `update`, `storeMultiple` y `storeMultiplelocal`, evitando el error `SQLSTATE[22003]: Numeric value out of range` en `sobrepeso`.
  - En `cotizaciones.js` y `cotizaciones-local.js`: homologado `calcularSobrepeso()` con protección contra pesos `> 60` ton y alerta automática al abrir registros con peso corrupto.
- Pantalla Completa en Mis Viajes (`/viajes/mis-viajes` — `viajes_solicitados.blade.php` y `cotizacion-documentacion.js`):
  - Botón **Pantalla completa** en barra superior y menú "Opciones" (`toggleFullscreenMisViajes()`) con soporte de Fullscreen API del navegador y salida con tecla `ESC`.
  - Selector de densidad en pantalla completa (`Auto (Captura)` | `Compacta` | `Normal`): en modo `Auto (Captura)` (activo por defecto al entrar a pantalla completa) calcula dinámicamente el alto de fila (`18px–28px`) según la cantidad de viajes mostrados, fija el ancho de la columna checkbox (`42px`) y de las 8 columnas de documentos, y asigna el espacio horizontal disponible a `Transportista` y `Destino` para que 25–35+ viajes quepan completos en una sola captura.
- Cierre de Sesión Automático y Rastreo de Mapas (`CheckUserSessionTimeout.php` y `sessionAutoLogout.js`):
  - Sincronización multi-pestaña en `localStorage` (`sgt_last_activity_at` y `sgt_map_tracking_active_at`) para evitar que una pestaña en segundo plano dispare `POST /logout` y cierre la sesión activa de otras pestañas.
  - Detección de actividad real en interfaz con keep-alive automático a `/session/keep-alive` cada 4 minutos mientras haya actividad o rastreo de mapas.
  - En pantallas de rastreo/mapas (`/coordenadas*`, `/mapa-comparacion*`, `/configurar-geocerca*`, `/gps*`, `/reporteria/consumo-unidades/mapa*`), el cierre automático por inactividad se pausa.
  - Rutas autenticadas de `routes/externos.php` integradas en `middleware(['auth', 'idle.timeout'])`.
- Viajes Locales (Burrero / Vaimas — `/viajes/solicitud/simple-local` y `/viajes/editar-local`): corregido guardado de `naviera_id` en `CotizacionesController::storelocal` y sanitización de strings `'null'` en `singleUpdatelocal`. En `cotizaciones-local.js`, `numBoleta` y `numDoda` solo se exigen si se adjunta archivo (`#BoletaLib`/`#Doda`), aislando el ID activo por pestaña (`window.currentCotizacionId`).
- Spec 024 (Devengo de sueldos, anticipos de operadores y deducciones de liquidación):
  - `OperadorGastosContabilidadService`: deduplicación automática de comprobaciones (`limpiarComprobacionesDuplicadas()`).
  - Sueldos y anticipos segregados en `/gastos` mediante el filtro de tipo "Sueldos" o categoría "Sueldos de Operadores".
  - Conciliación de Sueldo vs Dinero Sin Justificar en `/reporteria/utilidad`: `pagoOperacion` limitado a `min($sueldo_viaje, $total_pagado)`.
  - Renombramiento de Anticipos a "Dinero para viaje": Creado comando `php artisan contabilidad:renombrar-anticipos-dinero-viaje {empresa?}` y actualizado `OperadorGastosContabilidadService` para registrar futuros viajes con `Dinero para viaje - [Operador]`.
  - Balance General Empresa 8 (Oliva) al 2026-09-30: Activo $1,938,007.88 = Pasivo $911,327.96 + Patrimonio $1,026,679.92. Bancos en positivo $432,750.25 (Azteca + Banorte). Cuadrado estrictamente con Diferencia $0.00.
- Spec 020 (Cierre Contable de Ejercicios, Acumulación Real 3.2.1 y SGT DateRangePicker):
  - En `/contabilidad/balance-general`: selector migrado a `initSgtDateRange`; acumulación real en `3.2.1` cuando no existen pólizas de cierre formal.
- Spec 011 (CONAC Pólizas & Verdad Operativa): Balance General al corte 2026-09-30 conciliado y cuadrado con Diferencia $0.00 (CXC `1.1.2.1%`: $297,650.00, CXP `2.1.1%`: $594,357.96, Bancos `1.1.1.2%`: $432,750.25).
- Conciliación de Ingresos por Fletes y Reporte de Utilidad (`/reporteria/utilidad`): `precioViaje` toma `$d->total` cuadrando con `4.1.1.1`. Spec 023 aplica filtro Cliente/Todos a grilla y salidas (`selectAll: filtered`).
- Desglose Contable de Cobros Multipoliza (`PolizaService::contabilizarMovimientoBancario`) y Regla Contable para Viajes Full (`CotizacionesService::contabilizarProvisionViaje` solo provisiona la `Principal`).
- `/reporteria/viajes`: columna `peso_contenedor` con `agNumberColumnFilter` numérico y habilitada en Excel/PDF.
- Formatos Oficiales CONAC & Exportación (`/contabilidad/balance-general`): ESF-01, ERA-02, BC-03, AC-07, LD-06 en PDF/Excel y modal drill-down por concepto.
- Specs operativas activas: Spec 002 (Gastos/Transferencias), Spec 003 (GPS Naanix), Spec 004/005 (Sync Gastos/Móvil/Multi-equipo), Spec 006 (`/socios`), Spec 007 (Gastos indirectos), Spec 010 (`initSgtDateRange`), Spec 015 (GBV01 opcional), Spec 016 (Reporte Personalizado CXC), Spec 017 (`#filtro_unidad` en liquidaciones), Spec 018 (`/liquidaciones/ajustes`).
- Respaldo de Sistema Completo: Comando Artisan `backup:sistema-completo` (`GenerarRespaldoSistemaCompletoCommand.php`) con `--sin-gps`, `--listar` y `--eliminar`.
- Plataforma ERP SGTSITA (Gologi Pro) 100% operativa (Metronic UI, Laravel 9, MySQL 8, Docker).
- **Spec 019 (Thin Controllers & Reusable Services) — COMPLETADA AL 100% (Fases 1 a 6):**
  * **Fase 1 (Catálogos):** `ClienteService`, `ProveedorService`, `OperadorService`, `PrestamoService`, `EquipoService` + FormRequests.
  * **Fase 2 (Tesorería & CxC/CxP):** `BancosService`, `GastosService`, `CuentasCobrarService`, `CuentasPagarService`.
  * **Fase 3 (Liquidaciones & Ajustes):** `LiquidacionesService` centraliza balance real, amortizaciones, bancos y `OperadorGastosContabilidadService`.
  * **Fase 4 (Operaciones & Planeación):** `CotizacionesService` y `PlaneacionService` (`PlaneacionController` y `CotizacionesLocalesController` delgados).
  * **Fase 5 (Portales & Monitoreo):** `CostosViajeMepService`, `MepService`, `CoordenadasService`.
  * **Fase 6 (Validación):** Sintaxis verificada (`php -l`), cachés limpias y rutas resueltas. API móvil protegida sin cambios de contrato.
- **Spec 025 (Validación de Peso de Contenedor y Sobrepeso):** En `CotizacionesService::validarYCalcularPesoSobrepeso()` y `CotizacionesController` (`> 0` y `<= 60` ton, recalcula `sobrepeso = max(peso - peso_reglamentario, 0)`), homologado en `cotizaciones.js` y `cotizaciones-local.js`.
- **Pantalla Completa en Mis Viajes (`/viajes/mis-viajes`):** Botón Pantalla completa (`toggleFullscreenMisViajes()`) y selector de densidad (`Auto (Captura)` | `Compacta` | `Normal`).
- **Cierre de Sesión Automático y Rastreo de Mapas (`CheckUserSessionTimeout.php` y `sessionAutoLogout.js`):** Sincronización multi-pestaña en `localStorage`, keep-alive cada 4 min y pausa automática en pantallas de rastreo/mapas.
- **Spec 024 (Devengo de sueldos, anticipos y deducciones):** `OperadorGastosContabilidadService` deduplica comprobaciones y registra "Dinero para viaje - [Operador]". En `/reporteria/utilidad`, `pagoOperacion = min($sueldo_viaje, $total_pagado)`. Balance General Empresa 8 cuadrado con Diferencia $0.00.
- **Spec 020 & 011 (CONAC & Cierre Contable):** Acumulación real en `3.2.1`, formatos ESF-01, ERA-02, BC-03, AC-07, LD-06 y selector `initSgtDateRange`.
- **Specs operativas activas:** Spec 002–018 (`/liquidaciones/ajustes`, `#filtro_unidad`, Reporte Personalizado CXC, Naanix GPS, Multi-equipo `requiere_unidad_gasto == 1`).
- **Respaldo de Sistema Completo:** `php artisan backup:sistema-completo` (`--sin-gps`, `--listar`, `--eliminar`).

## Decisiones arquitectónicas (y por qué)
- `PlaneacionService` y `LiquidacionesService`: centralizan la lógica de negocio e integran `OperadorGastosContabilidadService` para mantener sincronizadas las pólizas contables de sueldos, anticipos, comprobaciones y liquidaciones.
- `CotizacionesService`: unifica creación individual, en lote (`storeMultipleCotizaciones`, `storeMultipleLocal`), locales (`storeLocal`, `singleUpdateLocal`), validación de peso (`<= 60` ton) y conversiones Full / Local-Foráneo.
- Deduplicación de Viáticos y Aislamiento de Sueldos: `tipo_gasto = 'operador'` con categoría `'Sueldos de Operadores'` y `origen_legacy = 'asignacion_sueldo_operador'` se filtran fuera de otros gastos de planeación.
- Multi-equipo H00 Global: activado estrictamente con `requiere_unidad_gasto == 1`.
- Transacciones `DB::transaction()` obligatorias en todo cálculo y mutación de saldo.

## Errores a evitar
- En `storeLocal`, crear primero `Cotizaciones`, después su bitácora y luego `DocumCotizacion` con el ID real; devolver `id`, `cotizacion_id`, `docum_cotizacion_id` y `num_contenedor`.
- En ediciones de viajes locales, persistir transportista en `id_proveedor` y `transportista_local`, y empresa en `id_empresa`/`empresa_local` (sincronizando `docum_cotizacion.id_empresa`).
- Para adjuntos en FileUploader, obtener archivo con `getInstance(input).getChoosedFiles()`.
- Al renderizar PDFs con `Pdf::loadView`, asegurar compatibilidad dual de variables (`cotizacion`/`cotizaciones`) y blindar blades con operadores null-safe (`?->`) y `try-catch`.
- NUNCA alterar la firma JSON de rutas en `routes/api.php` consumidas por la app Flutter `operador_appsgt`.
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.

## Próximos pasos
- [ ] Ejecutar en producción `php artisan contabilidad:sync-historico 8 --force` tras desplegar cambios.
- [ ] Validar en producción telemetría continua de Naanix y retirar `SISGPSTrait.php`.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
