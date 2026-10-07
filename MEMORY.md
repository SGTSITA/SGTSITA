# MEMORY.md — SGTSITA

Memoria del proyecto entre sesiones. Máximo ~50 líneas: resume o elimina lo que ya no aporte.

## Estado actual
- Plataforma ERP de Transporte y Logística (Gologi Pro / SGTSITA) operativa.
- Módulos activos: Cotizaciones, Viajes, Contenedores, Planeación, Monitoreo, Gastos y Liquidaciones.
- Spec 002 implementada: Integración de movimientos y transferencias bancarias en modal de gastos con Draft Autosave.
- Spec 003 implementada: Refactorización del proveedor GPS SIS ("Senbais") hacia la nueva API REST/JSON Naanix (`MClientesExternosJSPY`).
- Spec 004 implementada: Sincronización integral y consistencia de importes entre Gastos de viaje, Pagos bancarios y Administración App Móvil.
- Spec 005 implementada: Restauración automática de gastos soft-deleted al re-sincronizar desde App Móvil Admin (`$gasto->restore()` en `GastosService::registrar` y `withTrashed()` en `AppMovilAdminController`).
- API REST activa para la app móvil Flutter `operador_appsgt` (`/api/operador/*`).
- Microservicio en Node.js para notificaciones vía WhatsApp (`whatsapp-bot/`).
- Stack: Laravel 9 + PHP 8.1/8.2 + MySQL 8 + Docker + Metronic UI.

## Decisiones (y por qué)
- Docker Compose: garantiza paridad entre entorno local de desarrollo y producción.
- API Sanctum para `operador_appsgt`: autenticación móvil desacoplada y segura.
- Transacciones `DB::transaction`: obligatorias en cotizaciones, viajes, gastos y movimientos bancarios para evitar inconsistencias.
- Trait Naanix independiente (`app/Traits/NaanixGPSTrait.php`): preserva `SISGPSTrait.php` intacto como respaldo/fallback para reversión en caliente si se requiriera en producción.
- Caché de Tokens Naanix (50 min) y Llave UTC dinámica: previene saturación de peticiones de autenticación en el cron `rastreo:intervalConfig`.
- Consulta masiva por lote (`ObtenerPosicionActualGeneral`): reduce la latencia de sincronización de ~40s (SOAP N llamadas) a < 1s (1 llamada POST).
- `sincronizarGastoConBancos()` en `GastosService`: unifica la propagación en cascada de importes hacia `gasto_pagos`, `cat_bancos_cuentas_movimientos` y recálculo de saldo en `bancos`.

## Aprendizajes y errores a evitar
- NUNCA modificar la respuesta JSON de endpoints en `routes/api.php` sin verificar el impacto en la app Flutter `operador_appsgt`.
- No ejecutar `migrate:fresh` ni `db:wipe` en ningún entorno.
- En Naanix REST, la llave debe calcularse estrictamente en UTC (`UtcNow.Hour + UtcNow.Day + IdCliente`).
- En UbicacionService, asegurar `$inicio = microtime(true)` para evitar 500 al formatear respuestas de error o unidades sin posición reportada.
- Al editar costos en App Móvil Admin, si el gasto ya está pagado debe sincronizarse en cascada con el movimiento bancario; si no está pagado, solo tocar gastos.
- En `AppMovilAdminController`, validar siempre topes ($100k diésel, $50k urea) y precio por litro ($5-$60/L diésel, $2-$60/L urea); envolver mutaciones en `DB::transaction`.
- Si un gasto previo estaba en papelera (`deleted_at != null`), `GastosService::registrar()` debe invocar obligatoriamente `$gasto->restore()`; de lo contrario, se actualiza en BD pero permanece invisible en cotizaciones y `/gastos`.
- Notificaciones de feedback, confirmación de eliminación y loading en App Móvil Admin estandarizadas uniformemente con SweetAlert2.

## Próximos pasos
- [ ] Validar en producción la telemetría continua de Naanix y retirar `SISGPSTrait.php` una vez estabilizado.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
- [ ] Ejecutar `php artisan fix:gasto-fscu8106019` en el entorno de producción para corregir el histórico.
- [ ] Ejecutar `php artisan fix:gasto-msmu7292298` en el entorno de producción para restaurar el diésel histórico de MSMU7292298.
