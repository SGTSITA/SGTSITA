# Tareas — Spec 004: Sincronización Integral y Consistencia de Importes entre Gastos de Viaje, Pagos Bancarios y Administración App Móvil

- [x] **T1. Implementar método `sincronizarGastoConBancos` en `GastosService.php`.** RF-1, RF-4
  - Hecho cuando: Exista un método atómico bajo `DB::transaction()` que evalúe si el gasto tiene pago aplicado y, de ser así, actualice el gasto, el `GastoPago`, el `CatBancoCuentasMovimientos` (incluyendo su JSON `detalles`) y recalcule el saldo de la cuenta bancaria.
- [x] **T2. Integrar sincronización condicional en `AppMovilAdminController.php`.** RF-1
  - Hecho cuando: Al actualizar costo de diésel/urea en `/app-movil-admin/{id}/edit`, si el gasto no está pagado solo se actualice el gasto e importe, y si ya está pagado se invoque la sincronización completa en bancos.
- [x] **T3. Corregir ajuste de pagos únicos y JSON `detalles` en `GastosController.php`.** RF-2, RF-3
  - Hecho cuando: Al editar un gasto en `/gastos`, el pago único y el movimiento bancario se sincronicen exactamente al nuevo monto total del gasto, actualizando el sub-monto en `detalles` JSON y recalculando el saldo de la cuenta.
- [x] **T4. Agregar validación de seguridad de combustible en `ApiValidationService.php`.** RF-5
  - Hecho cuando: La API móvil rechace costos excesivos (> $100,000 MXN o precio por litro absurdo) devolviendo el formato de respuesta JSON idéntico actual con código HTTP 422 sin alterar ninguna clave del contrato.
- [x] **T5. Crear comando de regularización de datos `FixGastoFscu8106019Command`.** RF-6
  - Hecho cuando: Exista un comando Artisan seguro para corregir el gasto #2532, pago #2317 y movimiento #2258 del contenedor `FSCU8106019-ZZH86` al valor real de $17,529.09 y restituir el saldo de la cuenta #6.
- [x] **T6. Ejecutar regularización y verificar consistencia en base de datos.** RF-6, DoD
  - Hecho cuando: Se ejecute el comando en el entorno de desarrollo y se verifique mediante consulta SQL que el movimiento #2258 tenga $17,529.09 y la cuenta #6 tenga el saldo correcto.
- [x] **T7. Verificación de vistas y pruebas de API.** DoD
  - Hecho cuando: Se pruebe la edición en `/app-movil-admin` y `/gastos`, se verifique la respuesta del API móvil ante valores inválidos y no se observen errores en consola ni logs.
