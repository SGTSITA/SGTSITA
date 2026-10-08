# Tareas de Implementación — Spec 005

- [x] **Tarea 1 — Soporte de Restauración en `GastosService::registrar()`**
  - **RF:** RF-1
  - **Archivos:** `app/Services/GastosService.php`
  - **Hecho cuando:** Si `resolverGastoExistente()` retorna un modelo con `deleted_at` no nulo (`$gasto->trashed()`), se ejecuta `$gasto->restore()` antes de llamar a `$gasto->update($payload)`.

- [x] **Tarea 2 — Detección con `withTrashed()` en `AppMovilAdminController`**
  - **RF:** RF-2
  - **Archivos:** `app/Http/Controllers/AppMovilAdminController.php`
  - **Hecho cuando:** Las consultas que buscan `$gastoDieselExistente` y `$gastoUreaExistente` incluyan `withTrashed()` para no omitir registros históricos en papelera al calcular sincronizaciones y pagos.

- [x] **Tarea 3 — Comando de Regularización para el Contenedor MSMU7292298**
  - **RF:** RF-3
  - **Archivos:** `app/Console/Commands/FixGastoMsmu7292298Command.php`
  - **Hecho cuando:** El comando restaura el Gasto #2236 con monto $16,827.91 y `deleted_at = NULL`, enlazado a la cotización #4602 y asignación #3720.

- [x] **Tarea 4 — Verificación en Navegador con Chrome DevTools**
  - **RF:** RF-1, RF-2, RF-3
  - **Archivos:** Vistas web de Cotizaciones (`/cotizaciones/4602/edit`) y Gastos (`/gastos`)
  - **Hecho cuando:** La pestaña "Gastos Viaje" en la cotización #4602 y el módulo `/gastos` muestren exitosamente el registro de diésel por $16,827.91 junto con la urea por $902.40.
