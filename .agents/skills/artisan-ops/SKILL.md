---
name: artisan-ops
description: Protocolo seguro para la ejecución de comandos Artisan, gestión de migraciones y limpieza de caché en SGTSITA (local o Docker).
---

# Procedimiento de Operaciones Artisan

Utiliza este procedimiento cuando se requiera ejecutar comandos de Laravel Artisan, crear migraciones o limpiar cachés del sistema.

## 1. Detección de Entorno
Verificar si el entorno está corriendo directamente en el host o dentro del contenedor de Docker:
- **En Host:** `php artisan <comando>`
- **En Docker:** `docker compose exec app php artisan <comando>`

## 2. Protocolo para Migraciones
1. **Inspección Previa:** Revisar siempre el archivo de migración generado antes de aplicar `migrate`.
2. **Reversibilidad:** Comprobar que el método `down()` contenga la acción inversa exacta (ej: `dropIfExists`, `dropColumn`).
3. **Ejecución Segura:**
   ```bash
   php artisan migrate --pretend
   php artisan migrate
   ```
4. **Prohibición Estricta:** NUNCA ejecutar `migrate:fresh` ni `migrate:rollback --step=99` sin confirmación previa del usuario.

## 3. Limpieza y Optimización de Caché
Cuando se modifiquen rutas, configuraciones o vistas:
```bash
php artisan optimize:clear
```
*(Limpia configuración, eventos, rutas y vistas compiladas en un solo comando).*
