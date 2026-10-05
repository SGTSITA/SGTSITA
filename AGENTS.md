# AGENTS.md — SGTSITA

Sistema ERP de Gestión de Transporte, Logística y Contenedores (JoseMXN / Gologi Pro). Incluye cotizaciones, monitoreo, API para operadores móviles y bot de WhatsApp.

## Stack y estructura
- Laravel 9.x (PHP 8.1 / 8.2), MySQL 8, Docker Compose (`docker-compose.yml`, `Dockerfile`).
- `whatsapp-bot/`: microservicio en Node.js para notificaciones automáticas por WhatsApp.
- `routes/web.php`: panel administrativo con tema Metronic.
- `routes/api.php`: API REST consumida por la app móvil Flutter `operador_appsgt`.
- `docs/constitution.md`: 6 principios innegociables del proyecto.
- `MEMORY.md`: memoria viva del proyecto entre sesiones.
- `specs/`: especificaciones funcionales bajo metodología SDD.

## Comandos
- Iniciar Docker: `docker compose up -d`
- Artisan en Host: `php artisan <comando>`
- Artisan en Docker: `docker compose exec app php artisan <comando>`
- Limpiar cachés: `php artisan optimize:clear`
- Pruebas unitarias: `vendor/bin/phpunit`

## Convenciones
- Thin Controllers: lógica de negocio delegada a `app/Services/`.
- Validaciones estrictas en FormRequests (`app/Http/Requests/`).
- Código y modelos en inglés o español según módulo existente; vistas y mensajes de usuario en español.
- Diseño consistente con el tema Metronic.

## Reglas de dominio / trampas conocidas
- La app móvil `operador_appsgt` depende de `routes/api.php`. Nunca cambiar nombres de parámetros ni formato JSON sin sincronizar con la app móvil.
- Toda operación de cotizaciones, partidas o viajes debe ejecutarse dentro de `DB::transaction()`.
- Prohibido `php artisan migrate:fresh` o `db:wipe` en cualquier entorno.

## Forma de trabajar
- Para cambios pequeños: editar directamente y probar.
- Para nuevas funcionalidades o módulos: seguir el flujo SDD en `specs/NNN-nombre/` con `spec.md`, `plan.md` y `tasks.md`.
- No toques código hasta que el usuario apruebe la spec y el plan.

## Memoria
- Al empezar, lee `MEMORY.md` para conocer el estado del proyecto y las decisiones tomadas.
- Al terminar una tarea, actualízalo: estado actual, decisiones importantes (con su porqué) y errores a evitar.
- Mantenlo breve (máximo ~50 líneas): resume o elimina lo que ya no aporte.
- Si algo se convierte en una regla permanente, propón moverlo a `AGENTS.md` en lugar de dejarlo en la memoria.
- No guardes nunca datos sensibles (claves, tokens de WhatsApp).

## Límites
- ✅ Siempre: actualizar `MEMORY.md` al terminar cada tarea, usar `DB::transaction()`, validar con FormRequests.
- ⚠️ Pregunta antes: crear migraciones que alteren tablas existentes de cotizaciones o viajes, instalar paquetes nuevos.
- 🚫 Nunca: ejecutar `migrate:fresh`, quemar secretos en código o romper endpoints de la API móvil.

## Verificación
- Después de cada cambio web, verifica con el MCP de Chrome DevTools: abre la URL local, prueba la funcionalidad y revisa la consola de errores.
- En cambios a endpoints de API: verificar con curl o pruebas HTTP el código de respuesta y estructura JSON.

## Reglas SDD
- Lee `docs/constitution.md` y la spec activa (`specs/NNN-*/`) antes de tocar código en nuevas funcionalidades.
