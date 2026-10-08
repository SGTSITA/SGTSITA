# MEMORY.md — SGTSITA

Memoria del proyecto entre sesiones. Máximo ~50 líneas: resume o elimina lo que ya no aporte.

## Estado actual
- Plataforma ERP de Transporte y Logística (Gologi Pro / SGTSITA) operativa.
- Módulos activos: Cotizaciones, Viajes, Contenedores, Planeación, Monitoreo, Gastos y Liquidaciones.
- Spec 002 implementada: Integración de movimientos y transferencias bancarias en modal de gastos con Draft Autosave.
- Spec 003 implementada: Refactorización del proveedor GPS SIS ("Senbais") hacia la nueva API REST/JSON Naanix (`MClientesExternosJSPY`).
- Spec 004 implementada: Sincronización integral y consistencia de importes entre Gastos de viaje, Pagos bancarios y Administración App Móvil.
- Spec 005 implementada: Restauración automática de gastos soft-deleted al re-sincronizar desde App Móvil Admin (`$gasto->restore()` en `GastosService::registrar` y `withTrashed()` en `AppMovilAdminController`).
- Spec 006 implementada: Resumen mensual de utilidades, drill-down de contenedores por mes (`modalDetalleMes`) y selector rápido de año/mes en `/socios`.
- API REST activa para la app móvil Flutter `operador_appsgt` (`/api/operador/*`).
- Microservicio en Node.js para notificaciones vía WhatsApp (`whatsapp-bot/`).
- Stack: Laravel 9 + PHP 8.1/8.2 + MySQL 8 + Docker + Metronic UI.

## Decisiones (y por qué)
- Docker Compose: garantiza paridad entre entorno local de desarrollo y producción.
- API Sanctum para `operador_appsgt`: autenticación móvil desacoplada y segura.
- Transacciones `DB::transaction`: obligatorias en cotizaciones, viajes, gastos y movimientos bancarios.
- `meses_resumen` en `SociosService`: Agrupa viajes (utilidad bruta) y gastos indirectos imputados por mes, calculando Utilidad Mensual cuadradita con el periodo.
- `linkedCalendars: false` en `daterangepicker`: Desacopla la vista de mes/año del calendario de inicio respecto al de fin.
- `/reporteria/utilidad`: Modal "Ver Gastos" muestra gastos indirectos del periodo si no hay viajes o contenedor seleccionado, eliminando el bloqueo previo.
- Trait Naanix independiente (`app/Traits/NaanixGPSTrait.php`): preserva `SISGPSTrait.php` intacto como respaldo/fallback.
- `sincronizarGastoConBancos()` en `GastosService`: unifica cascada a pagos y movimientos bancarios.

## Aprendizajes y errores a evitar
- NUNCA modificar la respuesta JSON de endpoints en `routes/api.php` sin verificar el impacto en Flutter.
- No ejecutar `migrate:fresh` ni `db:wipe` en ningún entorno.
- En `ranges` de daterangepicker, claves con expresiones dinámicas requieren corchetes `[expr]: [...]`.
- Si un gasto previo estaba en papelera, `GastosService::registrar()` debe invocar obligatoriamente `$gasto->restore()`.
- Notificaciones de feedback en App Móvil Admin estandarizadas uniformemente con SweetAlert2.

## Próximos pasos
- [ ] Validar en producción la telemetría continua de Naanix y retirar `SISGPSTrait.php` una vez estabilizado.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
- [ ] Ejecutar `php artisan fix:gasto-fscu8106019` y `fix:gasto-msmu7292298` en producción.
