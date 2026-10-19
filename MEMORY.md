# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~100 líneas).

## Estado actual
- Plataforma ERP de Transporte y Logística (Gologi Pro / SGTSITA) operativa.
- Módulos activos: Cotizaciones, Viajes, Contenedores, Planeación, Monitoreo, Gastos y Liquidaciones.
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
## Decisiones (y por qué)
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

## Aprendizajes y errores a evitar
- NUNCA modificar la respuesta JSON de endpoints en `routes/api.php` sin verificar el impacto en Flutter (`operador_appsgt`).
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.
- En selectores de unidad/equipo, mostrar siempre `id_equipo - marca` (vía `$equipo->texto_select`) para consistencia en todo el sistema.
- En Naanix REST, la llave debe calcularse estrictamente en UTC (`UtcNow.Hour + UtcNow.Day + IdCliente`).
- En catálogos y reportes de imputación a tracto, filtrar siempre `where('tipo', 'Tractos / Camiones')` en `Equipo`.
- Si un gasto previo estaba en papelera, `GastosService::registrar()` debe invocar obligatoriamente `$gasto->restore()`.
- En `ranges` de daterangepicker, claves con expresiones dinámicas requieren corchetes `[expr]: [...]`.
- Si scripts compartidos (como `cotizacion-documentacion.js`) se usan en `layouts.usuario_externo`, asegurarse de que las dependencias (`sgt-daterange.js`, `moment`, `daterangepicker`) estén inyectadas en ese layout.
## Próximos pasos
- [ ] Validar en producción telemetría continua de Naanix y retirar `SISGPSTrait.php`.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
- [ ] Ejecutar `php artisan fix:gasto-fscu8106019` y `fix:gasto-msmu7292298` en producción.