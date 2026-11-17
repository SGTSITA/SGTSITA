# Spec 005 — Restauración y Visibilidad de Gastos Soft-Deleted Re-sincronizados desde App Móvil Admin

Estado: completado

## 1. Contexto y Problema
En el ERP SGTSITA, los gastos de combustible (Diésel y Urea) generados durante los viajes pueden ser creados, modificados o re-sincronizados desde múltiples vías:
1. App Móvil de Operadores.
2. Planeación y asignación de viajes.
3. Administración de la App Móvil (`/app-movil-admin/{id}/edit`).
4. Módulo central de Gastos (`/gastos`).

### El Caso Detectado (Contenedor MSMU7292298 / DocumCotización 4520 / Cotización 4602 / Asignación 3720 / Bitácora 8):
1. **Captura en Admin Móvil:** El usuario ingresó a `/app-movil-admin/8/edit`, capturó datos de diésel ($16,827.91 / 609.93 L) y urea ($902.40 / 48 L), dejando vacías coordenadas y odómetro, y guardó los cambios exitosamente.
2. **Síntoma Reportado:** Al revisar en Cotizaciones y en el módulo de Gastos, la Urea sí aparece correctamente en el grid y en el listado, pero el Diésel **no aparece** en ninguna parte.
3. **Causa Raíz Identificada:**
   - La asignación #3720 tenía un registro histórico de diésel en la tabla `gastos` (Gasto #2236) que había sido marcado como eliminado lógicamente (`deleted_at: 2026-08-12 14:13:12`).
   - Al guardar en `AppMovilAdminController`, el método `resolverGastoExistente` en `GastosService` localiza el gasto histórico mediante `Gasto::withTrashed()`.
   - El servicio actualiza los montos y fechas en la base de datos (`$gasto->update(...)`), **pero nunca lo restaura** (`$gasto->restore()` no es invocado).
   - Como resultado, el gasto de diésel permanece con `deleted_at != null`. Todas las vistas del ERP (`/gastos`, `gridGastosOperador` en `/cotizaciones/{id}/edit` y `/gastos/data`) filtran registros con `whereNull('deleted_at')`, ocultando completamente el gasto de diésel.
   - En contraste, la Urea no tenía registros previos en la papelera; por ende, se creó un registro activo nuevo (`deleted_at = null`) que es inmediatamente visible.

---

## 2. Usuarios y Roles Afectados
- **Administrador de App Móvil / Tráfico:** Edita bitácoras y espera que tanto diésel como urea se sincronicen y sean visibles en el sistema.
- **Facturación y Cotizaciones:** Requiere visualizar todos los gastos de viaje (diésel y urea) en la pestaña "Gastos Viaje" de la cotización para cierres y liquidaciones.
- **Tesorería y Cuentas por Pagar:** Consulta `/gastos` para programar pagos de viáticos y combustible a operadores.

---

## 3. Historias de Usuario
- **HU-1:** Como administrador en `/app-movil-admin`, cuando capturo o modifico un gasto de combustible (diésel o urea) en una bitácora cuyo gasto previo había sido eliminado o enviado a la papelera, quiero que el sistema reactive y restaure automáticamente el registro de gasto para que vuelva a estar visible en todo el sistema.
- **HU-2:** Como usuario de Cotizaciones en `/cotizaciones/{id}/edit`, quiero visualizar en la pestaña "Gastos Viaje" todos los gastos capturados desde la bitácora móvil (incluyendo el diésel del contenedor MSMU7292298), reflejando con exactitud los litros y montos capturados.
- **HU-3:** Como analista de Gastos en `/gastos`, quiero que al re-vincular o editar una bitácora de viaje, los gastos de combustible se muestren activos en estado `pendiente_pago` (o pagado según corresponda), listos para auditoría o dispersión bancaria.

---

## 4. Requisitos Funcionales (Notación EARS)

### Módulo de Servicios de Gastos (`GastosService`)
- **RF-1 (Restauración Automática de Gastos en Papelera):**
  - CUANDO `GastosService::registrar()` recibe datos de un gasto resuelto mediante `resolverGastoExistente()`,
  - SI el registro encontrado se encuentra en la papelera (`$gasto->trashed()`),
  - EL SISTEMA debe restaurar el registro (`$gasto->restore()`) antes o durante la actualización de sus datos, garantizando que `deleted_at` quede en `NULL`.

- **RF-2 (Detección Consistente de Gastos Existentes en Controladores):**
  - CUANDO `AppMovilAdminController::update()` busca un gasto existente para validar su estado de pago y sincronización,
  - EL SISTEMA debe buscar considerando registros existentes incluso si estaban en papelera (`withTrashed()`), asegurando que las validaciones de pago y las referencias pasadas al servicio sean simétricas y congruentes.

### Corrección de Datos Históricos (Caso MSMU7292298)
- **RF-3 (Restauración y Regularización del Gasto #2236):**
  - EL SISTEMA debe restaurar el gasto #2236 (`deleted_at = null`), verificar sus vínculos activos con la cotización #4602, asignación #3720, contenedor #4520 y operador #517, e imputación por $16,827.91.

---

## 5. Criterios de Aceptación (Definition of Done - DoD)
- [x] Al guardar cambios en `/app-movil-admin/{id}/edit` para una bitácora con gasto previo en papelera, el gasto queda restaurado (`deleted_at IS NULL`).
- [x] En `/cotizaciones/edit/4602`, la pestaña "Gastos Viaje" muestra tanto el Diésel ($16,827.91) como la Urea ($902.40).
- [x] En el módulo `/gastos`, el registro de Diésel del viaje aparece en el listado con estado `pendiente_pago`.
- [x] La operación se ejecuta de forma segura dentro de transacciones atómicas `DB::transaction()`.
