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
- API REST activa para Flutter `operador_appsgt` y microservicio WhatsApp (`whatsapp-bot/`).
- Stack: Laravel 9 (PHP 8.1/8.2) + MySQL 8 + Docker + Metronic UI.
## Decisiones (y por qué)
- Multi-equipo H00 Global (`id_empresa=28`): coexisten tractocamiones activos (AB-100, AP-002, etc.). Cada movimiento se imputa a su unidad y los reportes filtran por `asignaciones.id_camion` o `gastos.id_equipo`.
- Transacciones `DB::transaction`: obligatorias en cotizaciones, viajes, gastos y movimientos bancarios para evitar inconsistencias.
- Trait Naanix independiente (`app/Traits/NaanixGPSTrait.php`): preserva `SISGPSTrait.php` intacto como respaldo/fallback.
- Caché de Tokens Naanix (50 min) y Llave UTC dinámica: previene saturación de peticiones de autenticación en el cron `rastreo:intervalConfig`.
- Consulta masiva por lote (`ObtenerPosicionActualGeneral`): reduce latencia de sincronización de ~40s a < 1s.
- `sincronizarGastoConBancos()` en `GastosService`: unifica propagación en cascada de importes hacia `gasto_pagos`, `cat_bancos_cuentas_movimientos` y saldo en `bancos`.
- `meses_resumen` en `SociosService`: agrupa viajes (bruta) y gastos indirectos imputados por mes, cuadrando Utilidad Mensual.
- `linkedCalendars: false` en `daterangepicker`: desacopla el calendario izquierdo del derecho al cambiar de año/mes.
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
## Próximos pasos
- [ ] Validar en producción telemetría continua de Naanix y retirar `SISGPSTrait.php`.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
- [ ] Ejecutar `php artisan fix:gasto-fscu8106019` y `fix:gasto-msmu7292298` en producción.