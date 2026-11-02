# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~60 líneas).

## Estado actual
<<<<<<< Updated upstream
- Plataforma ERP de Transporte y Logística (Gologi Pro / SGTSITA) operativa.
- Módulos activos: Cotizaciones, Viajes, Contenedores, Planeación, Monitoreo, Gastos y Liquidaciones.
- Spec 018 (Complementos y Ajustes de Liquidación): Submódulo `/liquidaciones/ajustes` ("Ajustes de Liquidación") en el menú lateral. Permite registrar liquidaciones complementarias vinculadas a una liquidación previa sin modificar el histórico cerrado. Captura dinámica de Percepciones (sueldo, viáticos, reembolsos, bonos) y Deducciones (abonos a préstamos/adelantos con amortización en `pagos_prestamos`, descuentos). Cálculo de neto en tiempo real con salida y validación bancaria vía `BancosService::registrarMovimiento` (polimorfismo a `LiquidacionAjuste`). Generación de comprobante oficial en PDF y listado en AG Grid.
- Spec 017 (Filtro de Unidades en Historial de Liquidaciones - Empresa Global): En `/liquidaciones/historial`, selector reactivo de tractocamiones (`#filtro_unidad`) con `EquipoService::getTractocamiones()`. Búsqueda no destructiva con `whereExists` para preservar la liquidación completa aunque contenga viajes con unidades distintas.
- Spec 016: Reporte Personalizado de Cuentas por Cobrar (CXC) exportable en PDF y Excel con layout oficial "ESTADO DE CUENTA - TRANSPORTE". Valores vacíos monetarios se muestran como $0; columna Sobrepeso mapeada a "Total Sobre Peso Viaje" con cálculo de precisión idéntico al formulario de edición ((peso - reglamentario) * precio_sp, e.g. HAMU4974121 = $10,152.00); columna de observaciones conectada a `observaciones_edo_cuenta`. Comando `php artisan viajes:corregir-sobrepeso-costos` (`CorregirSobrepesoViajesCostosCommand.php`) para recalcular y corregir en BD (`viajes_costos`) las diferencias de sobrepeso por decimales reales. `ViajesCostosService::procesarSobrepeso` actualizado para guardar siempre la diferencia decimal exacta.
- Spec 011 (CONAC Pólizas & Verdad Operativa): Balance General conciliado y cuadrado con Diferencia $0.00. Bancos sincronizados de `cat_banco_cuentas_movimientos`. Subcuenta Banorte `1.1.1.2.005` y `1.1.2.3.004`.
- Spec 002: Integración de movimientos y transferencias bancarias en gastos (Draft Autosave).
- Spec 003: Refactorización GPS SIS hacia API REST Naanix (`MClientesExternosJSPY`).
- Spec 004: Sincronización integral Gastos de viaje, Pagos bancarios y App Móvil Admin.
- Spec 005 (demo): Multi-equipo en H00 Global (`id_empresa=28`). Filtro universal de Unidad en reportes y precarga al editar gastos.
- Spec 005 (móvil): Restauración automática de gastos soft-deleted al re-sincronizar desde App Móvil Admin.
- Spec 006: Resumen mensual de utilidades, desglose drill-down y selector rápido en `/socios`.
- Spec 007: Visualización de gastos indirectos de periodo sin contenedor en `/reporteria/utilidad`.
- Spec 010: Control universal de calendario DateRangePicker dinámico en módulo central SGT (`window.initSgtDateRange`, años delimitados por BD de cotizaciones y año actual + 1).
- Spec 012: Separación opcional por unidades en reportería vía checkbox (`separar_unidades` default false) y filtro de unidad condicionados estrictamente a empresas con Configuración Global = 1 (`requiere_unidad_gasto == 1`); para empresas normales (0) la pantalla queda limpia sin esos controles; confirmador de unidad en `/gastos` para empresas con `requiere_unidad_gasto == 1`.
- Spec 013: Agrupación de otros gastos por unidad en `/reporteria/utilidad`, resumen con Bruto, Gastos y Neto por unidad; cuadre estricto del encabezado superior al filtrar una sola unidad; asignación automática de `id_equipo` desde asignaciones de viaje.
- Módulo `reporteria/complementos-pago` (interno SGT): filtro por unidad y columna Tractocamión condicionados a empresas globales (`requiere_unidad_gasto == 1`). Vistas de clientes intactas.
- API REST activa para Flutter `operador_appsgt` y microservicio WhatsApp (`whatsapp-bot/`).
- Stack: Laravel 9 (PHP 8.1/8.2) + MySQL 8 + Docker + Metronic UI.
- Respaldo de Sistema Completo: Comando Artisan `backup:sistema-completo` (`GenerarRespaldoSistemaCompletoCommand.php`) para generar paquete `.tar.gz` con código fuente + documentos físicos (`public/cotizaciones`, `public/uploads`, `public/equipos`, `public/operador`) + volcado SQL. Excluye `.env`, `.env.*`, `vendor/`, `node_modules/`, cachés y `.git/`. Soporta `--sin-gps`, `--listar` y eliminación con `--eliminar` (o `--archivo=...`). Flag `--skip-ssl` en `mysqldump` para compatibilidad local Docker-Host y servidores de producción.

## Decisiones (y por qué)
- `LiquidacionesAjustesController`: los ajustes se modelan como entidades independientes (`liquidacion_ajustes` y `liquidacion_ajustes_detalles`) con FK a la liquidación original, evitando reabrir o alterar liquidaciones cerradas y protegiendo la trazabilidad contable y bancaria previa.
- Integración Bancaria Polimórfica: los ajustes con importe neto positivo registran egreso en `cat_bancos_cuentas_movimientos` con `referenciaable_type = LiquidacionAjuste::class` y validación de saldo disponible mediante `BancosService::validarsaldoparacargo`.
- Amortización de Deducciones: al vincular una deducción a un préstamo/adelanto activo, se actualiza `prestamos.saldo_actual` y se genera `PagoPrestamo` con `tipo_origen = 'ajuste_liquidacion'`.
- `/liquidaciones/historial`: filtro de unidad evalúa existencia con `whereExists` en la consulta principal de `Liquidaciones` para no desmembrar liquidaciones multi-equipo.
- `EquipoService` y accesor `$equipo->texto_select`: unifican consultas y formato `id_equipo - marca` en todo el sistema.
- Transacciones `DB::transaction`: obligatorias en cotizaciones, viajes, gastos, liquidaciones, ajustes y movimientos bancarios.
- Multi-equipo H00 Global: activado estrictamente con `requiere_unidad_gasto == 1` (sin hardcodear IDs como 28). Cada movimiento se imputa a su unidad.
- Visibilidad condicional de Controles de Unidad en Reportería: El switch de `#checkSepararUnidades` y el selector de unidad (`id_equipo`) solo se muestran en las vistas si `$esEmpresaGlobal` (`requiere_unidad_gasto == 1`). Para empresas normales (0) la UI queda limpia. El JS valida con optional chaining o fallbacks (`val() || ''`, `is(':checked') ? 1 : 0`) para no romper llamadas AJAX ni parámetros al backend.
- `reporteria/complementos-pago`: Solo modificado en la vista interna del SGT; la vista del portal cliente se mantiene intacta.
- Spec 012 & 013 (Utilidad por Unidad): En `/reporteria/utilidad`, al activar separación, cada tracto muestra sus viajes (Bruto), sus otros gastos detallados y su Utilidad Neta (`Bruto - Gastos`). Gastos sin unidad van a sección huérfana. Al filtrar una unidad (`id_equipo`), `getGastosGeneralesPeriodo` filtra fielmente por dicho equipo para que el Gran Total superior cuadre al centavo con el desglose de la unidad.
- Spec 012 & 013 (Integridad en Gastos): En empresas con `requiere_unidad_gasto == 1`, se exige unidad en todo tipo de gasto (con segundo selector de confirmación en periodo/general y extracción automática de `id_camion` en viajes).
- Transacciones `DB::transaction`: obligatorias en cotizaciones, viajes, gastos y movimientos bancarios para evitar inconsistencias.
- Trait Naanix independiente (`app/Traits/NaanixGPSTrait.php`): preserva `SISGPSTrait.php` intacto como respaldo/fallback.
- Caché de Tokens Naanix (50 min) y Llave UTC dinámica: previene saturación de peticiones de autenticación en el cron `rastreo:intervalConfig`.
- Consulta masiva por lote (`ObtenerPosicionActualGeneral`): reduce latencia de sincronización de ~40s a < 1s.
- `sincronizarGastoConBancos()` en `GastosService`: unifica propagación en cascada de importes hacia `gasto_pagos`, `cat_bancos_cuentas_movimientos` y saldo en `bancos`.
- `meses_resumen` en `SociosService`: agrupa viajes (bruta) y gastos indirectos imputados por mes, cuadrando Utilidad Mensual.
- `linkedCalendars: false` y `showDropdowns: true` en `daterangepicker`: desacopla calendarios y permite seleccionar mes/año sin desbordar.
- `window.initSgtDateRange()` y `/sgt/fechas/periodos-cotizaciones`: calcula dinámicamente `ejercicio_ini_bd` (BD `cotizaciones`) y `ejercicio_fin_bd` (`max(max_bd, anio_actual + 1)`). Elimina años inválidos (2001) y previene mantenimiento anual. En acciones rápidas solo muestra: Últimos 7 días, Últimos 30 días, Este mes, Mes anterior y Personalizado (excluyendo Hoy, Ayer y años estáticos del menú rápido). Soportado tanto en `layouts.app` como en `layouts.usuario_externo` para vistas de clientes (`/viajes/mis-viajes`, `/viajes/documents`, reportería externa).
- `/reporteria/utilidad`: botón "Ver Gastos" muestra gastos indirectos del periodo si no hay viajes o contenedor seleccionado.
- `EquipoService` y accesor `$equipo->texto_select`: unifican consultas (`getTractocamiones()`) y estandarizan el formato visual de unidades a `id_equipo - marca` en todos los selectores de gastos y reportería (Principio 7 Constitución).
=======
- Plataforma ERP SGTSITA (Gologi Pro) 100% operativa (Metronic UI, Laravel 9, MySQL 8, Docker).
- **Spec 019 (Thin Controllers & Reusable Services) — COMPLETADA AL 100% (Fases 1 a 6):**
  * **Fase 1 (Catálogos):** `ClienteService`, `ProveedorService`, `OperadorService`, `PrestamoService`, `EquipoService` + FormRequests.
  * **Fase 2 (Tesorería & CxC/CxP):** `BancosService`, `GastosService`, `CuentasCobrarService`, `CuentasPagarService`.
  * **Fase 3 (Liquidaciones & Ajustes):** `LiquidacionesService` centraliza balance real, amortizaciones y bancos.
  * **Fase 4 (Operaciones & Planeación):** `CotizacionesService` y `PlaneacionService` (`PlaneacionController` 2,473 -> 420 líneas; `CotizacionesLocalesController` 173 -> 60 líneas).
  * **Fase 5 (Portales & Monitoreo):** `CostosViajeMepService`, `MepService`, `CoordenadasService` (`CostosViajeMEPController` 407 -> 154 líneas; `MepController` 570 -> 216 líneas; `ExternosController` y `ConboysController` desacoplados).
  * **Fase 6 (Validación):** 37 archivos verificados sin errores sintácticos (`php -l`), cachés limpias (`optimize:clear`) y 100% de rutas resueltas. API móvil protegida sin cambios de contrato.
- Submódulo `/liquidaciones/ajustes` para complementos y ajustes independientes.
- Respaldo completo del sistema disponible vía `php artisan backup:sistema-completo`.

## Decisiones arquitectónicas (y por qué)
- `PlaneacionService::anularPlaneacion`: centraliza reversión atómica de asignación, devolución bancaria de anticipos y cancelación de gastos asociados tanto para el panel web como para la app móvil Flutter.
- `CotizacionesService`: unifica la lógica de creación individual, en lote y conversiones Full / Local-Foráneo para evitar duplicación entre controladores web y locales.
- Deducciones en liquidaciones y ajustes amortizan préstamos/adelantos activos actualizando `saldo_actual` y registrando `pagos_prestamos`.
- Transacciones `DB::transaction()` obligatorias en todo cálculo y mutación de saldo.
>>>>>>> Stashed changes

## Errores a evitar
- En `/viajes/solicitud/simple`, validar folios documentales solo al adjuntar archivos sobre viajes existentes, inicializar FileUploader después del guardado y proteger scripts compartidos ante campos ausentes. El procesamiento de archivos, persistencia, confirmaciones y notificaciones deben residir en `CotizacionesService`; el controlador solo delega.
- NUNCA alterar la firma JSON de rutas en `routes/api.php` consumidas por la app Flutter `operador_appsgt`.
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.
- Validar siempre que el monto de conceptos sea positivo y que haya cuenta bancaria asignada cuando exista desembolso neto.
- Al renderizar PDFs con Pdf::loadView, asegurar compatibilidad dual de variables (cotizacion/cotizaciones) y blindar blades con operadores null-safe (?->) y try-catch para no interrumpir transacciones de guardado.

## Próximos pasos
- [ ] Validar en producción telemetría continua de Naanix y retirar `SISGPSTrait.php`.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
