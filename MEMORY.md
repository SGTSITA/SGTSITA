# MEMORY.md — SGTSITA

Memoria del proyecto entre sesiones. Máximo ~50 líneas: resume o elimina lo que ya no aporte.

## Estado actual
- Plataforma ERP de Transporte y Logística (Gologi Pro / SGTSITA) operativa.
- Módulos activos: Cotizaciones, Viajes, Contenedores, Planeación, Monitoreo, Gastos y Liquidaciones.
- Spec 002 implementada: Integración de movimientos y transferencias bancarias en modal de gastos con Draft Autosave.
- Spec 003 implementada: Refactorización del proveedor GPS SIS ("Senbais") hacia la nueva API REST/JSON Naanix (`MClientesExternosJSPY`).
- Spec 004 implementada: Sincronización integral y consistencia de importes entre Gastos de viaje, Pagos bancarios y Administración App Móvil.
- [x] Spec 005 implementada: Multi-equipo en H00 Global (`id_empresa=28`). Precarga de unidad al editar gastos y filtro universal de Unidad/Equipo en Viajes, CXC, CXP, Utilidad, Documentos y Gastos por Pagar.
- API REST activa para la app móvil Flutter `operador_appsgt` (`/api/operador/*`).
- Microservicio en Node.js para notificaciones vía WhatsApp (`whatsapp-bot/`).
- Stack: Laravel 9 + PHP 8.1/8.2 + MySQL 8 + Docker + Metronic UI.

## Decisiones (y por qué)
- Multi-equipo H00 Global (`id_empresa=28`): coexisten tractocamiones activos (AB-100, AP-002, etc.). Cada movimiento se imputa a su unidad y los reportes filtran por `asignaciones.id_camion` o `gastos.id_equipo`.
- Transacciones `DB::transaction`: obligatorias en cotizaciones, viajes, gastos y movimientos bancarios para evitar inconsistencias.
- Trait Naanix independiente (`app/Traits/NaanixGPSTrait.php`): preserva `SISGPSTrait.php` intacto como respaldo/fallback.
- Caché de Tokens Naanix (50 min) y Llave UTC dinámica: previene saturación de peticiones de autenticación en el cron `rastreo:intervalConfig`.
- Consulta masiva por lote (`ObtenerPosicionActualGeneral`): reduce latencia de sincronización de ~40s a < 1s.
- `sincronizarGastoConBancos()` en `GastosService`: unifica propagación en cascada de importes hacia `gasto_pagos`, `cat_bancos_cuentas_movimientos` y saldo en `bancos`.
- `EquipoService` centralizado (`app/Services/EquipoService.php`): unifica `getTractocamiones()` para reportería y gastos eliminando duplicación de consultas en controladores (Principio 7 de la Constitución).

## Aprendizajes y errores a evitar
- NUNCA modificar la respuesta JSON de endpoints en `routes/api.php` sin verificar el impacto en la app Flutter `operador_appsgt`.
- No ejecutar `migrate:fresh` ni `db:wipe` en ningún entorno.
- En Naanix REST, la llave debe calcularse estrictamente en UTC (`UtcNow.Hour + UtcNow.Day + IdCliente`).
- En modal de nuevo gasto, disparar `handleSelectionNew` en `show.bs.modal` para asegurar que el bloque de Unidad/Equipo se muestre inmediatamente.
- En reportes y catálogos de imputación a unidad, filtrar siempre `where('tipo', 'Tractos / Camiones')` en `Equipo` para excluir remolques, chasis o dollys.
- En reportes con AG Grid, agregar columnas y filtros usando nombres de ruta y atributos de entidad consistentes (`id_equipo`, `asignaciones.id_camion`).

## Próximos pasos
- [ ] Validar en producción la telemetría continua de Naanix y retirar `SISGPSTrait.php` una vez estabilizado.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
- [ ] Ejecutar `php artisan fix:gasto-fscu8106019` en el entorno de producción para corregir el histórico.
