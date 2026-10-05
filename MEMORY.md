# MEMORY.md — SGTSITA

Memoria del proyecto entre sesiones. Máximo ~50 líneas: resume o elimina lo que ya no aporte.

## Estado actual
- Plataforma ERP de Transporte y Logística (Gologi Pro / SGTSITA) operativa.
- Módulos activos: Cotizaciones, Viajes, Contenedores, Planeación, Monitoreo, Gastos y Liquidaciones.
- Spec 002 implementada: Integración in-situ de movimientos y transferencias bancarias en modal de gastos con Draft Autosave (`sessionStorage`).
- API REST activa para la app móvil Flutter `operador_appsgt` (`/api/operador/*`).
- Microservicio en Node.js para notificaciones vía WhatsApp (`whatsapp-bot/`).
- Stack: Laravel 9 + PHP 8.1/8.2 + MySQL 8 + Docker + Metronic UI.

## Decisiones (y por qué)
- Docker Compose: garantiza paridad entre entorno local de desarrollo y producción.
- API Sanctum para `operador_appsgt`: autenticación móvil desacoplada y segura.
- Transacciones `DB::transaction`: obligatorias en cotizaciones, viajes, gastos y movimientos bancarios para evitar inconsistencias.
- Operaciones bancarias en Gastos (`permission:gastos`): aisladas en `GastosController` para evitar bloqueos por middleware `finanzas:3`.
- Flujo SDD: toda nueva funcionalidad se especifica en `specs/NNN-nombre/` antes de codificar.

## Aprendizajes y errores a evitar
- NUNCA modificar la respuesta JSON de endpoints en `routes/api.php` sin verificar el impacto en la app Flutter `operador_appsgt`.
- No ejecutar `migrate:fresh` ni `db:wipe` en ningún entorno.
- En modales de captura con Choices.js y radios, persistir borradores en `sessionStorage` para evitar pérdidas por navegación o saldos insuficientes.

## Próximos pasos
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
- [ ] Sincronización de notificaciones push para asignación de viajes al operador.
