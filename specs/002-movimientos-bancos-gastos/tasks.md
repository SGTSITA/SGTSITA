# Tareas — Spec 002: Movimientos y Transferencias Bancarias en Gastos

- [x] **T1. Registrar rutas backend en `routes/web.php`.** RF-5
  - Hecho cuando: Existan las rutas `GET /gastos/cuentas-bancarias`, `POST /gastos/cuentas-bancarias/movimiento` y `POST /gastos/cuentas-bancarias/transferencia` protegidas por `permission:gastos`.
- [x] **T2. Implementar métodos bancarios en `GastosController.php`.** RF-2, RF-3, RF-5
  - Hecho cuando: Los métodos validen los inputs, ejecuten los movimientos/transferencias bajo `DB::transaction()` y retornen respuestas JSON estandarizadas.
- [x] **T3. Crear partials de sub-modales en `resources/views/gastos/modals/`.** RF-1, RF-2, RF-3
  - Hecho cuando: Existan `modal_movimiento_in_gasto.blade.php` y `modal_transferencia_in_gasto.blade.php` estilizados con el tema Metronic.
- [x] **T4. Integrar botones y sub-modales en `resources/views/gastos/index.blade.php`.** RF-1, RF-2, RF-3
  - Hecho cuando: Los botones `+ Movimiento` y `⇄ Transferir` aparezcan junto al selector de banco y permitan abrir los sub-modales y refrescar saldos en tiempo real sin perder datos en `#modalGastoNew`.
- [x] **T5. Implementar el motor de borrador `sessionStorage` (Draft Autosave).** RF-4
  - Hecho cuando: Toda edición en `#formGastoNew` se guarde automáticamente, se restaure al reabrir el modal y se limpie únicamente al guardar con éxito o descartar manualmente.
- [x] **T6. Verificación integral y pruebas con Chrome DevTools.** DoD
  - Hecho cuando: Se pruebe la captura de gasto, el registro de movimiento/transferencia in-situ, la recarga del select y la persistencia de campos tras refrescar la página sin errores de consola.
