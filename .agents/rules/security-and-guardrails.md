# Guardarraíles de Seguridad y Protección (SGTSITA)

## 1. Protección de Datos y Secretos

- Nunca almacenar claves API, tokens de WhatsApp, credenciales de base de datos o secretos en el repositorio.
- Todas las configuraciones sensibles se leen de `.env` a través de archivos en `config/`.
- No mostrar trazas de error completas (_stack traces_) al usuario final en producción (`APP_DEBUG=false`).

## 2. Permisos y Roles (Spatie)

- Cualquier nueva ruta administrativa o de modificación debe contar con protección por middleware:
    ```php
    Route::middleware(['auth', 'permission:cotizaciones.crear'])->group(function() { ... });
    ```
- Si un usuario no autorizado intenta realizar una acción, abortar con HTTP 403 Forbidden.

## 3. Subida y Procesamiento de Archivos (PDF / Excel / Imágenes)

- Validar siempre tamaño máximo (`max:10240` KB) y tipos MIME autorizados (`mimes:pdf,xlsx,csv,jpg,png,html`).
- Guardar archivos usando el sistema de almacenamiento de Laravel (`Storage::disk('...')`), nunca en rutas del sistema operativo desprotegidas.
- Evitar nombres de archivo con caracteres no sanitizados.

## 4. Protección contra Ataques Web Comunes

- **CSRF:** Todas las rutas `POST`, `PUT`, `DELETE` en `routes/web.php` deben incluir directiva `@csrf` en formularios Blade o header `X-CSRF-TOKEN` en llamadas AJAX.
- **SQL Injection:** Prohibido concatenar entradas de usuario en sentencias SQL.
- **XSS:** Usar `{!! $variable !!}` únicamente cuando sea estrictamente necesario y el contenido haya sido sanitizado previamente (preferir siempre `{{ $variable }}`).
